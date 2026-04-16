<?php

namespace App\Services\Parsers;

class RustParser implements LanguageParserInterface
{
    public function getSupportedExtensions(): array
    {
        return ['rs'];
    }

    public function getLanguageName(): string
    {
        return 'Rust';
    }

    public function parseFile(string $filePath): array
    {
        $code = file_get_contents($filePath);
        $entities = [];

        preg_match_all('/^(?:pub\s+)?(?:struct|enum|union|trait|impl|mod|use|fn|type|const|static)\s+(\w+)/m', $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $fullMatch = trim($match[0]);
            $name = $match[1];

            $type = match (true) {
                str_starts_with($fullMatch, 'struct ') => 'struct',
                str_starts_with($fullMatch, 'enum ') => 'enum',
                str_starts_with($fullMatch, 'union ') => 'union',
                str_starts_with($fullMatch, 'trait ') => 'trait',
                str_starts_with($fullMatch, 'impl ') => 'impl',
                str_starts_with($fullMatch, 'mod ') => 'module',
                str_starts_with($fullMatch, 'use ') => 'use',
                str_starts_with($fullMatch, 'fn ') => 'function',
                str_starts_with($fullMatch, 'type ') => 'type_alias',
                str_starts_with($fullMatch, 'const ') => 'constant',
                str_starts_with($fullMatch, 'static ') => 'static',
                default => 'declaration',
            };

            if ($name && strlen($name) > 0 && ! in_array($name, ['Self', 'super', 'crate', 'mod', 'pub', 'ref', 'mut'])) {
                $entities[] = [
                    'type' => $type,
                    'name' => $name,
                    'namespace' => null,
                    'file_path' => $filePath,
                    'details' => ['category' => 'Rust'],
                ];
            }
        }

        return $entities;
    }
}
