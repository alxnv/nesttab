<?php

/**
 * Description of TableHelper
 *
 * Helper class for tables
 * 
 * @author Alexandr
 */

namespace Alxnv\Nesttab\core;

class TableHelper {
    /**
     * Возвращает для вывода список подтаблиц указанной таблицы, отформатированный с помощью коллбэка
     *   $getValue
     * @param int $id - id of the current table
     * @param string $prefix - class for <p>, or ''
     * @param callable $getValue - коллбэк возвращающий значение для каждой подтаблицы
     * @param callable $additionalParams - дополнительные параметры, передаваемые в функцию
     * @param callable||null $skipCondition - если не null, то возвращает true если элемент массива
     *   нужно пропустить
     * @return string
     */
    public static function childTables(int $tbl_id, string $prefix, callable $getValue,
            array $additionalParams, mixed $skipCondition) {
        global $td;
        $s = '';
        if (isset($td['cat'][$tbl_id])) {
            $s .= '<p' . $prefix . '>';
            foreach ($td['cat'][$tbl_id] as $ind) {
                if (!is_null($skipCondition)) {
                    if ($skipCondition($ind)) continue; // type 2
                }
                $s .= $getValue($ind, $additionalParams);
            }
            $s .= '</p>';
        }
        return $s;
    }
    /**
     * Возвращает из памяти (из $td) если найдена информацию о таблице
     * @global type $td
     * @global \Alxnv\Nesttab\core\type $yy
     * @param int $id
     * @return array
     */
    public static function getOneFromMemory(int $id) {
        global $td, $yy;
        if (!isset($td['ind'][$id]) || !isset($td['tbl'][$id])) {
            \yy::gotoErrorPage('Table not found');
        }
        $row = $td['tbl'][$id];
        $arr = ['id' => $row[0], 'p_id' => $td['ind'][$id][0], 'name' => $row[1], 'descr' => $row[2],
            'table_type' => $row[3]];
        return $arr;
    }
    /**
     * returns long table type name by short table type name
     * @global type $yy
     * @param string $char ('O','L','D','T')
     * @return string ('one', 'list', 'ord', 'tree')
     * @throws \Exception
     */
    public static function getTableTypeByOneChar(string $char):string {
        global $yy;
        if (($key = array_search($char, $yy->settings2['table_names_short'])) === false) {
                throw new \Exception('Bad table type');
        }
        return $yy->settings2['table_names'][$key];
    }
    
}
