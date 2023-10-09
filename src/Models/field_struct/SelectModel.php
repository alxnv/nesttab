<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа select - поля выбора записи из другой таблицы
 */

namespace Alxnv\Nesttab\Models\field_struct;

use Illuminate\Support\Facades\Session;

class SelectModel extends \Alxnv\Nesttab\Models\field_struct\BasicModel {
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
        if (isset($columns[$i]['parameters']['req']) && (is_null($value) || (intval($value) == 0))) {
            $table_recs->setErr($index, __('Select an item'));
        }
        return $value;
    }
    /**
     * пытается сохранить(изменить)  в таблице поле
     * @param array $tbl - данные о текущей таблице которой принадлежит поле
     * @param array $fld - данные о типе текущего поля из yy_col_types
     * @param array $r - Request
     * @param array $old_values - []
     */
    public function save(array $tbl, array $fld, array &$r, array $old_values) {
        global $yy, $db;
        
        if (!isset($r['table_id']) || (intval($r['table_id']) == 0)) {
            $this->setErr('table_id', __('The table is not choosen'));
        } else {
            if (!isset($r['flds']) || !is_array($r['flds']) || (count($r['flds']) == 0)) {
                $this->setErr('', __('Choose at least one field'));
            }
        }
        
        if ($this->hasErr()) return;
        $default = 0;
        if (!$this->hasErr()) {
            // сохраняем выбранные поля для поля select
            $link_table_id = intval($r['table_id']);
        };
        $linkedTable = \Alxnv\Nesttab\Models\TablesModel::getOne($link_table_id);
        $intSize = $linkedTable['id_bytes'];
        
        $params = [];
        $saveParams = ['ref_table' => $link_table_id, 'intSize' => $intSize]; // id of the table to link to
        $this->saveStep2($tbl, $fld, $r, $old_values, $default, $params, $saveParams);
        if (!$this->hasErr()) {
            // сохраняем выбранные поля для поля select
            $this->adapter->saveSelectValues($tbl, $fld, $r, $old_values, $link_table_id);
        };
        return;
    }
    
    /**
     * удаление поля из структуры таблицы
     *   !!! контроллер вызывается через ajax
     * @param array $column - запись из yy_columns (структура полей в таблицах)
     * @param array $fld - запись из таблицы определений типов полей
     * @param array $tbl - запись из таблицы yy_tables (данные таблиц)
     * @param array $r - входные параметры скрипта
     * @return string - '', либо строка сообщения об ошибке
     */
    public function delete(array $column, array $fld, array $tbl, array $r) {
        global $yy, $db;
        
        $err = parent::delete($column, $fld, $tbl, $r);
        
        if ($err == '') {
            $db->qdirect("delete from yy_select where src_fld_id = $1", [$column['id']]);
        }
        
        return $err;
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
        global $yy;
        //echo $rec['id'];
        $value = $rec['value'];
        if (is_null($value)) $value = 0;
        //echo $rec['parameters'];
        if (isset($selectsInitialValues[$rec['id']])) {
            $name = $selectsInitialValues[$rec['id']];
        } else {
            $name = $yy->settings2['not_selected'];
        }
        //var_dump($selectsInitialValues);
        echo '<br />';
        echo \yy::qs($rec['descr']);
        echo '<br />';
        echo '<br />';
        echo '<select class="select2" id="f_' . $rec['name'] . '"'
                . ' name="' . $rec['name'] . '" >';
        echo '<option selected value="' . $value . '">' . \yy::qs($name);
        echo '</select>';
        echo '<br />';
        echo '<br />';
        
        $js = "$('#f_" . $rec['name'] . "').select2({ dropdownCssClass: 'dropdown-select2',"
                . "    placeholder: 'Search for source',
    //minimumInputLength: 1,
    language: '" . config('app.locale') . "',
    ajax: { 
        url: '" . asset('/' . config('nesttab.nurl') . '/ajax_select_get')
                . "/" . $rec['id'] . "',
        dataType: 'json',
        type: 'post',
        headers: {
            'X-CSRF-TOKEN': '" . Session::token() . "',
        },
        quietMillis: 250,
        data: function (term, page) {
            return {
                q: term.term, // search term
                page: term.page || 1, //??? does it work?
            };
        },
        processResults: function (data) {
            return {
                results: data.list,
                pagination: {
                    'more' : data.more,
                }
            };
        }
        },
 });";
        \blocks::add('jquery', $js);
    }

    /**
     * Получить все данные об отображаемых полях для поля типа select
     * @global type $db
     * @global type $yy
     * @param type $fld_id - id поля типа select
     * @return type
     */
    public function getSelectData($fld_id) {
        global $db, $yy;
        $arr = $db->qlistArr("select * from yy_select where src_fld_id = $1 "
                . " order by ordr", [$fld_id]);
        $ar2 = [];
        foreach ($arr as $rec) {
            $ar2[] = $rec['fld_id'];
        }
        return $ar2;
    }
    
    
}
