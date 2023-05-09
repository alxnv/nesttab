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
    
}
