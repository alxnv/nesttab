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
        if (!$db->qdirect_no_error_message("alter table $tblname drop column $name")) {
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
        $is_newrec = (!isset($r['id']));
        $err = '';
        if (!isset($r['name'])) $err .= chr(13) . __('Name of field not found');
        if (!isset($r['descr'])) $err .= chr(13) . __('Description of field not found');
        if ($err <> '') return $err;
        $name = mb_substr($r['name'], 0, $yy->db_settings['max_custom_field_size']);
        $descr = mb_substr($r['descr'], 0, 250);
        if (trim($descr) == '') $err .= chr(13) . __('Description must not be empty');
        if (($s = $db->valid_field_name($name)) <> '') {
            $err .= $s;
        }
        if ($err <> '') return $err;
        $required = (isset($r['req']) ? 1 : 0);
        $default = (isset($r['default']) ? 1 : 0);
        $fld_type_id = $fld['id'];
        $tblname= $tbl['name'];

        if (!$db->qdirect_no_error_message("lock tables yy_columns write, $tblname write")){
            $err .= __('Table does not exist');
        }
        if ($is_newrec) {
            $arr = $db->q("select id, name from yy_columns where table_id = $1 and name = $2",
                [$tbl['id'], $name]);
            if ($arr) $err .= chr(13) . __('The field with this name is already exists');
        }
        if (!$is_newrec) {
            $arr = $db->q("select id, name from yy_columns where table_id = $1 and name = $2 "
                    . "and id <> $3",
                [$tbl['id'], $name, $r['id']]);
            if ($arr) $err .= chr(13) . __('The field with this name is already exists');
            //$old_values = $this->prepare_old_values($old_values);
        }
        
        $params = [];
        if ($required) $params['req'] = 1;
        if ($default) $params['default'] = 1;
        
        // field specific
        // ...
        
        
        if ($err == '') {
            $tbl_id= $tbl['id'];
            $params2 = json_encode($params);
            if ($is_newrec) {
                if (!$db->qdirect_no_error_message("alter table $tblname add $name bool not null"
                        . " default $default")) {
                    $err .= __('Error modifying table');
                }
                if ($err == '') {
                    $obj = $db->qobj("select max(ordr) as mx from yy_columns where table_id = $tbl_id");
                    $n2 = ($obj ? $obj->mx : 0) + 1;
                    if (!$db->qdirect_no_error_message("insert into yy_columns (name,descr,"
                            . "parameters,table_id,ordr,field_type) values"
                            . "($1, $2, $3, $4, $5, $6)", [$name, $descr, $params2, $tbl_id, $n2, $fld_type_id])) {
                        $err .= __('Error modifying table structure');
                    }
                    
                }
            } else {
                // its existing record
                $old_col_name = $old_values['name'];
                if ($old_col_name <> $name) {
                    if (!$db->qdirect_no_error_message("alter table $tblname change"
                            . " $old_col_name $name bool")) {
                        $err .= __('Error modifying table: ' . sprintf ("Error %s\n", $db->handle->errorInfo()[2]));
                    }
                }
                if ($err == '') {
                    if (!$db->qdirect_no_error_message("alter table $tblname alter"
                            . " $name set default $default")) {
                        $err .= __('Error setting default value: ' . sprintf ("Error %s\n", $db->handle->errorInfo()[2]));
                    }
                    
                }
                if ($err == '') {
                    if (!$db->qdirect_no_error_message("update yy_columns set name=$1,descr=$2,"
                            . "parameters=$3 where table_id = $4 and id=$5",
                            [$name, $descr, $params2, $tbl_id, $r['id']])) {
                        $err .= __('Error modifying table structure');
                    }
                }
                    
            }
        }
        $db->qdirect("unlock tables");
        return $err;
        
    }
    
}
