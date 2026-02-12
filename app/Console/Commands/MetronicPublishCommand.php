<?php

namespace App\Console\Commands;

use App\Support\AppLog;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MetronicPublishCommand extends Command
{
    protected $signature = 'metronic:publish {--force : Overwrite existing public/assets directory}';

    protected $description = 'Publish Metronic demo1 dist assets to public/assets';

    public function handle(Filesystem $files): int
    {
        $source = base_path('metronic/demo1/dist/assets');
        $target = public_path('assets');

        if (! $files->isDirectory($source)) {
            $this->error("Metronic assets source directory not found: {$source}");

            return self::FAILURE;
        }

        if ($files->exists($target)) {
            if (! $this->option('force')) {
                $this->error("Target already exists: {$target}. Re-run with --force to overwrite.");

                return self::FAILURE;
            }

            $files->deleteDirectory($target);
        }

        $files->ensureDirectoryExists($target);

        $ok = $files->copyDirectory($source, $target);

        if (! $ok) {
            $this->error('Failed to copy Metronic assets.');

            return self::FAILURE;
        }

        AppLog::info('Metronic Assets Published', [
            'source' => $source,
            'target' => $target,
        ]);

        $this->info("Metronic assets published to: {$target}");

        return self::SUCCESS;
    }
}
