<?php

/* 
 * Базовый класс работы со структурой таблицы (от него наследуются все остальные классы
 *  в этом каталоге
 */

namespace Alxnv\Nesttab\Models\field_struct\mysql;

class BasicModel {
    
    /**
     * объект с массивом ошибок с индексом по наименованиям полей формы
     *  в которых ошибочные данные
     * @var type array
     */
    public $err; 

    public function __construct() {
        $this->err = new \Alxnv\Nesttab\Models\ErrorModel();
    }
   
    /**
     * Заглушка, вызываемая для вывода поля таблицы для редактирования
     * @param array $rec - массив с данными поля
     * @param array $errors - массив ошибок
     */
    public function editField(array $rec, array $errors) {
        echo \yy::qs($rec['descr']);
        echo '<br />';
        echo (is_null($rec['value']) ? 'Not defined' : \yy::qs($rec['value']));
        echo '<br /><br />';
    }
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
     * Устанавливаем ошибку для указанного поля
     * @param string $field - поле, для которого устанавливается ошибка
     * @param string $errorString - сообщение об ошибке
     */
    public function setErr(string $field, string $errorString) {
        $this->err->setErr($field, $errorString);
    }
    
    /**
     * Проверяем, есть ли ошибка в данных
     * @return boolean
     */
    public function hasErr() {
        return $this->err->hasErr();
    }

    /**
     * пытается сохранить(изменить)  в таблице поле, шаг 2
     * @param array $tbl
     * @param array $fld
     * @param array $r
     * @param array $params - дополнительные параметры поля, если не пустые
     */
    public function saveStep2(array $tbl, array $fld, array $r, array $old_values, $default, array $params = []) {
        global $yy, $db;
                    //\yy::gotoErrorPage('Unable to lock process555555');

        $is_newrec = (!isset($r['id']));
        $name = (isset($r['name']) ? $r['name'] : '');
        $descr = (isset($r['descr']) ? $r['descr'] : '');
        //if (!isset($r['descr'])) $this->setErr('descr', __('Description of field not found'));
        $name = mb_substr($name, 0, $yy->db_settings['max_custom_field_size']);
        $descr = mb_substr($descr, 0, 250);
        if (trim($descr) == '') $this->setErr('descr', __('Description must not be empty'));
        if (($s = $db->valid_field_name($name)) <> '') {
            $this->setErr('name', $s);
        }
        $required = (isset($r['req']) ? 1 : 0);
        $fld_type_id = $fld['id'];
        $tblname= $tbl['name'];
        $tblname_2 = $db->nameEscape($tblname);
        $s = "\\Alxnv\\Nesttab\\core\\db\\" . config('nesttab.db_driver') . "\\TableHelper";
        $th = new $s();
        if (($s = $th->testIfReservedField($tbl['table_type'], $name)) <> '') {
            $this->setErr('name', $s);
        }
        $name_2 = $db->nameEscape($name);
        if ($this->hasErr()) return;
        $definition = $th->getFieldDef($fld_type_id, $params);
        /*if (!$db->qdirectNoErrorMessage("lock tables yy_columns write")){
            $err .= __('The table does not exist');
            return $err;
        }*/
        if ($is_newrec) {
            $arr = $db->q("select id, name from yy_columns where table_id = $1 and name = $2",
                [$tbl['id'], $name]);
            if ($arr) {
                $this->setErr('name', __('The field with this name is already exists'));
                return;
            }

        }
        if (!$is_newrec) {
            $arr = $db->q("select id, name from yy_columns where table_id = $1 and name = $2 "
                    . "and id <> $3",
                [$tbl['id'], $name, $r['id']]);
            if ($arr) {
                $this->setErr('name', __('The field with this name is already exists'));
                return;
            }
           //$old_values = $this->prepare_old_values($old_values);
        }
        
        //$params = [];
        if ($required) $params['req'] = 1;
        if ($default) $params['default'] = $default;
        
        // field specific
        // ...
        
        
        if (!$this->hasErr()) {
            $tbl_id= $tbl['id'];
            $params2 = json_encode($params);
            if ($is_newrec) {
                if (!$this->hasErr()) {
                    if (!$db->qdirectNoErrorMessage("lock tables yy_columns write")){
                        $this->setErr('', __('The table does not exist'));
                        $db->qdirect("unlock tables");
                        return;
                    }
                    $obj = $db->qobj("select max(ordr) as mx from yy_columns where table_id = $tbl_id");
                    $n2 = ($obj ? $obj->mx : 0) + 1;
                    if (!$db->qdirectNoErrorMessage("insert into yy_columns (name,descr,"
                            . "parameters,table_id,ordr,field_type) values"
                            . "($1, $2, $3, $4, $5, $6)", [$name, $descr, $params2, $tbl_id, $n2, $fld_type_id])) {
                        $this->setErr('', __('Error modifying table structure'));
                        $db->qdirect("unlock tables");
                        return;
                    }
                    $db->qdirect("unlock tables");
                    
                }
                if (!$this->hasErr() && !$this->addField($tblname_2, $name_2, $tbl['id'], 
                        $tbl['table_type'], $default, $fld_type_id, $definition)) {
                    return;
                }
            } else {
                // its existing record
                $old_col_name = $old_values['name'];
                $old_col_name_2 = $db->nameEscape($old_col_name);
                $old_params = $this->prepareOldParams($old_values);
                if ($old_col_name <> $name || $this->colDefChanged($params, $old_params)) {
                    if (!$db->qdirectNoErrorMessage("alter table $tblname_2 change"
                            . " $old_col_name_2 $name_2 $definition not null")) {
                        // !!! it does not result in error if the table does not exist
                        //   don't now why 
                        $this->setErr('', __('Error modifying table: ' . 
                                sprintf ("Error %s\n", $db->handle->errorInfo()[2])));
                        return;
                    }
                }
                if (!$this->hasErr()) {
                    $df5 = $db->escape($default);
                    if (!$db->qdirectNoErrorMessage("alter table $tblname_2 alter"
                            . " $name_2 set default $df5")) {
                        $this->setErr('default', __('Error setting default value: ' . 
                                sprintf ("Error %s\n", $db->handle->errorInfo()[2])));
                        return;
                    }
                    
                }
                if (!$this->hasErr()) {
                    if (!$db->qdirectNoErrorMessage("update yy_columns set name=$1,descr=$2,"
                            . "parameters=$3 where table_id = $4 and id=$5",
                            [$name, $descr, $params2, $tbl_id, $r['id']])) {
                        $this->setErr('', __('Error modifying table structure'));
                        return;
                    }
                }
                    
            }
        }
        //$db->qdirect("unlock tables");
        //return $err;
        
    }
    
