<?php


/**
 * Helper-функции для html
 *
 * @author Alexander Vorobyov
 */
namespace Alxnv\Nesttab\core;

class HtmlHelper {
    
    /**
     * make select tag with keys [0 .. count($arr)-1] from array
     * @param array $arr
     * @param int $n
     * @return string
     */
    public static function makeselsimp(&$arr,$n=0) {
        $s='';
        for ($i=0,$j=count($arr);$i<$j;$i++) {
            $s .= '<option '.($i==$n ? 'selected ' : '').'value='.$i.'>'.$arr[$i];
         };
        return $s;
    }
    /**
     * make select tag from array
     * @param array $arr - array of $key => $value
     * @param type $current - key of the current element in select
     */
    public static function makeselect(array $arr, $current) {
        $s = '';
        foreach ($arr as $key => $value) {
            $s .= '<option '.($current == $key ? 'selected ' : '') 
                    . 'value="' . \yy::qs($key) . '">' . \yy::qs($value);
        }
        return $s;
    }
    
}
