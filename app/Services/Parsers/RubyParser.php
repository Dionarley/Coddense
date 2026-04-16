<?php

namespace App\Services\Parsers;

class RubyParser implements LanguageParserInterface
{
    public function getSupportedExtensions(): array
    {
        return ['rb', 'erb', 'rake'];
    }

    public function getLanguageName(): string
    {
        return 'Ruby';
    }

    public function parseFile(string $filePath): array
    {
        $code = file_get_contents($filePath);
        $entities = [];

        preg_match_all('/^(?:class|module|def|attr_reader|attr_writer|attr_accessor)\s+(\w+)/m', $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = $match[1];
            $line = $match[0];

            if (str_starts_with($line, 'class ') || str_starts_with($line, 'module ')) {
                $type = str_starts_with($line, 'class ') ? 'class' : 'module';
            } elseif (str_starts_with($line, 'attr_')) {
                $type = 'attribute';
            } else {
                $type = 'method';
            }

            if ($name && strlen($name) > 0 && ! in_array($name, ['initialize', 'new', 'call'])) {
                $entities[] = [
                    'type' => $type,
                    'name' => $name,
                    'namespace' => null,
                    'file_path' => $filePath,
                    'details' => ['category' => 'Ruby'],
                ];
            }
        }

        return $entities;
    }
}
