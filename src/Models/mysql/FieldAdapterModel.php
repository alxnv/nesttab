<?php

/**
	Models\field_struct\<object> Adapter model for mysql
	
*/
namespace Alxnv\Nesttab\Models\mysql;

class FieldAdapterModel {
    protected $fs;
    /**
     * 
     * @param type $fs - Models\field_struct\<model>
     */
    public function init($fs) {
        $this->fs = $fs;
    }
    
    /**
     * пытаемся сохранить данные о полях вывода поля типа select в yy_select
     * @param array $tbl - данные о текущей таблице которой принадлежит поле
     * @param array $fld - данные о типе текущего поля из yy_col_types
     * @param array $r - Request
     * @param array $old_values - []
     * @param int $link_table_id - id таблицы на которую делаем ссылку
     */
    public function saveSelectValues(array $tbl, array $fld, array &$r, array $old_values,
            int $link_table_id) {
        // $r['id'] - id поля из yy_columns
        global $db, $yy;
        $db->qdirect('lock tables yy_select write, yy_columns read');
        $arr = [];
        foreach ($r['flds'] as $value) {
            $arr[] = intval($value);
        }
        $s = join(', ', $arr);
        // получаем все поля на которые переданы в $r ссылки bp yy_columns
        $col_flds = $db->qlistArr("select * from yy_columns where "
                . " id in ($s)", [$r['id']]);
        if (count($col_flds) == 0) {
            $this->fs->setErr('', __('Choose at least one field'));
        } else {
            $db->qdirect('delete from yy_select where src_fld_id = $1',
                    [$r['id']]);
            $ar3 = [];
            $i = 1;
            foreach ($arr as $value) {
                $ar3[] = '(' . $r['id']. ', ' . $i . ', ' . $value . ')';
                $i++;
            }
            $s2 = join(', ', $ar3);
            $db->qdirect("insert into yy_select (src_fld_id, ordr, fld_id) values $s2");
        }
        
        $db->qdirectNoErrorMessage('unlock tables');
    }
    
}