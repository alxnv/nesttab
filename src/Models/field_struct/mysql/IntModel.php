<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа txt
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class IntModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    
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
            $r['default'] = mb_substr(trim($r['default']), 0, 255);
            $default = $r['default'];
            $fh = new \Alxnv\Nesttab\core\FormatHelper();
            if (false === $fh::IntConv($default)) {
                $this->setErr('default', '"' . $default . '" ' . __('is not valid') . ' ' . __('int value'));
            }
            $default = intval($default);
        } else {
            $default = '';
            $this->setErr('default', '"" ' . __('is not valid') . ' ' . __('int value'));
        }
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default);

    }
    
    
}
