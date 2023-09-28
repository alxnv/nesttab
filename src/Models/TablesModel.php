<?php

/**
 * yy_tables
 */

namespace Alxnv\Nesttab\Models;


class TablesModel {

    /**
     * Returns data for select, data is taken from $this->getAllForSelect()
     * @param array $fromDb
     * @return array
     */
    public function getAllTablesSelectData(array $fromDb) {
        global $yy;
        $arr = [0 => '-- ' . __('choose a table') .' --'];
        //for ($i = 1; $i < 300; $i++) $arr[$i] = 'Value';
        foreach ($fromDb as $rec) {
            $arr[$rec['id']] = \yy::qs($rec['descr'] . ' (' . $rec['name'] . ')');
        }
        return $arr;
    }
    
    /**
     * Возвращает данные всех таблиц нулевого уровня типа "L" и "D"
     *  (выводится в <select> при создании нового поля типа select)
     * @global \Alxnv\Nesttab\Models\type $yy
     * @global \Alxnv\Nesttab\Models\type $db
     * @return array - данные списка всех таблиц нулевого уровня типа "L" и "D"
     */
    public function getAllForSelect() {
        global $yy, $db;

        // выбираем из таблиц типов 'list','tree' и 'ord'
        $allowed_types = \Alxnv\Nesttab\core\db\BasicTableHelper::getSelectTablesTypes(); 
        $list = $db->qlistArr("select id, table_type, name, descr from yy_tables"
                . " where parent_tbl_id = 0 and table_type in $allowed_types"
                . " order by descr, id");
                
        return $list;
    }
            
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
     *  в случае ошибки перейти на страницу с ошибкой
     * @param int $id - идентификатор таблицы
     * @return array - строку из бд с информацией об этой бд
     */
    public static function getOne(int $id) {
        global $db;
        $tbl = $db->q("select * from yy_tables where id=$1", [$id]);
        if (is_null($tbl)) \yy::gotoErrorPage('Table not found');
        return $tbl;
    }
    /**
     * получить содержимое таблицы
     *  в случае ошибки перейти на страницу с ошибкой
     * @param int $id - идентификатор таблицы
     * @return array|null - строку из бд с информацией об этой бд,
     *   или null, если запись не найдена
     */
    public static function getOneRetError(int $id, string &$errorMessage) {
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
    public static function getOneAjax(int $id) {
        global $db;
        $tbl = $db->q("select * from yy_tables where id=$1", [$id]);
        if (is_null($tbl)) \App::abort(404);
        return $tbl;
    }
}