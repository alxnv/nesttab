<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа txt
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class TxtModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    
    //public function data_type() {
    //    return 'tinyint(4)';
    //}

    /**
     * пытается сохранить(изменить)  в таблице поле
     * @param array $tbl
     * @param array $fld
     * @param array $r
     */
    public function save(array $tbl, array $fld, array &$r, array $old_values) {
        global $yy, $db;
        if (isset($r['default'])) {
            $r['default'] = substr($r['default'], 0, $yy->settings2['max_txt_size']);
            $default = $r['default'];
        } else {
            $default = '';
        }
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default);

    }
    
    
}