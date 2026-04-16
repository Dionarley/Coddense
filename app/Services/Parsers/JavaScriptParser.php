<?php

namespace App\Services\Parsers;

class JavaScriptParser implements LanguageParserInterface
{
    public function getSupportedExtensions(): array
    {
        return ['js', 'jsx', 'mjs', 'cjs'];
    }

    public function getLanguageName(): string
    {
        return 'JavaScript';
    }

    public function parseFile(string $filePath): array
    {
        $code = file_get_contents($filePath);
        $entities = [];

        preg_match_all('/(?:export\s+)?(?:default\s+)?(?:function\s+(\w+)|class\s+(\w+))/', $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = $match[1] ?? $match[2] ?? null;
            if ($name && strlen($name) > 1) {
                $type = isset($match[2]) ? 'class' : 'function';
                $entities[] = [
                    'type' => $type,
                    'name' => $name,
                    'namespace' => null,
                    'file_path' => $filePath,
                    'details' => [],
                ];
            }
        }

        return $entities;
    }
}
