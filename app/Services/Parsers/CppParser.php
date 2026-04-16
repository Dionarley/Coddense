<?php

namespace App\Services\Parsers;

class CppParser implements LanguageParserInterface
{
    public function getSupportedExtensions(): array
    {
        return ['cpp', 'cc', 'cxx', 'c', 'h', 'hpp', 'hh'];
    }

    public function getLanguageName(): string
    {
        return 'C/C++';
    }

    public function parseFile(string $filePath): array
    {
        $code = file_get_contents($filePath);
        $entities = [];

        preg_match_all('/^\s*(?:class|struct|enum|union|namespace|typedef|using|inline|virtual|explicit|template)\s+(?:typename\s+)?(\w+)/m', $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = $match[1];
            $line = trim($match[0]);

            $type = match (true) {
                str_starts_with($line, 'class ') => 'class',
                str_starts_with($line, 'struct ') => 'struct',
                str_starts_with($line, 'enum ') => 'enum',
                str_starts_with($line, 'union ') => 'union',
                str_starts_with($line, 'namespace ') => 'namespace',
                str_starts_with($line, 'typedef ') => 'typedef',
                str_starts_with($line, 'using ') => 'alias',
                default => 'declaration',
            };

            if ($name && strlen($name) > 1) {
                $entities[] = [
                    'type' => $type,
                    'name' => $name,
                    'namespace' => null,
                    'file_path' => $filePath,
                    'details' => ['category' => 'C/C++'],
                ];
            }
        }

        preg_match_all('/(?:void|int|char|float|double|bool|auto|auto|struct|class|enum|auto)\s+(\w+)\s*\([^)]*\)\s*(?:const)?\s*\{/m', $code, $fnMatches, PREG_SET_ORDER);
        foreach ($fnMatches as $fn) {
            $name = $fn[1];
            if ($name && strlen($name) > 1 && ! in_array($name, ['if', 'for', 'while', 'switch', 'main'])) {
                $entities[] = [
                    'type' => 'function',
                    'name' => $name,
                    'namespace' => null,
                    'file_path' => $filePath,
                    'details' => ['category' => 'C/C++'],
                ];
            }
        }

        return $entities;
    }
}
