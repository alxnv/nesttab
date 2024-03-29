<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа html
 */

namespace Alxnv\Nesttab\Models\field_struct;

class HtmlModel extends \Alxnv\Nesttab\Models\field_struct\BasicModel {

    
    /**
     * Проверяем на валидность значение $value, и в случае ошибки записываем ее в
     *   $table_recs->err
     * @param type $value
     * @param object $table_recs (Models/table/BasicTableModel)
     * @param string $index - индекс в массиве ошибок для записи сообщения об ошибке
     * @param array $columns - массив всех колонок таблицы
     * @param int $i - индекс текущего элемента в $columns
     * @param array $r - (array)Request
     * @return mixed - возвращает валидированное (и, возможно, обработанное) значение
     *   текущего поля
     */
    public function validate($value, object $table_recs, string $index, array &$columns, int $i, array &$r) {
        return $value;
    }
    /**
     * Вывод поля таблицы для редактирования
     * @param array $rec - массив с данными поля
     * @param array $errors - массив ошибок
     * @param int $table_id - id of the table
     * @param int $rec_id - 'id' of the record in the table
     * @param array $r - request data of redirected request
     * @param array $extra['selectsInitialValues' - array(<id значения поля из yy_columns для полей типа select> => <initial value>)
     * ]
     */
    public function editField(array $rec, array $errors, int $table_id, int $rec_id, $r, array $extra) {
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
