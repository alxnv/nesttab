<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа txt
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class NumModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    
    //public function data_type() {
    //    return 'tinyint(4)';
    //}

    /**
     * Вывод поля таблицы для редактирования
     * @param array $rec - массив с данными поля
     * @param array $errors - массив ошибок
     */
    public function editField(array $rec, array $errors) {
        //echo $e->getErr('default');
        echo \yy::qs($rec['descr']);
        echo '<br />';
        echo '<input type="number" size="20" '
            . ' name="' . $rec['name'] . '" value="' . (!is_null($rec['value']) ? \yy::qs($rec['value']) : '') . '" />'
            . '<br />';
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
        if (isset($r['default'])) {
            $r['default'] = mb_substr(trim($r['default']), 0, 255);
            $default = $r['default'];
            $s = '\\Alxnv\\Nesttab\\core\\db\\' . config('nesttab.db_driver') . '\\FormatHelper';
            $fh = new $s();

            //$fh = new \Alxnv\Nesttab\core\FormatHelper();
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
