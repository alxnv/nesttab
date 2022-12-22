<?php

/* 
 * Класс работы со структурой таблицы
 * полями типа boolean
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class BoolModel extends \Alxnv\Nesttab\Models\field_struct\mysql\BasicModel {

    
    //public function data_type() {
    //    return 'tinyint(4)';
    //}

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

        $err = '';
        $tblname= $tbl['name'];
        $name = $column['name'];
        if (!$db->qdirectNoErrorMessage("alter table $tblname drop column $name")) {
            $err .= sprintf ("Error %s\n", mysqli_error($db->handle));
        }
        if ($err == '') {
            $err .= \Alxnv\Nesttab\Models\StructColumnsModel::delete($column['id']);
        }
        return $err;
    }

    /**
     * пытается сохранить(изменить)  в таблице поле
     * @param array $tbl
     * @param array $fld
     * @param array $r
     */
    public function save(array $tbl, array $fld, array $r, array $old_values) {
        global $yy, $db;
        $err = '';
        $default = (isset($r['default']) ? 1 : 0);
        return $this->saveStep2($tbl, $fld, $r, $old_values, $err, $default);

    }
    
    
}
