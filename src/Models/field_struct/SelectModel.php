<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа select - поля выбора записи из другой таблицы
 */

namespace Alxnv\Nesttab\Models\field_struct;

use Illuminate\Support\Facades\Session;

class SelectModel extends \Alxnv\Nesttab\Models\field_struct\BasicModel {
    /**
     * пытается сохранить(изменить)  в таблице поле
     * @param array $tbl - данные о текущей таблице которой принадлежит поле
     * @param array $fld - данные о типе текущего поля из yy_col_types
     * @param array $r - Request
     * @param array $old_values - []
     */
    public function save(array $tbl, array $fld, array &$r, array $old_values) {
        global $yy, $db;
        
        if (!isset($r['table_id']) || (intval($r['table_id']) == 0)) {
            $this->setErr('table_id', __('The table is not choosen'));
        } else {
            if (!isset($r['flds']) || !is_array($r['flds']) || (count($r['flds']) == 0)) {
                $this->setErr('', __('Choose at least one field'));
            }
        }
        
        if ($this->hasErr()) return;
        $default = 0;
        if (!$this->hasErr()) {
            // сохраняем выбранные поля для поля select
            $link_table_id = intval($r['table_id']);
        };
        $linkedTable = \Alxnv\Nesttab\Models\TablesModel::getOne($link_table_id);
        $intSize = $linkedTable['id_bytes'];
        
        $params = ['link_table_id' => $link_table_id, 'intSize' => $intSize]; // id of the table to link to
        $this->saveStep2($tbl, $fld, $r, $old_values, $default, $params);
        if (!$this->hasErr()) {
            // сохраняем выбранные поля для поля select
            $this->adapter->saveSelectValues($tbl, $fld, $r, $old_values, $link_table_id);
        };
        return;
    }
    
    /**
     * удаление поля из структуры таблицы
     *   !!! контроллер вызывается через ajax
     * @param array $column - запись из yy_columns (структура полей в таблицах)
     * @param array $fld - запись из таблицы определений типов полей
     * @param array $tbl - запись из таблицы yy_tables (данные таблиц)
     * @param array $r - входные параметры скрипта
     * @return string - '', либо строка сообщения об ошибке
     */
    public function delete(array $column, array $fld, array $tbl, array $r) {
        global $yy, $db;
        
        $db->qdirect("delete from yy_select where src_fld_id = $1", [$column['id']]);

        $err = parent::delete($column, $fld, $tbl, $r);
        return $err;
    }
    /**
     * Получить все данные об отображаемых полях для поля типа select
     * @global type $db
     * @global type $yy
     * @param type $fld_id - id поля типа select
     * @return type
     */
    public function getSelectData($fld_id) {
        global $db, $yy;
        $arr = $db->qlistArr("select * from yy_select where src_fld_id = $1 "
                . " order by ordr", [$fld_id]);
        $ar2 = [];
        foreach ($arr as $rec) {
            $ar2[] = $rec['fld_id'];
        }
        return $ar2;
    }
    
    
}
