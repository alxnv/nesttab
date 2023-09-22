<?php


/**
 * Helper-функции для массивов
 *
 * @author Alexander Vorobyov
 */
namespace Alxnv\Nesttab\core;

class ArrayHelper {
    
    /**
     * Выполняет функцию для каждого элемента массива и возвращает обработанный новый массив
     * @param array $arr
     * @param type $funct
     */
    public static function forArray(array $arr, $funct) {
        $arr2 = [];
        foreach ($arr as $value) {
            $arr2[] = $funct($value);
        }
        return $arr2;
    }

    /**
     * make array with keys and values equal to values of another array
     * @param array $arr
     * @return array
     */
    public static function keyLikeValue(array $arr) {
        $arr2 = [];
        foreach ($arr as $value) {
            $arr2[$value] = $value;
        }
        return $arr2;
    }
    
    /**
     * Make array with keys = value of $arr[$i][$fld], values = $i (index in $arr)
     * @param array $arr - input array
     * @return array 
     */
    public static function getArrayIndexes(array $arr, $fld) {
        $ar2 = [];
        for ($i = 0; $i < count($arr); $i++) {
            $ar2[$arr[$i][$fld]] = $i;
        }
        return $ar2;
    }
    
}
