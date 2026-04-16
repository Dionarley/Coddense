<?php

namespace App\Services\Parsers;

class JavaParser implements LanguageParserInterface
{
    public function getSupportedExtensions(): array
    {
        return ['java', 'jsp'];
    }

    public function getLanguageName(): string
    {
        return 'Java';
    }

    public function parseFile(string $filePath): array
    {
        $code = file_get_contents($filePath);
        $entities = [];

        preg_match_all('/(?:public|private|protected|static|abstract|final|class|interface|enum|record|sealed)\s+(?:class|interface|enum|record|sealed)?\s*(\w+)/m', $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $fullMatch = trim($match[0]);
            $name = $match[1];

            if (str_contains($fullMatch, ' class ') || str_starts_with($fullMatch, 'class ')) {
                $type = 'class';
            } elseif (str_contains($fullMatch, ' interface ') || str_starts_with($fullMatch, 'interface ')) {
                $type = 'interface';
            } elseif (str_contains($fullMatch, ' enum ') || str_starts_with($fullMatch, 'enum ')) {
                $type = 'enum';
            } elseif (str_contains($fullMatch, ' record ') || str_starts_with($fullMatch, 'record ')) {
                $type = 'record';
            } elseif (str_contains($fullMatch, ' sealed ') || str_starts_with($fullMatch, 'sealed ')) {
                $type = 'sealed';
            } else {
                continue;
            }

            if ($name && strlen($name) > 1 && ! in_array($name, ['void', 'int', 'boolean', 'String', 'class', 'interface', 'enum'])) {
                $entities[] = [
                    'type' => $type,
                    'name' => $name,
                    'namespace' => null,
                    'file_path' => $filePath,
                    'details' => ['category' => 'Java'],
                ];
            }
        }

        preg_match_all('/(?:public|private|protected)\s+(?:static\s+)?(?:final\s+)?(?:\w+|<[^>]+>)\s+(\w+)\s*[=;]/m', $code, $propMatches, PREG_SET_ORDER);
        foreach ($propMatches as $prop) {
            $name = $prop[1];
            if ($name && strlen($name) > 1) {
                $entities[] = [
                    'type' => 'field',
                    'name' => $name,
                    'namespace' => null,
                    'file_path' => $filePath,
                    'details' => ['category' => 'Java'],
                ];
            }
        }

        return $entities;
    }
}
