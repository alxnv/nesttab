<?php

/**
 * Структура таблиц
 */

namespace Alxnv\Nesttab\Models;


class StructModel {

    /**
     * Возвращает все таблицы
     * @global type $yy
     * @global type $db
     * @return array
     */
    public function getAll() {

        global $yy, $db;
        
        $list = $db->qlistArr("select id, table_type, name, descr from yy_tables"
                . " where parent_tbl_id=0 order by table_type, descr, id");
                
        return $list;
    }
}