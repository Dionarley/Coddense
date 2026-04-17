<?php

namespace App\Jobs;

use App\Models\Repository;
use App\Services\ParserFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class ProcessRepositoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $maxExceptions = 3;

    public function __construct(
        public int $repositoryId
    ) {}

    public function handle(ParserFactory $parserFactory): void
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
            $this->parseAndStoreEntities($repository, $tempPath, $parserFactory);
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

    private function parseAndStoreEntities(Repository $repository, string $tempPath, ParserFactory $parserFactory): void
    {
        $finder = new Finder;
        $finder->files()
            ->in($tempPath)
            ->exclude(['node_modules', 'vendor', 'storage', 'public', 'bootstrap', 'cache', 'logs', 'tests', 'database', '.git', 'config', 'resources'])
            ->name('/\.(php|js|jsx|ts|tsx|py|rb|rs|java|cpp|cc|cxx|c|h|hpp|hh|jsp|erb|rake)$/')
            ->filter(function (\SplFileInfo $file) {
                $path = $file->getRelativePathname();
                foreach (['node_modules', 'vendor', 'storage', '.git', 'bootstrap/cache'] as $exclude) {
                    if (str_contains($path, $exclude)) {
                        return false;
                    }
                }

                return true;
            });

        $languages = [];
        $fileCount = 0;
        $scanner = new VulnerabilityScanner;

        foreach ($finder as $file) {
            $fileCount++;

            $vulnerabilities = [];
            $extension = $file->getExtension();

            if (in_array($extension, ['php'])) {
                $vulnerabilities = $scanner->scan($file->getPathname());
            }

            $entities = $parserFactory->parseFile($file->getPathname());

            if (! empty($entities)) {
                $language = $this->detectLanguage($extension);

                foreach ($entities as $entity) {
                    $repository->codeEntities()->create([
                        'type' => $entity['type'],
                        'name' => $entity['name'],
                        'namespace' => $entity['namespace'] ?? null,
                        'file_path' => $file->getRelativePathname(),
                        'language' => $language,
                        'details' => $entity['details'] ?? null,
                        'vulnerabilities' => empty($vulnerabilities) ? null : $vulnerabilities,
                    ]);

                    $languages[$language] = true;
                }
            }

            if ($fileCount % 100 === 0) {
                Log::info("Processed {$fileCount} files...");
            }
        }

        Log::info("Total files processed: {$fileCount}");

        if (! empty($languages)) {
            $repository->update(['languages' => array_keys($languages)]);
        }
    }

    private function detectLanguage(string $extension): string
    {
        return match ($extension) {
            'php' => 'PHP',
            'js', 'jsx', 'mjs', 'cjs' => 'JavaScript',
            'ts', 'tsx' => 'TypeScript',
            'py' => 'Python',
            'rb', 'erb', 'rake' => 'Ruby',
            'cpp', 'cc', 'cxx', 'c', 'h', 'hpp', 'hh' => 'C/C++',
            'java', 'jsp' => 'Java',
            'rs' => 'Rust',
            default => 'Unknown',
        };
    }

    public function failed(\Throwable $exception): void
    {
        $repository = Repository::find($this->repositoryId);
        if ($repository) {
            $repository->update(['status' => 'failed']);
        }
    }
}
