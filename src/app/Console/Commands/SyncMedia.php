<?php

namespace App\Console\Commands;

use App\Exceptions\UnknownFileException;
use App\Exceptions\UnknownMimeTypeException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use App\GoogleDrive;

class SyncMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:sync {--force} {--simulate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync media from google drive';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $drive = new GoogleDrive;
        $files = $drive->files();

        $this->comment('Converting files...');

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            try {
                $drive->convert($file['id'], $this->option('force'), $this->option('simulate'));
            } catch (UnknownFileException $e) {
                Log::error($e->getMessage());
            } catch (UnknownMimeTypeException $e) {
                Log::error($e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->comment('');

        $this->comment('Setting cache...');

        if (Cache::has('google-drive-list') && (count($files) !== count(Cache::get('google-drive-list')))) {
            $this->info('Files changed!');

            Log::info(
                sprintf('Files changed from %d to %d.', count(Cache::get('google-drive-list')), count($files)),
                [
                    'old_files' => Cache::get('google-drive-list')->map(function ($file) {
                        return $file['id'];
                    }),
                    'new_files' => $files->map(function ($file) {
                        return $file['id'];
                    }),
                ]
            );

            Cache::forget('google-drive-list');
            Cache::forever('google-drive-list', $files);
        } else {
            $this->comment('Files did not change.');
        }

        $this->info('Finished.');

        return true;
    }
}
