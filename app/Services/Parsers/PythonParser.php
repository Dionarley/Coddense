<?php

namespace App\Services\Parsers;

class PythonParser implements LanguageParserInterface
{
    public function getSupportedExtensions(): array
    {
        return ['py'];
    }

    public function getLanguageName(): string
    {
        return 'Python';
    }

    public function parseFile(string $filePath): array
    {
        $code = file_get_contents($filePath);
        $entities = [];

        preg_match_all('/^(?:class|def|async\s+def)\s+(\w+)/m', $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = $match[1];
            $isClass = isset($match[0]) && str_starts_with($match[0], 'class');
            $entities[] = [
                'type' => $isClass ? 'class' : 'function',
                'name' => $name,
                'namespace' => null,
                'file_path' => $filePath,
                'details' => [],
            ];
        }

        return $entities;
    }
}
