<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа html
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class HtmlModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    
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
        if (isset($r['mce_0'])) {
            $r['default'] = $r['mce_0'];
            unset($r['mce_0']);
            $r['default'] = substr($r['default'], 0, $yy->settings2['max_html_size']);
            $default = $r['default'];
        } else {
            $default = '';
        }
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default);

    }
    
    
}
