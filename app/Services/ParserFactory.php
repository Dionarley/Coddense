<?php

namespace App\Services;

use App\Services\Parsers\JavaScriptParser;
use App\Services\Parsers\LanguageParserInterface;
use App\Services\Parsers\PhpParser;
use App\Services\Parsers\PythonParser;
use App\Services\Parsers\TypeScriptParser;

class ParserFactory
{
    private array $parsers = [];

    public function __construct()
    {
        $this->registerDefaultParsers();
    }

    private function registerDefaultParsers(): void
    {
        $this->register(new PhpParser);
        $this->register(new JavaScriptParser);
        $this->register(new TypeScriptParser);
        $this->register(new PythonParser);
    }

    public function register(LanguageParserInterface $parser): void
    {
        foreach ($parser->getSupportedExtensions() as $ext) {
            $this->parsers[$ext] = $parser;
        }
    }

    public function getParserForFile(string $filePath): ?LanguageParserInterface
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return $this->parsers[$extension] ?? null;
    }

    public function getSupportedExtensions(): array
    {
        return array_keys($this->parsers);
    }

    public function getAvailableLanguages(): array
    {
        $languages = [];
        foreach ($this->parsers as $parser) {
            $languages[] = $parser->getLanguageName();
        }

        return array_unique($languages);
    }

    public function parseFile(string $filePath): array
    {
        $parser = $this->getParserForFile($filePath);

        if (! $parser) {
            return [];
        }

        return $parser->parseFile($filePath);
    }
}
