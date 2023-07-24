<?php


/**
 * Helper-функции для строк
 *
 * @author Alexander Vorobyov
 */
namespace Alxnv\Nesttab\core;

class StringHelper {
    
    /**
     * find $needle in $s and split $s in two strings by this needle 
     * @param string $needle
     * @param string $s
     * @return array [$s1, $s2]
     */
    public static function splitByFirst(string $needle, string $s) {
        $i = mb_strpos($s, $needle);
        if ($i === false) return ['', $s];
        return [mb_substr($s, 0, $i), mb_substr($s, $i + 1)];
    }
    
}
