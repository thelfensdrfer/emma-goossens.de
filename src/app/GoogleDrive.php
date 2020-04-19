<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

use Carbon\Carbon;
use Hypweb\Flysystem\GoogleDrive\GoogleDriveAdapter;

class GoogleDrive
{
    /**
     * @var \League\Flysystem\Cached\CachedAdapter
     */
    protected $adapter;

    public function __construct()
    {
        $client = new \Google_Client();
        $client->setClientId(config('services.google.clientId'));
        $client->setClientSecret(config('services.google.clientSecret'));
        $client->refreshToken(config('services.google.refreshToken'));

        $service = new \Google_Service_Drive($client);

        $additionalFields = implode(',', [
            'thumbnailLink',
            'webViewLink',
            'imageMediaMetadata',
        ]);

        $this->adapter = new GoogleDriveAdapter($service, config('services.google.folderId'), [
            'additionalFetchField' => $additionalFields,
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
     * List all the files.
     *
     * @return \Illuminate\Support\Collection
     */
    public function list()
    {
        return Cache::remember('google-drive-list', now()->addMinutes(10), function () {
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
                         * @var $metaData \Google_Service_Drive_DriveFileImageMediaMetadata
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
                        'downloadLink' => $file['webViewLink'] ?? null,
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
        });
    }
}
