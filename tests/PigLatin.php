<?php

class PigLatin
{
    /**
     * Converts a word from English to PigLatin
     * @param  string $word - word in English
     * @return string       - word in PigLatin
     */
    public function convert($word)
    {
        if (empty($word)) return '';

        $firstLetter = substr($word, 0, 1);
        $pigLatinWord = ltrim($word, $firstLetter) . $firstLetter . 'ay';

        return $pigLatinWord;
    }
}
