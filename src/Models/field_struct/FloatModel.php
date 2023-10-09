<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа double
 */

namespace Alxnv\Nesttab\Models\field_struct;

class FloatModel extends \Alxnv\Nesttab\Models\field_struct\BasicModel {

    
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
        echo '<input type="text" size="20" '
            . ' name="' . $rec['name'] . '" value="' . (!is_null($rec['value']) ? \yy::qs($rec['value']) : '') . '" />'
            . '<br />';
        echo '<br />';
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
        $s = '\\Alxnv\\Nesttab\\core\\db\\' . config('nesttab.db_driver') . '\\FormatHelper';
        $fh = new $s();

        //$fh = new \Alxnv\Nesttab\core\FormatHelper();
        if (false === $fh::doubleConv($value)) {
            $table_recs->setErr($index, '"' . $value . '" ' . __('is not valid') . ' ' . __('float value'));
        }
        $value = floatval($value);
        if (isset($columns[$i]['parameters']['req']) && ($value == 0.0)) {
            $table_recs->setErr($index, __('This value must not be equal to') . ' 0');
        }
        return $value;
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
            if (false === $fh::doubleConv($default)) {
                $this->setErr('default', '"' . $default . '" ' . __('is not valid') . ' ' . __('float value'));
            }
            $default = floatval($default);
        } else {
            $default = '';
            $this->setErr('default', '"" ' . __('is not valid') . ' ' . __('float value'));
        }
        if (!isset($r['m'])) {
            $r['m'] = '0';
        }
        if (!isset($r['d'])) {
            $r['d'] = '0';
        }
        $m = intval($r['m']);
        $d = intval($r['d']);
        if ($m < 0) {
            $this->setErr('m', __("This number must be positive"));
        }
        if ($d < 0) {
            $this->setErr('d', __("This number must be positive"));
        }
        if ($m > 255) {
            $this->setErr('m', __('This number must be less than') . ' 256');
        }
        if ($d >= $m && !($d == 0 && $m == 0)) {
            $this->setErr('d', __('This number must be less than') . ' ' . __('previous number'));
        }
        $params = ['m' => $m, 'd' => $d];
        return $this->saveStep2($tbl, $fld, $r, $old_values, $default, $params);

    }
    
    /**
     * function that determines if col definition is changed
     * @param array $params
     * @param array $old_params
     * @return boolean
     */
    protected function colDefChanged($params, $old_params) {
        return (($params['m'] <> $old_params['m']) || ($params['d'] <> $old_params['d']));
    }
    
}
