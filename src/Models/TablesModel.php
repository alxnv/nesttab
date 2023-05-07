<?php

/**
 * Структура таблиц
 */

namespace Alxnv\Nesttab\Models;


class TablesModel {

    /**
     * Возвращает все таблицы верхнего уровня
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
    /**
     * Возвращает все таблицы верхнего уровня упорядоченные по названию таблиц
     * @global type $yy
     * @global type $db
     * @return array
     */
    public static function getAllByDescr() {

        global $yy, $db;
        
        $list = $db->qlistArr("select id, table_type, name, descr from yy_tables"
                . " where parent_tbl_id=0 order by descr, table_type, id");
                
        return $list;
    }
    
    /**
     * получить содержимое таблицы
     * @param int $id - идентификатор таблицы
     */
    public static function getOne(int $id) {
        global $db;
        $tbl = $db->q("select * from yy_tables where id=$1", [$id]);
        if (is_null($tbl)) \yy::gotoErrorPage('Table not found');
        return $tbl;
    }
}