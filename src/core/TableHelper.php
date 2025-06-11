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
     * @return string
     */
    public static function childTables(int $tbl_id, string $prefix, callable $getValue,
            array $additionalParams) {
        global $td;
        $s = '';
        if (isset($td['cat'][$tbl_id])) {
            $s .= '<p' . $prefix . '>';
            foreach ($td['cat'][$tbl_id] as $ind) {
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
        if (!isset($td['ind'][$id]) || !isset($td['dat'][$td['ind'][$id]])) {
            \yy::gotoErrorPage('Table not found');
        }
        $row = $td['dat'][$td['ind'][$id]];
        $arr = ['id' => $row[0], 'p_id' => $row[1], 'name' => $row[2], 'descr' => $row[3],
            'table_type' => $row[4]];
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
