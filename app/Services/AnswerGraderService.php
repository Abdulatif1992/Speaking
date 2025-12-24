<?php

namespace App\Services;

class AnswerGraderService
{
    public function grade(string $userAnswer, array $answers10, array $answers5, array $opts = []): array
    {
        $user = $this->normalize($userAnswer);

        $defaults = [
            'fuzzy_threshold_10' => 0.90,
            'fuzzy_threshold_5'  => 0.80,
            'fuzzy_max_score'    => 4,
        ];
        $opts = array_merge($defaults, $opts);

        // 10 balliklar: exact yoki wildcard
        [$ok10, $type10, $match10] = $this->checkList($user, $answers10);
        if ($ok10) {
            return [10, $type10, $match10, 1.0];
        }

        // 5 balliklar: exact yoki wildcard
        [$ok5, $type5, $match5] = $this->checkList($user, $answers5);
        if ($ok5) {
            return [5, $type5, $match5, 1.0];
        }

        // fuzzy best-match (10 va 5 listlar orasidan)
        $best = ['sim' => 0.0, 'ans' => null, 'bucket' => null];

        foreach ($answers10 as $ans) {
            $sim = $this->similarityRatio($user, $ans);
            if ($sim > $best['sim']) $best = ['sim' => $sim, 'ans' => $ans, 'bucket' => 10];
        }
        foreach ($answers5 as $ans) {
            $sim = $this->similarityRatio($user, $ans);
            if ($sim > $best['sim']) $best = ['sim' => $sim, 'ans' => $ans, 'bucket' => 5];
        }

        if ($best['bucket'] === 10 && $best['sim'] >= $opts['fuzzy_threshold_10']) {
            return [10, 'fuzzy->10', $best['ans'], $best['sim']];
        }
        if ($best['bucket'] === 5 && $best['sim'] >= $opts['fuzzy_threshold_5']) {
            return [5, 'fuzzy->5', $best['ans'], $best['sim']];
        }

        $partial = (int) floor($best['sim'] * $opts['fuzzy_max_score']);
        $partial = max(0, min($opts['fuzzy_max_score'], $partial));

        return [$partial, 'partial', $best['ans'], $best['sim']];
    }

    private function checkList(string $userNormalized, array $answers): array
    {
        foreach ($answers as $ans) {
            $ansN = $this->normalize((string)$ans);

            // exact
            if ($userNormalized === $ansN) {
                return [true, 'exact', $ans];
            }

            // wildcard
            $regex = $this->wildcardPatternToRegex($ansN);
            if ($regex && preg_match($regex, $userNormalized)) {
                return [true, 'wildcard', $ans];
            }
        }
        return [false, null, null];
    }

    private function normalize(string $s): string
    {
        $s = trim($s);

        if (function_exists('mb_convert_kana')) {
            $s = mb_convert_kana($s, 'asKV', 'UTF-8');
        }

        // Siz aytgan belgilarni olib tashlash:
        // - 一. ? 。 ！
        $s = preg_replace('/[-\.\?。！一]/u', '', $s);

        // bo‘sh joylarni normallashtirish
        $s = preg_replace('/\s+/u', ' ', $s);

        return $s;
    }

    private function wildcardPatternToRegex(string $pattern): ?string
    {
        $pattern = $this->normalize($pattern);

        // "*" yoki "＊" bo'lsa wildcard deb olamiz
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
