<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа boolean
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class BoolModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    /**
     * Проверяем на валидность значение $value, и в случае ошибки записываем ее в
     *   $table_recs->err
     * !!! никогда не выдает ошибку, так как это checkbox
     * @param type $value
     * @param object $table_recs (TableRecsModel)
     * @param string $index - индекс в массиве ошибок для записи сообщения об ошибке
     * @param array $columns - массив всех колонок таблицы
     * @param int $i - индекс текущего элемента в $columns
     * @return mixed - возвращает валидированное (и, возможно, обработанное) значение
     *   текущего поля
     */
    public function validate($value, object $table_recs, string $index, array $columns, int $i) {
        return $value;
    }
    
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
