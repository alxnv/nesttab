<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа file
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class FileModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    
    /**
     * Вывод поля таблицы для редактирования
     * @param array $rec - массив с данными поля
     * @param array $errors - массив ошибок
     */
    public function editField(array $rec, array $errors) {
        echo \yy::qs($rec['descr']);
        echo '<br />';
        \yy::imageLoad($rec['name']);
        echo '<br />';
        echo '<br />';
    }
    /**
     * пытается сохранить(изменить)  в таблице поле
     * @param array $tbl
     * @param array $fld
     * @param array $r
     */
    public function save(array $tbl, array $fld, array &$r, array $old_values) {
        global $yy, $db;
        $s = '\\Alxnv\\Nesttab\\core\\db\\' . config('nesttab.db_driver') . '\\FormatHelper';
        $fh = new $s();
        
        $default = '';
        if (isset($r['allowed'])) {
            $r['allowed'] = $fh::delimetedByCommaToArray(mb_substr($r['allowed'], 0, 10000));
            $allowed = $r['allowed'];
        } else {
            $allowed = [];
        }
        if (count($allowed) == 0) {
            $this->setErr('allowed', __('You must specify files extensions'));
        }
        $params = ['allowed' => $allowed];
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default, $params);

    }
    
    
}
