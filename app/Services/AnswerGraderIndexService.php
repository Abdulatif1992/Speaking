<?php

namespace App\Services;

class AnswerGraderIndexService
{
    /**
     * Endi: bitta list qabul qiladi va topilgan elementning indexini qaytaradi.
     *
     * Return format:
     * [
     *   ?int   $index,        // topilsa: array key/index, topilmasa: null
     *   string $reason,       // exact | wildcard | fuzzy | partial | not_found
     *   ?string $matched,     // original answer (listdagi qiymat)
     *   float  $similarity    // 0..1
     * ]
     */
    public function grade(string $userAnswer, array $answers, array $opts = []): array
    {
        $user = $this->normalize($userAnswer);

        $defaults = [
            // bitta list bo'lgani uchun bitta threshold kifoya
            'fuzzy_threshold' => 0.90,
            'fuzzy_max_score' => 2,   // eski logikani saqlab qolamiz (partial)
            // partial natijani "topildi" deb hisoblash uchun minimal similarity
            // (xohlasangiz 0.0 qoldiring)
            'partial_min_similarity' => 0.85,
            // topilmasa qaytariladigan index: null yoki -1
            'not_found_index' => null,
        ];
        $opts = array_merge($defaults, $opts);

        // 1) exact / wildcard: index bilan qaytaradi
        [$ok, $type, $match, $idx] = $this->checkList($user, $answers);
        if ($ok) {
            return [$this->calculateGrade($idx, $answers), $idx, $type, $match, 1.0];
        }

        // 2) fuzzy best-match: list ichidan eng yaqinini topamiz
        $best = ['sim' => 0.0, 'ans' => null, 'idx' => null];

        foreach ($answers as $i => $ans) {
            $sim = $this->similarityRatio($user, (string)$ans);
            if ($sim > $best['sim']) {
                $best = ['sim' => $sim, 'ans' => (string)$ans, 'idx' => $i];
            }
        }

        // 3) Threshold yetarli bo'lsa — shu element indexini qaytaramiz
        if ($best['idx'] !== null && $best['sim'] >= $opts['fuzzy_threshold']) {
            return [$this->calculateGrade($best['idx'], $answers), 'fuzzy', $best['ans'], $best['sim']];
        }

        // 4) partial (eski logika saqlanadi), lekin endi ball emas:
        // Agar similarity partial_min_similarity dan yuqori bo'lsa, "eng yaqin" indexni qaytaramiz.
        $partial = (int) floor($best['sim'] * $opts['fuzzy_max_score']);
        $partial = max(0, min($opts['fuzzy_max_score'], $partial));

        if ($best['idx'] !== null && $best['sim'] >= $opts['partial_min_similarity'] && $partial > 0) {
            return [$this->calculateGrade($best['idx'], $answers), 'partial', $best['ans'], $best['sim']];
        }

        // 5) topilmadi
        return [$opts['not_found_index'], 'not_found', null, $best['sim'] ?? 0.0];
    }

    /**
     * Endi index ham qaytaradi.
     * Return: [bool $ok, ?string $type, ?string $match, ?int|string $idx]
     */
    private function calculateGrade($index ,$answers){
        return round(10 - (10/count($answers) * $index), 1);
    }
    
    private function checkList(string $userNormalized, array $answers): array
    {
        foreach ($answers as $idx => $ans) {
            $ansN = $this->normalize((string)$ans);

            // exact
            if ($userNormalized === $ansN) {
                return [true, 'exact', (string)$ans, $idx];
            }

            // wildcard
            $regex = $this->wildcardPatternToRegex($ansN);
            if ($regex && preg_match($regex, $userNormalized)) {
                return [true, 'wildcard', (string)$ans, $idx];
            }
        }
        return [false, null, null, null];
    }

    private function normalize(string $s): string
    {
        $s = trim($s);

        if (function_exists('mb_convert_kana')) {
            $s = mb_convert_kana($s, 'asKV', 'UTF-8');
        }

        // Siz aytgan belgilar natijaga ta'sir qilmasin
        $s = preg_replace('/[-\.\?。！一]/u', '', $s);

        $s = preg_replace('/\s+/u', ' ', $s);

        return $s;
    }

    private function wildcardPatternToRegex(string $pattern): ?string
    {
        $pattern = $this->normalize($pattern);

        if (!preg_match('/[*＊]/u', $pattern)) {
            return null;
        }

        $escaped = preg_quote($pattern, '/');
        $escaped = preg_replace('/\\\\\*|\\\\＊/u', '.*', $escaped);

        return '/^' . $escaped . '$/u';
    }

    private function similarityRatio(string $s1, string $s2): float
    {
        $s1 = $this->normalize($s1);
        $s2 = $this->normalize($s2);

        $len1 = mb_strlen($s1, 'UTF-8');
        $len2 = mb_strlen($s2, 'UTF-8');
        $maxLen = max($len1, $len2);

        if ($maxLen === 0) return 1.0;

        $dist = $this->mbLevenshtein($s1, $s2);
        $ratio = 1.0 - ($dist / $maxLen);

        return max(0.0, min(1.0, $ratio));
    }

    private function mbLevenshtein(string $s1, string $s2): int
    {
        $a = preg_split('//u', $s1, -1, PREG_SPLIT_NO_EMPTY);
        $b = preg_split('//u', $s2, -1, PREG_SPLIT_NO_EMPTY);

        $n = count($a);
        $m = count($b);

        if ($n === 0) return $m;
        if ($m === 0) return $n;

        $dp = [];
        for ($i = 0; $i <= $n; $i++) $dp[$i] = $i;

        for ($j = 1; $j <= $m; $j++) {
            $prev = $dp[0];
            $dp[0] = $j;

            for ($i = 1; $i <= $n; $i++) {
                $temp = $dp[$i];
                $cost = ($a[$i - 1] === $b[$j - 1]) ? 0 : 1;

                $dp[$i] = min(
                    $dp[$i] + 1,
                    $dp[$i - 1] + 1,
                    $prev + $cost
                );

                $prev = $temp;
            }
        }

        return $dp[$n];
    }
}
