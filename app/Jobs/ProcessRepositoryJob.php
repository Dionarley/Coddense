<?php

namespace App\Jobs;

use App\Models\Repository;
use App\Services\CodeParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessRepositoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $maxExceptions = 3;

    public function __construct(
        public int $repositoryId
    ) {}

    public function handle(CodeParserService $parser): void
    {
        $repository = Repository::find($this->repositoryId);

        if (! $repository) {
            Log::warning('Repository not found', ['repository_id' => $this->repositoryId]);

            return;
        }

        $repository->update(['status' => 'processing']);
        $url = $repository->remote_url;
        $isLocal = ! Str::startsWith($url, ['http://', 'https://', 'git@']);
        $tempPath = $isLocal ? $url : storage_path("app/temp/{$repository->id}");

        try {
            if (! $isLocal) {
                $this->cloneRepository($repository, $tempPath);
            }
            $this->parseAndStoreEntities($repository, $tempPath, $parser);
            $repository->update(['status' => 'completed']);
        } catch (\Throwable $e) {
            Log::error('Repository processing failed', [
                'repository_id' => $repository->id,
                'error' => $e->getMessage(),
            ]);
            $repository->update(['status' => 'failed']);
            throw $e;
        } finally {
            if (! $isLocal && File::isDirectory($tempPath)) {
                File::deleteDirectory($tempPath);
            }
        }
    }

    private function cloneRepository(Repository $repository, string $tempPath): void
    {
        $url = $repository->remote_url;

        if (Str::startsWith($url, ['http://', 'https://', 'git@'])) {
            File::makeDirectory($tempPath, 0755, true);
            $output = [];
            $exitCode = null;
            exec('git clone --depth 1 '.escapeshellarg($url).' '.escapeshellarg($tempPath).' 2>&1', $output, $exitCode);

            if ($exitCode !== 0) {
                throw new \RuntimeException('Git clone failed: '.implode("\n", $output));
            }
        } elseif (File::isDirectory($url)) {
            File::copyDirectory($url, $tempPath);
        } else {
            throw new \RuntimeException('Invalid source path: '.$url);
        }
    }

    private function parseAndStoreEntities(Repository $repository, string $tempPath, CodeParserService $parser): void
    {
        $files = File::allFiles($tempPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $entities = $parser->parseFile($file->getRealPath());

                foreach ($entities as $entity) {
                    $repository->codeEntities()->create([
                        'type' => $entity['type'],
                        'name' => $entity['name'],
                        'namespace' => $entity['namespace'] ?? null,
                        'file_path' => $file->getRelativePathname(),
                        'details' => $entity['details'] ?? null,
                    ]);
                }
            }
        }
    }

    private function cleanup(string $tempPath): void
    {
        if (File::isDirectory($tempPath)) {
            File::deleteDirectory($tempPath);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $repository = Repository::find($this->repositoryId);
        if ($repository) {
            $repository->update(['status' => 'failed']);
        }
    }
}
