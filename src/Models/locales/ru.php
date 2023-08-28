<?php
namespace Alxnv\Nesttab\Models\locales;

class ru {
    public $format = 'd.m.Y H:i:s';
    
    /**
     * Is it a valid datetime value for this locale
     * @param string $dt - 
     * @return bool - is it a valid datetime value
     */
    public static function isValidValue(string $dt):bool {
        //echo $dt . ' ';
        $b = preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4}) (\d{1,2})\:(\d{2}):(\d{2})$/', $dt, $r);
        if ($b) {
            //dd($r);
            $b2 = checkdate($r[2], $r[1], $r[3]);
            if (!$b2) return false;
            if (($r[4] > 23) || ($r[5] > 59) || ($r[6] > 59)) return false;
            return true;
        } else{
            return false;
        }
    }
}