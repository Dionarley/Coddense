<?php

namespace App\Services\Parsers;

class TypeScriptParser implements LanguageParserInterface
{
    public function getSupportedExtensions(): array
    {
        return ['ts', 'tsx'];
    }

    public function getLanguageName(): string
    {
        return 'TypeScript';
    }

    public function parseFile(string $filePath): array
    {
        $code = file_get_contents($filePath);
        $entities = [];

        preg_match_all('/(?:export\s+)?(?:default\s+)?(?:interface\s+(\w+)|type\s+(\w+)|enum\s+(\w+)|function\s+(\w+)|class\s+(\w+)|const\s+(\w+)\s*[=:]/)', $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = $match[1] ?? $match[2] ?? $match[3] ?? $match[4] ?? $match[5] ?? $match[6] ?? null;
            if ($name) {
                $type = isset($match[1]) ? 'interface' : (isset($match[2]) ? 'type' : (isset($match[3]) ? 'enum' : (isset($match[5]) ? 'class' : 'function')));
                $entities[] = [
                    'type' => $type,
                    'name' => $name,
                    'namespace' => null,
                    'file_path' => $filePath,
                    'details' => ['category' => $type],
                ];
            }
        }

        return $entities;
    }
}
