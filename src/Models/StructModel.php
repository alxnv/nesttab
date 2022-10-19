<?php

/**
 * Структура таблиц
 */

namespace app\models;


class StructModel extends \app\yy\Model {

    /**
     * Возвращает все таблицы
     * @global type $yy
     * @global type $db
     * @return array
     */
    public function getAll() {

        global $yy, $db;
        
        $list = $db->qlist_arr("select id, table_type, name, descr from yy_tables"
                . " where parent_tbl_id=0 order by table_type, descr, id");
                
        return $list;
    }
}