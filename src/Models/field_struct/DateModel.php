<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа txt

 * 
 * MySQL извлекает и выводит величины DATETIME в формате 'YYYY-MM-DD HH:MM:SS'
 *  */

namespace Alxnv\Nesttab\Models\field_struct;

use Carbon\Carbon;

class DateModel extends \Alxnv\Nesttab\Models\field_struct\BasicModel {

    
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
        global $yy;
        
        $value = trim($value);
        if (($value == '') || is_null($value)) {
            $value = Carbon::now()->format($yy->formatDate); // current datetime
            $vdb = Carbon::createFromFormat($yy->formatDate, $value)->toDateString();
            $columns[$i]['value_for_db'] = $vdb;
        } else {
            if (!$yy->localeObj->isValidDate($value)) {
                $table_recs->setErr($index, __('Not valid value'));
            } else {
                // get string in format 'YYYY-MM-DD HH:ii:ss' and tell to save $vdb 
                //  value to database, not $value
                $vdb = Carbon::createFromFormat($yy->formatDate, $value)->toDateString();

                $columns[$i]['value_for_db'] = $vdb;
                
            }
        }
        
        return $value;
    }
    /**
     * Преобразовать если нужно данные из БД перед редактированием в форме
     * @param array $columns
     * @param int $index - индекс текущего элемента в $columns
     */
    public function convertDataForInput(array &$columns, int $index) {
        global $yy;
        if (array_key_exists('value', $columns[$index])) {
            if (is_null($columns[$index]['value'])) {
                $s = '';
            } else {
                $s = (new Carbon($columns[$index]['value']))->format($yy->formatDate);
            }
            $columns[$index]['value'] = $s;
        }
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
        echo '<br />';
/*        echo '<input type="text" size="20" '
        . '  data-role="datebox" '
            . ' name="' . $rec['name'] . '"  data-options=' . "'" . '{"mode":"datebox"}' . "'" . ' />';*/
//echo '<input data-role="datebox" data-options=' . "'" . '{"mode":"slidebox"}' . "'" . ' />';
        echo '<input type="text" size="20" '
        . '  data-role="datebox" data-options=' . "'" . '{"mode":"datebox", "displayDropdownPosition" : "topRight"}' . "'"
//        . '  data-role="datebox" data-options=' . "'" . '{"mode":"calbox", "maxDays": 10, "minDays": 10, "displayDropdownPosition" : "topRight"}' . "'"
            . ' name="' . $rec['name'] . '" value="' . (!is_null($rec['value']) ? \yy::qs($rec['value']) : '') . '" />';

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
        if (isset($r['default']) && (trim($r['default']) <> '')) {
            $r['default'] = mb_substr(trim($r['default']), 0, 255);
            $default = $r['default'];
            if (!$yy->localeObj->isValidDate($default)) {
                $this->setErr('default', __('Not valid value'));
            } else {
                $default = Carbon::createFromFormat($yy->formatDate, $default)
                        ->toDateString();
            }
        } else {
            $default = '';
        }
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default,
                [], ['isNull' => 1, 'defaultForPhys' => ($default == '' ?
                null : $default)]);

    }
    
    
}
