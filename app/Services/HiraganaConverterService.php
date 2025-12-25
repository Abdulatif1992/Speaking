<?php

namespace App\Services;

use App\Services\JapaneseReading\JapaneseReadingInterface;

class HiraganaConverterService
{
    public function __construct(private JapaneseReadingInterface $reading) {}

    /**
     * String -> hiragana string
     */
    public function toHiraganaString(string $text): string
    {
        $text = $this->preNormalize($text);
        return $this->reading->toHiraganaReading($text);
    }

    /**
     * Array -> hiragana array (original index saqlanadi)
     */
    public function toHiraganaArray(array $texts): array
    {
        $out = [];
        foreach ($texts as $k => $v) {
            $out[$k] = $this->toHiraganaString((string)$v);
        }
        return $out;
    }

    /**
     * Bir yo'la ko'p input: string va listlarni bir paketda aylantirish
     */
    public function toHiraganaPayload(string $userAnswer, array $answers10, array $answers5): array
    {
        return [
            'user_answer' => $this->toHiraganaString($userAnswer),
            'answers10'   => $this->toHiraganaArray($answers10),
            'answers5'    => $this->toHiraganaArray($answers5),
        ];
    }

    /**
     * Sizning oldingi qoidalaringizni shu yerda saqlang:
     * - trim
     * - bo'sh joylar
     * - ignorable belgilar: - . ? 。 ！ 一
     *
     * Eslatma: xohlasangiz bu qismni minimal qoldirish ham mumkin.
     */
    private function preNormalize(string $s): string
    {
        $s = trim($s);

        if (function_exists('mb_convert_kana')) {
            $s = mb_convert_kana($s, 'asKV', 'UTF-8');
        }

        // Siz aytgan belgilar natijaga ta'sir qilmasin:
        $s = preg_replace('/[-\.\?。！一]/u', '', $s);

        $s = preg_replace('/\s+/u', ' ', $s);

        return $s;
    }
}
