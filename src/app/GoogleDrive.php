<?php

namespace App;

use App\Exceptions\FileCreationException;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use Carbon\Carbon;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFileImageMediaMetadata;
use Hypweb\Flysystem\GoogleDrive\GoogleDriveAdapter;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManager;
use League\Flysystem\Cached\CachedAdapter;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\X264;
use GuzzleHttp\Psr7\Response;

use App\Exceptions\UnknownFileException;
use App\Exceptions\UnknownMimeTypeException;

class GoogleDrive
{
    /**
     * @var CachedAdapter
     */
    protected $adapter;

    /**
     * @var ImageManager
     */
    protected $manager;

    /**
     * GoogleDrive constructor.
     */
    public function __construct()
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.clientId'));
        $client->setClientSecret(config('services.google.clientSecret'));
        $client->refreshToken(config('services.google.refreshToken'));

        $service = new Google_Service_Drive($client);

        $additionalFields = implode(',', [
            'thumbnailLink',
            'webContentLink',
            'imageMediaMetadata',
        ]);

        $this->adapter = new GoogleDriveAdapter($service, config('services.google.folderId'), [
            'additionalFetchField' => $additionalFields,
        ]);

        $this->manager = new ImageManager([
            'driver' => 'imagick',
        ]);
    }

    /**
     * Get the human readable file size.
     *
     * @param int $size
     * @param int $precision
     * @return string
     */
    protected static function humanFilesize(int $size, $precision = 2)
    {
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $step = 1024;
        $i = 0;

        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Get all files.
     *
     * @return Collection
     */
    public function files()
    {
        return collect($this->adapter->listContents())
            // Only include files
            ->filter(function (array $file) {
                return Str::lower($file['type'] ?? '') === 'file';
            })
            // Sort by timestamp (newews first)
            ->sortByDesc('timestamp')
            // Get normalized response
            ->map(function (array $file) {
                $location = null;
                $ratio = 1;
                $width = null;
                $height = null;

                if (isset($file['imageMediaMetadata'])) {
                    /**
                     * @var $metaData Google_Service_Drive_DriveFileImageMediaMetadata
                     */
                    $metaData = $file['imageMediaMetadata'];

                    $location = $metaData->getLocation();

                    $width = $metaData->getWidth();
                    $height = $metaData->getHeight();
                    if ($metaData->height !== null && $metaData->width !== null) {
                        // If image was rotated an uneven number of times, width and height are switched
                        if ($metaData->getRotation() % 2 === 0) {
                            $ratio = round($metaData->width / $metaData->height, 2);
                        } else {
                            $ratio = round($metaData->height / $metaData->width, 2);
                        }
                    }
                }

                return [
                    'id' => $file['path'] ?? null,
                    'name' => $file['name'] ?? null,
                    'time' => isset($file['timestamp']) ? Carbon::parse($file['timestamp']) : null,
                    'mimetype' => $file['mimetype'] ?? null,
                    'size' => $file['size'] ?? null,
                    'size_human' => isset($file['size']) ? self::humanFilesize($file['size'], 0) : null,
                    'thumbnailLink' => isset($file['thumbnailLink']) ? Str::replaceLast('s220', 's500', $file['thumbnailLink']) : null,
                    'downloadLink' => isset($file['webContentLink']) ? Str::replaceLast('&export=download', '', $file['webContentLink']) : null,
                    'ratio' => $ratio,
                    'width' => $width,
                    'height' => $height,
                    'location' => $location !== null ? [
                        'alt' => $location->getAltitude(),
                        'lng' => $location->getLongitude(),
                        'lat' => $location->getLatitude(),
                    ] : null,
                ];
            });
    }

    /**
     * Get all the cached files.
     *
     * @return Collection
     */
    public function list()
    {
        return Cache::rememberForever('google-drive-list', function () {
            return $this->files();
        });
    }

    /**
     * @param string $id
     * @param bool $simulate
     * @return string
     */
    protected function download(string $id, bool $simulate): ?string
    {
        Log::debug(sprintf('Downloading %s', $id));

        /**
         * @var $response Response
         */
        $response = $this->adapter->getService()->files->get($id, [
            'alt' => 'media',
        ]);

        if (!$simulate) {
            return $response->getBody();
        }

        return null;
    }

    /**
     * @param string $id
     * @param string $path
     * @param bool $force
     * @param bool $simulate
     * @throws FileCreationException
     */
    protected function convertImage(string $id, string $path, bool $force = false, bool $simulate = false)
    {
        if (file_exists($path) && !$force) {
            Log::debug(sprintf('File already exists %s', $path));
            return;
        }

        $imageContents = $this->download($id, $simulate);

        Log::debug('Converting image');

        if (!$simulate) {
            $this->manager->make($imageContents)
                ->resize(1024, null, function (Constraint $constraint) {
                    $constraint->aspectRatio();
                })
                ->save($path, 90, 'jpg');

            if (!file_exists($path)) {
                throw new FileCreationException(sprintf('Could not create jpg file %s!', $path));
            }
        }
    }

    /**
     * @param string $id
     * @param string $path
     * @param bool $force
     * @param bool $simulate
     * @throws FileCreationException
     */
    protected function convertVideo(string $id, string $path, bool $force = false, bool $simulate = false)
    {
        if ((file_exists($path) && file_exists($path . '.mp4') && file_exists($path . 'webm') && file_exists($path . '-thumbnail')) && !$force) {
            Log::debug(sprintf('File already exists %s', $path));
            return;
        }

        $videoContents = $this->download($id, $simulate);

        $video = null;

        if (!$simulate) {
            // Save raw file
            if (!file_exists($path) || $force) {
                file_put_contents($path, $videoContents);
            }

            $ffmpeg = FFMpeg::create([
                'timeout' => 600,
            ]);
            $video = $ffmpeg->open($path);
        }

        Log::debug('Converting video');

        if (!$simulate) {
            // Save thumbnail
            if (!file_exists($path . '-thumbnail') || $force) {
                $video
                    ->frame(TimeCode::fromSeconds(1))
                    ->save($path . '-thumbnail');

                $playButton = $this->manager
                    ->make(storage_path('app/images/play.png'))
                    ->resize(256, null, function (Constraint $constraint) {
                        $constraint->aspectRatio();
                    });

                $this->manager->make($path . '-thumbnail')
                    ->blur(10)
                    ->insert($playButton, 'center')
                    ->save($path . '-thumbnail', 100);
            }

            if (!file_exists($path . '.mp4') || $force) {
                $video->save(new X264('aac', 'libx264'), $path . '.mp4');
            }

            if (!file_exists($path . '.webm') || $force) {
                $video->save(new WebM, $path . '.webm');
            }

            if (!file_exists($path)) {
                throw new FileCreationException(sprintf('Could not create video file %s!', $path));
            }

            if (!file_exists($path . '-thumbnail')) {
                throw new FileCreationException(sprintf('Could not create video preview image %s!', $path . '-thumbnail'));
            }

            if (!file_exists($path . '.mp4')) {
                throw new FileCreationException(sprintf('Could not create mp4 file %s!', $path . '.mp4'));
            }

            if (!file_exists($path . '.webm')) {
                throw new FileCreationException(sprintf('Could not create webm file %s!', $path . '.webm'));
            }
        }
    }

    /**
     * Get the public url of a google drive file.
     *
     * @param string $id
     * @param bool $force
     * @param bool $simulate
     * @return boolean
     * @throws FileCreationException
     * @throws UnknownFileException
     * @throws UnknownMimeTypeException
     */
    public function convert(string $id, bool $force = false, bool $simulate = false): bool
    {
        Log::debug(sprintf('Trying to convert file %s (Force: %d; Simulate: %d)', $id, (int)$force, (int)$simulate));
        $files = $this->list();

        /**
         * @var $file array|null
         */
        $file = $files
            ->filter(function ($file) use ($id) {
                return $file['id'] === $id;
            })
            ->first();

        if (empty($file)) {
            throw new UnknownFileException(sprintf('Could not find file: %s', $id));
        }

        $path = public_path(sprintf('preview/%s', $id));

        if (Str::startsWith($file['mimetype'], 'image/')) {
            Log::debug(sprintf('Converting image %s', $file['mimetype']));
            $this->convertImage($id, $path, $force, $simulate);
        } else if (Str::startsWith($file['mimetype'], 'video/')) {
            Log::debug(sprintf('Converting video %s', $file['mimetype']));
            $this->convertVideo($id, $path, $force, $simulate);
        } else {
            throw new UnknownMimeTypeException(sprintf('Unkown mime type: %s', $file['mimetype']));
        }

        return true;
    }
}
