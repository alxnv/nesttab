<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа auto numbering
 */

namespace Alxnv\Nesttab\Models\field_struct;

class AutoModel extends \Alxnv\Nesttab\Models\field_struct\BasicModel {

    
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
        if (($value == 0) && isset($columns[$i]['parameters']['step'])) {
            $parent_tbl_id = 0; // to replace !!! $table_recs->getParentTableId($table_recs->tbl);
            $s3 = $table_recs->getWhereClauseForAuto($parent_tbl_id); // where clause for searching for the max value
            // set $value to maximum value of auto field value in the table plus 'step' if it is zero
            $value = $table_recs->returnMaxAuto($columns, intval($columns[$i]['parameters']['step']), $s3);
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
     * @param array $extra['selectsInitialValues' - array(<id значения поля из yy_columns для полей типа select> => <initial value>)
     * ]
     */
    public function editField(array $rec, array $errors, int $table_id, int $rec_id, $r, array $extra) {
        //echo $e->getErr('default');
        echo \yy::qs($rec['descr']) . ' (' . __("leave '0' in this field for autonumbering") . ')';
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
        if (isset($r['step'])) {
            $r['step'] = mb_substr(trim($r['step']), 0, 255);
            $step = $r['step'];
            $s = '\\Alxnv\\Nesttab\\core\\db\\' . config('nesttab.db_driver') . '\\FormatHelper';
            $fh = new $s();

            //$fh = new \Alxnv\Nesttab\core\FormatHelper();
            if (false === $fh::IntConv($step)) {
                $this->setErr('step', '"' . $step . '" ' . __('is not valid') . ' ' . __('int value'));
            }
            $step = intval($step);
        } else {
            $step = 0;
            $this->setErr('step', '"" ' . __('is not valid') . ' ' . __('int value'));
        }
        $default = 0;
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default, ['step' => $step],
               ['unsigned' => 1]);

    }
    
    
}
