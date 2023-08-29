<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа txt

 * 
 * MySQL извлекает и выводит величины DATETIME в формате 'YYYY-MM-DD HH:MM:SS'
 *  */

namespace Alxnv\Nesttab\Models\field_struct;

use Carbon\Carbon;

class DatetimeModel extends \Alxnv\Nesttab\Models\field_struct\BasicModel {

    
    /**
     * Проверяем на валидность значение $value, и в случае ошибки записываем ее в
     *   $table_recs->err
     * @param type $value
     * @param object $table_recs (TableRecsModel)
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
            $value = Carbon::now()->format($yy->format); // current datetime
            $vdb = Carbon::createFromFormat($yy->format, $value)->toDateTimeString();
            $columns[$i]['value_for_db'] = $vdb;
        } else {
            if (!$yy->localeObj->isValidValue($value)) {
                $table_recs->setErr($index, __('Not valid value'));
            } else {
                // get string in format 'YYYY-MM-DD HH:ii:ss' and tell to save $vdb 
                //  value to database, not $value
                $vdb = Carbon::createFromFormat($yy->format, $value)->toDateTimeString();

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
        if (isset($columns[$index]['value'])) {
            $s = (new Carbon($columns[$index]['value']))->format($yy->format);
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
     */
    public function editField(array $rec, array $errors, int $table_id, int $rec_id, $r) {
        //echo $e->getErr('default');
        echo \yy::qs($rec['descr']);
        echo '<br />';
        echo '<input type="text" size="20" '
        . '  data-role="datebox" data-options=' . "'" . '{"mode":"datetimebox"}' . "'"
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
            if (!$yy->localeObj->isValidValue($default)) {
                $this->setErr('default', __('Not valid value'));
            } else {
                $default = Carbon::createFromFormat($yy->format, $default)
                        ->toDateTimeString();
            }
        } else {
            $default = '';
        }
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default,
                [], ['isNull' => 1]);

    }
    
    
}
