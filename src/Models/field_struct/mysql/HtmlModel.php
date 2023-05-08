<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа html
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class HtmlModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    
    /**
     * Проверяем на валидность значение $value, и в случае ошибки записываем ее в
     *   $table_recs->err
     * @param type $value
     * @param object $table_recs (TableRecsModel)
     * @param string $index - индекс в массиве ошибок для записи сообщения об ошибке
     */
    public function validate($value, object $table_recs, string $index) {
        return $value;
    }
    /**
     * Вывод поля таблицы для редактирования
     * @param array $rec - массив с данными поля
     * @param array $errors - массив ошибок
     */
    public function editField(array $rec, array $errors) {
        //echo $e->getErr('default');
        echo \yy::qs($rec['descr']);
        \yy::htmlEditor($rec['name'], (!is_null($rec['value']) ? $rec['value'] : ''));
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
            //$r['default'] = $r['mce_0'];
            //unset($r['mce_0']);
            $r['default'] = substr($r['default'], 0, $yy->settings2['max_html_size']);
            $default = $r['default'];
        } else {
            $default = '';
            $r['default'] = '';
        }
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default);

    }
    
    
}
