<?php

namespace App\Services\JapaneseReading;

class MecabCliReading implements JapaneseReadingInterface
{
    public function __construct(
        private string $mecabExePath,
        private ?string $dicPath = null
    ) {}

    public function toHiraganaReading(string $text): string
    {
        $text = trim($text);
        if ($text === '') return '';

        $readingKatakana = $this->mecabToReadingKatakana($text);

        // reading bo'lmasa fallback (kana unify bo'lsa ham foydali)
        if ($readingKatakana === '') {
            return $this->katakanaToHiragana($text);
        }

        return $this->katakanaToHiragana($readingKatakana);
    }

    private function mecabToReadingKatakana(string $text): string
    {
        $cmd = '"' . $this->mecabExePath . '"';
        if ($this->dicPath) {
            $cmd .= ' -d "' . $this->dicPath . '"';
        }

        $descriptors = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ];

        $proc = proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($proc)) return '';

        // UTF-8 yuboramiz (sizda hozir MeCab to'g'ri ko'rsatdi)
        fwrite($pipes[0], $text . "\n");
        fclose($pipes[0]);

        $out = stream_get_contents($pipes[1]) ?: '';
        $err = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exit = proc_close($proc);
        if ($exit !== 0 || $out === '') {
            return '';
        }

        $reading = '';
        $lines = preg_split("/\r\n|\n|\r/", $out);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line === 'EOS') continue;

            // surface \t feature
            $parts = explode("\t", $line, 2);
            if (count($parts) < 2) continue;

            [$surface, $feature] = $parts;
            $cols = explode(',', $feature);

            // IPADIC: reading odatda 7-index (8-ustun)
            $yomi = $cols[7] ?? '';

            if ($yomi === '' || $yomi === '*') {
                $reading .= $surface;
            } else {
                $reading .= $yomi; // katakana
            }
        }

        return $reading;
    }

    private function katakanaToHiragana(string $s): string
    {
        $chars = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY);
        $out = '';

        foreach ($chars as $ch) {
            $code = mb_ord($ch, 'UTF-8');

            // Katakana: 30A1..30F6 => Hiragana: 3041..3096 (minus 0x60)
            if ($code >= 0x30A1 && $code <= 0x30F6) {
                $out .= mb_chr($code - 0x60, 'UTF-8');
            } else {
                $out .= $ch;
            }
        }

        return $out;
    }
}
