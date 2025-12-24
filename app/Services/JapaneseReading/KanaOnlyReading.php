<?php

namespace App\Services\JapaneseReading;

class KanaOnlyReading implements JapaneseReadingInterface
{
    public function toHiraganaReading(string $text): string
    {
        $text = trim($text);
        return $this->katakanaToHiragana($text);
    }

    private function katakanaToHiragana(string $s): string
    {
        $chars = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY);
        $out = '';

        foreach ($chars as $ch) {
            $code = mb_ord($ch, 'UTF-8');

            // Katakana: 30A1..30F6 -> Hiragana: 3041..3096
            if ($code >= 0x30A1 && $code <= 0x30F6) {
                $out .= mb_chr($code - 0x60, 'UTF-8');
            } else {
                $out .= $ch;
            }
        }

        return $out;
    }
}
