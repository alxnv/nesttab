<?php


/**
 * Description of FormatHelper
 *
 * @author Alexander Vorobyov
 */
namespace Alxnv\Nesttab\core\db\mysql;

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
        try {
            $n = intval($s);
        } catch (\Exception $e) {
            return false;
        }
        if (($n < -2147483648) || ($n > 2147483647)) return false;
        return $n;
    }
    
    /**
     * 
     * @param string $s
     * @return boolean|int - false, if this is not a string presentation
     *   of $bytes byte unsigned integer,
     *    or this integer otherwise
     */
    public static function intUnsignedBaseConv(string $s, int $bytes) {
        if (!preg_match('/^[\-]?[\d]{1,21}$/', $s)) return false;
        try {
            $n = intval($s);
        } catch (\Exception $e) {
            return false;
        }
        if ($bytes == 8) {
            // max value of int for the memory
            $s = '7FFFFFFFFFFFFFFF';
        } else {
            $s = str_repeat('FF', $bytes);
        }
        if (($n < 0) || ($n > hexdec($s))) return false;
        return $n;
    }
    /**
     * !!! not used
     * @param string $s
     * @return float|int - false, if this is not a string presentation
     *   of mysql float type,
     *    or this float otherwise
     */
    public static function floatConv(string $s) {
        //if (!preg_match('/^[\-]?[\d]{1,255}(\.\d{1,255})?(e\[\-|\+]?\d{1,3})?$/i', $s)) return false;
        if (!preg_match('/^([-+]?\d*\.?\d+)(?:[eE]([-+]?\d+))?$/', $s)) return false;
        try {
            $n = floatval($s);
        } catch (\Exception $e) {
            return false;
        }
        if (($n < -3.402823466E+38) || ($n > 3.402823466E+38)) return false;
        return $n;
    }

    /**
     * 
     * @param string $s
     * @return float|int - false, if this is not a string presentation
     *   of mysql double type,
     *    or this double otherwise
     */
    public static function doubleConv(string $s) {
        //if (!preg_match('/^[\-]?[\d]{1,255}(\.\d{1,255})?(e\[\-|\+]?\d{1,3})?$/i', $s)) return false;
        if (!preg_match('/^([-+]?\d*\.?\d+)(?:[eE]([-+]?\d+))?$/', $s)) return false;
        try {
            $n = floatval($s);
        } catch (\Exception $e) {
            return false;
        }
        if (($n < -1.7976931348623157E+308) || ($n > 1.7976931348623157E+308)) return false;
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
