<?php
/**
 * модель, работающая с записями таблиц
 */

namespace Alxnv\Nesttab\Models;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

class TableRecsModel {
    /**
     * Получаем запись таблицы, добавляя к ней соответствующие объекты для 
     *   различных типов полей
     * @param array $columns - массив структуры полей для данной таблицы
     * @param string $table - имя таблицы
     * @param int $id - идентификатор записи таблицы
     */
    public static function getRecAddObjects(array $columns, string $table, int $id) {
        global $db;
        $rec = $db->q("select * from $table where id=$1", [$id]);
        if (is_null($rec)) \yy::gotoErrorPage('Record not found');
        for ($i = 0; $i < count($columns); $i++) {
            $s2 = '\\Alxnv\\Nesttab\\Models\\field_struct\\' . config('nesttab.db_driver') . '\\'
                    . ucfirst($columns[$i]['name_field']) .'Model';
            $columns[$i]['obj'] = new $s2();
            if (isset($rec[$columns[$i]['name']])) {
                $columns[$i]['value'] = $rec[$columns[$i]['name']];
            } else {
                $columns[$i]['value'] = null;
            }
        }
        return $columns;
    }
}