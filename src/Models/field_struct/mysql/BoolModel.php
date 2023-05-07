<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа boolean
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class BoolModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    
    /**
     * Вывод поля таблицы для редактирования
     * @param array $rec - массив с данными поля
     * @param array $errors - массив ошибок
     */
    public function editField(array $rec, array $errors) {
        //echo $e->getErr('default');
        echo '<input type="checkbox" id="' . $rec['name'] . '"'
                . ' name="' . $rec['name'] . '" ' .($rec['value'] ? 'checked="checked"' : '') . ' />'
                . ' <label for="' . $rec['name'] . '">' . \yy::qs($rec['descr']) .'</label><br />';
        echo '<br />';
    }
    
    //public function data_type() {
    //    return 'tinyint(4)';
    //}

    /**
     * пытается сохранить(изменить)  в таблице поле
     * @param array $tbl
     * @param array $fld
     * @param array $r
     */
    public function save(array $tbl, array $fld, array $r, array $old_values) {
        global $yy, $db;
        $default = (isset($r['default']) ? 1 : 0);
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default);

    }
    
    
}
