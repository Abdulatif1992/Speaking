<?php

namespace App\Services\JapaneseReading;

interface JapaneseReadingInterface
{
    public function toHiraganaReading(string $text): string;
}
