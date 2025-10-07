<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
// Added one more commit
class ClearTemporaryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:clear-temp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear temporary files older than 7 days in all subdirectories of storage/framework/cache, storage/logs, and storage/otc_log';

    /**
     * The temporary paths to clear.
     *
     * @var array
     */
    protected $temporaryPaths = [
        'framework/cache',
        'logs',
        'otc_log',
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();

        foreach ($this->temporaryPaths as $path) {
            $temporaryPath = storage_path($path);
            $this->deleteOldFiles($temporaryPath, $now);
        }

        $this->info('Temporary files and folders older than 7 days have been deleted.');

        return 0;
    }

    /**
     * Recursively delete files older than 7 days.
     *
     * @param string $path
     * @param \Carbon\Carbon $now
     * @return void
     */
    protected function deleteOldFiles($path, $now)
    {
        $files = File::allFiles($path);

        foreach ($files as $file) {
            if ($now->diffInDays(Carbon::createFromTimestamp(File::lastModified($file))) > 7) {
                File::delete($file);
            }
        }

        $directories = File::directories($path);

        foreach ($directories as $directory) {
            $this->deleteOldFiles($directory, $now);
            if (count(File::allFiles($directory)) == 0 && count(File::directories($directory)) == 0) {
                File::deleteDirectory($directory);
            }
        }
    }
}
