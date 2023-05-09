<?php


/**
 * Description of FormatHelper
 *
 * @author Alexander Vorobyov
 */
namespace Alxnv\Nesttab\core;

class FormatHelper {
    
    /**
     * Проверить содержится ли строка $needle в массиве строк $haystack (case insensitive)
     * @param string $needle
     * @param array $haystack
     * @return boolean
     */
    public static function inListCaseInsensitive(string $needle, array $haystack) {
        foreach ($haystack as $value) {
            if (mb_strtolower($needle) == mb_strtolower($value)) return true;
        }
        return false;
    }
    /**
     * Проверить, является ли имя расширения файла допустимым для размещения на сервере
     * @param string $a - имя расширения файла
     * @return boolean 
     */
    public static function validExt(string $a) {
        if (strtolower($a) == 'py') return false;
        if (strtolower($a) == 'pl') return false;
        if (str_starts_with(strtolower($a), 'php')) return false;
        return true;
    }
    
            
}
