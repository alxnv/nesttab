<?php

/* 
 * Базовый класс работы со структурой таблицы (от него наследуются все остальные классы
 *  в этом каталоге
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class BasicModel {

    /**
     * обрабатываем считанные из yy_columns данные и подготавливаем их для
     *   дальнейшей работы
     * @param type $ov
     * @return type
     */
    public function prepare_old_values($ov) {
        $ov2 = $ov;
        $ov2['parameters'] = json_decode($ov2['parameters']);
        return $ov2;
    }
    
    /**
     * пытается сохранить(изменить)  в таблице поле, шаг 2
     * @param array $tbl
     * @param array $fld
     * @param array $r
     */
    public function saveStep2(array $tbl, array $fld, array $r, array $old_values, $err, $default) {
        global $yy, $db;
        $is_newrec = (!isset($r['id']));
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
        $fld_type_id = $fld['id'];
        $tblname= $tbl['name'];

        /*if (!$db->qdirectNoErrorMessage("lock tables yy_columns write")){
            $err .= __('The table does not exist');
            return $err;
        }*/
        if ($is_newrec) {
            $arr = $db->q("select id, name from yy_columns where table_id = $1 and name = $2",
                [$tbl['id'], $name]);
            if ($arr) {
                $err .= chr(13) . __('The field with this name is already exists');
                return $err;
            }

        }
        if (!$is_newrec) {
            $arr = $db->q("select id, name from yy_columns where table_id = $1 and name = $2 "
                    . "and id <> $3",
                [$tbl['id'], $name, $r['id']]);
            if ($arr) {
                $err .= chr(13) . __('The field with this name is already exists');
                return $err;
            }
           //$old_values = $this->prepare_old_values($old_values);
        }
        
        $params = [];
        if ($required) $params['req'] = 1;
        if ($default) $params['default'] = $default;
        
        // field specific
        // ...
        
        
        if ($err == '') {
            $tbl_id= $tbl['id'];
            $params2 = json_encode($params);
            if ($is_newrec) {
                /**
                 * Новая запись - если эта первая запись, то создаем таблицы физически,
                 *  иначе только добавляем поле к таблице
                 */
                if (($s22 = $this->createNewTableOrJustAddField($tblname, $name, $tbl['id'], $tbl['table_type'], $default, $fld_type_id)) <> '') {
                    $err .= $s22;
                    return $err;
                }
                if ($err == '') {
                    $obj = $db->qobj("select max(ordr) as mx from yy_columns where table_id = $tbl_id");
                    $n2 = ($obj ? $obj->mx : 0) + 1;
                    if (!$db->qdirectNoErrorMessage("insert into yy_columns (name,descr,"
                            . "parameters,table_id,ordr,field_type) values"
                            . "($1, $2, $3, $4, $5, $6)", [$name, $descr, $params2, $tbl_id, $n2, $fld_type_id])) {
                        $err .= __('Error modifying table structure');
                        return $err;
                    }
                    
                }
            } else {
                // its existing record
                $old_col_name = $old_values['name'];
                if ($old_col_name <> $name) {
                    if (!$db->qdirectNoErrorMessage("alter table $tblname change"
                            . " $old_col_name $name bool")) {
                        // !!! it does not result in error if the table does not exist
                        //   don't now why 
                        $err .= __('Error modifying table: ' . sprintf ("Error %s\n", $db->handle->errorInfo()[2]));
                        return $err;
                    }
                }
                if ($err == '') {
                    if (!$db->qdirectNoErrorMessage("alter table $tblname alter"
                            . " $name set default $default")) {
                        $err .= __('Error setting default value: ' . sprintf ("Error %s\n", $db->handle->errorInfo()[2]));
                        return $err;
                    }
                    
                }
                if ($err == '') {
                    if (!$db->qdirectNoErrorMessage("update yy_columns set name=$1,descr=$2,"
                            . "parameters=$3 where table_id = $4 and id=$5",
                            [$name, $descr, $params2, $tbl_id, $r['id']])) {
                        $err .= __('Error modifying table structure');
                        return $err;
                    }
                }
                    
            }
        }
        //$db->qdirect("unlock tables");
        return $err;
        
    }
    
    /**
     * Новая запись - если эта первая запись, то создаем таблицы физически,
     *  иначе добавляем поле к таблице
     * 
     * @param string $table_name - имя таблицы
     * @param string $field_name - имя поля
     * @param type $table_id - id таблицы
     */
    protected function createNewTableOrJustAddField(string $table_name, string $field_name, int $table_id, string $table_type, $default_value, int $fld_type_id) {
        global $db;
        $s = "\\Alxnv\\Nesttab\\core\\db\\" . config('nesttab.db_driver') . "\\TableHelper";
        $th = new $s();
        $def = $th->getFieldDef($fld_type_id); // вернуть определение поля типа bool для create table
        $cnt_obj = $db->qobj("select count(*) as cnt from yy_columns where table_id = $table_id");
        if ($cnt_obj->cnt == 0) {

            $arr_commands = $th->getCreateTableStrings($table_type, $table_name, $def, $field_name, $default_value);
            foreach ($arr_commands as $command) {
                $result = $db->qdirectNoErrorMessage($command);
                if (!$result) { # table already exists 
                        $message = __('Error') . ' ' . 
                                $db->errorMessage;
                        return $message;
                }
            
            }

        } else {
            if (!$db->qdirectNoErrorMessage("alter table $table_name add $field_name $def not null"
                            . " default $default_value")) {
                $message = __('Error') . ' ' . 
                        $db->errorMessage;
                return $message;
            }
        }
        return '';
    }
    
}
