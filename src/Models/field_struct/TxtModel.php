<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа txt
 */

namespace Alxnv\Nesttab\Models\field_struct;

class TxtModel extends \Alxnv\Nesttab\Models\field_struct\BasicModel {

    
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
        if (isset($columns[$i]['parameters']['req']) && (trim($value) == '')) {
            $table_recs->setErr($index, __('This string must not be empty'));
        }
        return $value;
    }
    /**
     * Вывод поля таблицы для редактирования
     * @param array $rec - массив с данными поля
     * @param array $errors - массив ошибок
     * @param int $table_id - id of the table
     * @param int $rec_id - 'id' of the record in the table
     * @param array $r - request data of redirected request
     */
    public function editField(array $rec, array $errors, int $table_id, int $rec_id, $r) {
        //echo $e->getErr('default');
        echo \yy::qs($rec['descr']);
        echo '<br />';
        echo '<textarea '
        . ' name="' . $rec['name'] . '" cols="70" rows="5">' . (!is_null($rec['value']) ? \yy::qs($rec['value']) : '') . '</textarea>'
        . '<br />';
        echo '<br />';
    }
    
    
    
}
