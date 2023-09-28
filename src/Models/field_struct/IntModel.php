<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа txt
 */

namespace Alxnv\Nesttab\Models\field_struct;

class IntModel extends \Alxnv\Nesttab\Models\field_struct\BasicModel {

    
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
        $s = '\\Alxnv\\Nesttab\\core\\db\\' . config('nesttab.db_driver') . '\\FormatHelper';
        $fh = new $s();

        //$fh = new \Alxnv\Nesttab\core\FormatHelper();
        if (false === $fh::IntConv($value)) {
            $table_recs->setErr($index, '"' . $value . '" ' . __('is not valid') . ' ' . __('int value'));
        }
        $value = intval($value);
        if (isset($columns[$i]['parameters']['req']) && ($value == 0)) {
            $table_recs->setErr($index, __('This value must not be equal to') . ' 0');
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
     * @param array $selectsInitialValues - array(<id значения поля из yy_columns для полей типа select> => <initial value>)
     */
    public function editField(array $rec, array $errors, int $table_id, int $rec_id, $r, array $selectsInitialValues) {
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
