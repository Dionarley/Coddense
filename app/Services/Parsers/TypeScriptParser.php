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

        preg_match_all('/export\s+interface\s+(\w+)/', $code, $interfaces);
        foreach ($interfaces[1] as $name) {
            $entities[] = ['type' => 'interface', 'name' => $name, 'namespace' => null, 'file_path' => $filePath];
        }

        preg_match_all('/type\s+(\w+)\s*=/', $code, $types);
        foreach ($types[1] as $name) {
            $entities[] = ['type' => 'type', 'name' => $name, 'namespace' => null, 'file_path' => $filePath];
        }

        preg_match_all('/enum\s+(\w+)/', $code, $enums);
        foreach ($enums[1] as $name) {
            $entities[] = ['type' => 'enum', 'name' => $name, 'namespace' => null, 'file_path' => $filePath];
        }

        preg_match_all('/function\s+(\w+)/', $code, $functions);
        foreach ($functions[1] as $name) {
            $entities[] = ['type' => 'function', 'name' => $name, 'namespace' => null, 'file_path' => $filePath];
        }

        preg_match_all('/class\s+(\w+)/', $code, $classes);
        foreach ($classes[1] as $name) {
            $entities[] = ['type' => 'class', 'name' => $name, 'namespace' => null, 'file_path' => $filePath];
        }

        return $entities;
    }
}
