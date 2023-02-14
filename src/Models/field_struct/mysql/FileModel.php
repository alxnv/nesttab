<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа file
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class FileModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    
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
        $fh = new \Alxnv\Nesttab\core\FormatHelper();
        
        $default = '';
        if (isset($r['allowed'])) {
            $r['allowed'] = $fh::delimetedByCommaToArray(mb_substr($r['allowed'], 0, 10000));
            $allowed = $r['allowed'];
        } else {
            $allowed = [];
        }
        $params = ['allowed' => $allowed];
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default, $params);

    }
    
    
}
