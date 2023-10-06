<?php

/*
 * ColumnsModel adapter for mysql
 */

namespace Alxnv\Nesttab\Models\mysql;

/**
 * Description of ColumnsModel
 *
 * @author Alexandr
 */
class ColumnsModel {
    public $main; // main object for this model (Models\ColumnsModel)
    
    public function init($mainObject) {
        $this->main = $mainObject;
        
    }

    /**
     * Получение отображаемого значения вычисляемого поля запроса к БД
     * @global type $db
     * @global type $yy
     * @param array $names - имена полей для участия в получении отображаемого
     *   значения поля
     * @return string - строка типа trim(name) + ' | ' + trim(bool) ...
     */
    public function getSelectCalcField(array $names) {
        global $db, $yy;
        $ar2 = [];
        foreach ($names as $name) {
            $ar2[] = 'trim(' . $db->nameEscape($name) . ')';
        }
        $s2 = 'concat(' . 
                join(", '" . config('nesttab.select_fld_delimeter') . "', ", $ar2) .
                ')';
        return $s2;
    }        
    
}
