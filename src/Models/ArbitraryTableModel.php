<?php

/**
 * arbitary tables access procedures
 */

namespace Alxnv\Nesttab\Models;


class ArbitraryTableModel {

    /**
     * Возвращает все таблицы верхнего уровня
     * @global type $yy
     * @global type $db
     * @return array
     */
    /*public function getAll() {

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
    /*public static function getAllByDescr() {

        global $yy, $db;
        
        $list = $db->qlistArr("select id, table_type, name, descr from yy_tables"
                . " where parent_tbl_id=0 order by descr, table_type, id");
                
        return $list;
    }
    
    /**
     * получить содержимое таблицы
     *  в случае ошибки перейти на страницу с ошибкой
     * @param string $table - имя таблицы
     * @param int $id - идентификатор таблицы
     * @return array - строку из бд с информацией об этой бд
     */
    public static function getOne(string $table, int $id) {
        global $db;
        $tableName = $db->nameEscape($table);
        $tbl = $db->q("select * from $tableName where id=$1", [$id]);
        if (is_null($tbl)) \yy::gotoErrorPage('Record not found');
        return $tbl;
    }
    /**
     * получить содержимое таблицы
     *  в случае ошибки перейти на страницу с ошибкой
     * @param int $id - идентификатор таблицы
     * @return array|null - строку из бд с информацией об этой бд,
     *   или null, если запись не найдена
     */
    /*public static function getOneRetError(int $id, string &$errorMessage) {
        global $db;
        $errorMessage = '';
        $tbl = $db->q("select * from yy_tables where id=$1", [$id]);
        if (is_null($tbl)) {
            $errorMessage = 'Table not found';
        }
        return $tbl;
    }
    /**
     * получить содержимое таблицы
     *  при вызове из ajax запроса
     * @param int $id - идентификатор таблицы
     */
    /*public static function getOneAjax(int $id) {
        global $db;
        $tbl = $db->q("select * from yy_tables where id=$1", [$id]);
        if (is_null($tbl)) \App::abort(404);
        return $tbl;
    }*/
}