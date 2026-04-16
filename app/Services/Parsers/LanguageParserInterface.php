<?php

namespace App\Services\Parsers;

interface LanguageParserInterface
{
    public function getSupportedExtensions(): array;

    public function getLanguageName(): string;

    public function parseFile(string $filePath): array;
}
