<?php

namespace App\Services;

class TechnicalDebtScanner
{
    private const DEBT_PATTERNS = [
        ['id' => 'TD01', 'type' => 'TODO Comment', 'severity' => 'LOW', 'category' => 'documentation', 'pattern' => '/\/\/\s*TODO|\/\*\s*TODO|\#\s*TODO/i'],
        ['id' => 'TD02', 'type' => 'FIXME Comment', 'severity' => 'MEDIUM', 'category' => 'bug', 'pattern' => '/\/\/\s*FIXME|\/\*\s*FIXME|\#\s*FIXME/i'],
        ['id' => 'TD03', 'type' => 'HACK Comment', 'severity' => 'MEDIUM', 'category' => 'workaround', 'pattern' => '/\/\/\s*HACK|\/\*\s*HACK|\#\s*HACK/i'],
        ['id' => 'TD04', 'type' => 'XXX Comment', 'severity' => 'MEDIUM', 'category' => 'warning', 'pattern' => '/\/\/\s*XXX|\/\*\s*XXX|\#\s*XXX/i'],
    ];

    public function scan(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        $debts = [];

        foreach (self::DEBT_PATTERNS as $pattern) {
            if (preg_match_all($pattern['pattern'], $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $offset = is_array($match) ? $match[1] : 0;
                    $lineNumber = $this->getLineNumber($content, $offset);
                    $debts[] = [
                        'type' => $pattern['type'],
                        'severity' => $pattern['severity'],
                        'category' => $pattern['category'],
                        'cwe_id' => $pattern['id'],
                        'line' => $lineNumber,
                        'code' => trim($lines[$lineNumber - 1] ?? ''),
                    ];
                }
            }
        }

        return $debts;
    }

    public function scanContent(string $content): array
    {
        $lines = explode("\n", $content);
        $debts = [];

        foreach (self::DEBT_PATTERNS as $pattern) {
            if (preg_match_all($pattern['pattern'], $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $offset = is_array($match) ? $match[1] : 0;
                    $lineNumber = $this->getLineNumber($content, $offset);
                    $debts[] = [
                        'type' => $pattern['type'],
                        'severity' => $pattern['severity'],
                        'category' => $pattern['category'],
                        'cwe_id' => $pattern['id'],
                        'line' => $lineNumber,
                        'code' => trim($lines[$lineNumber - 1] ?? ''),
                    ];
                }
            }
        }

        return $debts;
    }

    private function getLineNumber(string $content, int $offset): int
    {
        return substr_count(substr($content, 0, $offset), "\n") + 1;
    }
}