    /**
     * stub for function that determines if col definition is changed
     * @param array $params
     * @param array $old_params
     * @return boolean
     */
    protected function colDefChanged($params, $old_params) {
        return false;
    }

    /**
     * 
     * @param array $old_values
     * @return array - 'parameters' field of $old_values json decoded
     */
    protected function prepareOldParams(array $old_values) {
        return (array)json_decode($old_values['parameters']);
    }
    
    /**
     * Новая запись - если эта первая запись, то создаем таблицы физически,
     *  иначе добавляем поле к таблице
     * 
     * @param string $table_name - имя таблицы
     * @param string $field_name - имя поля
     * @param type $table_id - id таблицы
     */
    protected function addField(string $table_name, string $field_name, int $table_id, string $table_type, $default_value, int $fld_type_id, string $def) {
        global $db;
        $df2 = $db->escape($default_value);
        //$def = $th->getFieldDef($fld_type_id); // вернуть определение поля типа для create table
        if (!$db->qdirectNoErrorMessage("alter table $table_name add $field_name $def not null"
                        . " default $df2")) {
            $message = __('Error') . ' ' . 
                    $db->errorMessage;
            $this->setErr('', $message);
            return false;
        }
        return true;
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

        $err = '';
        $tblname= $tbl['name'];
        $name = $column['name'];
        $yy->whithout_layout = 1;
        $tblname_2 = $db->nameEscape($tblname);
        $name_2 = $db->nameEscape($name);
        if ($err == '') {
            $err .= \Alxnv\Nesttab\Models\ColumnsModel::delete($column['id']);
        }
        if ($err == '') {
            $db->qdirectSpec("alter table $tblname_2 drop column $name_2", [42000]);
        }
        return $err;
    }

    
}
