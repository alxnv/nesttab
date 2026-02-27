<?php


/**
 * Helper-функции для массивов
 *
 * @author Alexander Vorobyov
 */
namespace Alxnv\Nesttab\core;

class ArrayHelper {
    
    /**
     * сортирует по id_col_type и алфавиту элементы с parent_id==$parentId глобальной переменной $td
     * @param int $parentId
     */
    public static function sortGlobalTd(int $parentId) {
        global $td;
        usort($td['cat'][$parentId], function ($a,$b) {
            global $td;
            $ia = is_array($a); // $a is type 2
            $ib = is_array($b); // $b is type 2
            if ($ia == $ib) {
                $aInd = ($ia ? $a[0] : $a);
                $bInd = ($ib ? $b[0] : $b);
                if (!isset($td['tbl'][$aInd]) || !isset($td['tbl'][$bInd])) {
                    return 0; // ошибка, не сортируем
                }
                return strcoll($td['tbl'][$aInd][2], $td['tbl'][$bInd][2]);
            } else {
                if ($ia && (!$ib)) {
                    return 1;
                } else {
                    return -1;
                }
            }
        });
    }
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
