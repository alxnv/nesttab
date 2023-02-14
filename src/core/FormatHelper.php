<?php


/**
 * Description of FormatHelper
 *
 * @author Alexander Vorobyov
 */
namespace Alxnv\Nesttab\core;

class FormatHelper {
    /**
     * 
     * @param string $s
     * @return boolean|int - false, if this is not a string presentation
     *   of 4 byte signed integer,
     *    or this integer otherwise
     */
    public static function intConv(string $s) {
        if (!preg_match('/^[\-]?[\d]{1,15}$/', $s)) return false;
        $n = intval($s);
        if (($n < -2147483648) || ($n > 2147483647)) return false;
        return $n;
    }
    
    /**
     * 
     * @param string $s - строки, разделенные ","
     * @return array - массив
     */
    public static function delimetedByCommaToArray(string $s) {
        $arr = explode(',', $s);
        for ($i = 0; $i < count($arr); $i++) {
            $arr[$i] = trim($arr[$i]);
        }
        return $arr;
    }
            
}
