<?php

namespace app\models;

class StructAddTableModel extends \app\yy\Model {

    // create table structure, step 2, write to the tables
    // пытаемся создать таблицу указанного типа и с указанным именем
    public function Execute(array $r, &$message) {

        global $yy, $db;
        include_once $yy->Engine_Path . "/core/table_functions.php";

        $arr2 = $yy->settings2['table_types'];
        $arr_table_names_short = $yy->settings2['table_names_short'];
	if (!isset($r['tbl_type']) || !isset($r['tbl_name']) || !isset($r['tbl_descr']))  die('Required parameter is not passed');
	$tbl_idx = intval($r['tbl_type']);
	if ($tbl_idx < 0 || $tbl_idx >= count($arr2)) die('Wrong index of table');
	$tbl_name = substr($r['tbl_name'], 0, $yy->built_in_settings['max_table_name_size']);
	$tbl_descr = trim(substr($r['tbl_descr'], 0, 200));
        $err = '';
	if (($s72 = $db->valid_table_name($tbl_name)) <>'') {
                $err .= chr(13) . $s72;
	}
        if ($tbl_descr == '') {
            $err .= chr(13) . \yy::t("The table's description could not be empty");
        }
        if ($err <>'') {
                $message = $err;
                return false;
        }
	$tbl_name2 = $db->escape($tbl_name);
        $arr_commands = get_init_table_struct($arr_table_names_short[$tbl_idx], $tbl_name2);
        //var_dump($arr_commands);exit;
        foreach ($arr_commands as $command) {
                $sth = $db->qdirect_spec($command, [1050]);
                if (!$sth && $db->handle->errorCode() == '1050') { # table already exists 
                        $message = \yy::t('The table') . ' ' . \yy::qs($tbl_name) 
                                . ' ' . \yy::t('is already exists');
                        return false;
                }
            
        }

        // Записываем данные таблицы в yy_tables
        $s3 = $db->escape($tbl_descr);
        $db->qdirect("insert into yy_tables (name, descr, parent_tbl_id, table_type)"
                . " values ('$tbl_name2', '$s3', 0, '$arr_table_names_short[$tbl_idx]')");
        
        // Если имя таблицы создано по шаблону, то проставляем номер таблицы в yy_settings 
        //   как следующий номер для автонумерации
        $arr_names = $yy->settings2['table_names'];
        $def_db_prefix = config('nesttab.db_prefix');
        $s = $def_db_prefix . $arr_names[$tbl_idx];
        //var_dump($s);
        if (substr($tbl_name, 0, strlen($s)) == $s) {
            $b = false;
            if (strlen($s) == strlen($tbl_name)) {
                $n = 1;
                $b = true;
            } else {
                $s2 = substr($tbl_name, strlen($s));
                if (preg_match('/^\d+$/', $s2)) {
                    $n = intval($s2);
                    $b=true;
                }
            }
            
            if ($b) {
                $field = $arr_names[$tbl_idx] . '_counter';
                $db->qdirect("lock tables yy_settings write");
                $obj = $db->qobj("select $field as v from yy_settings");
                //var_dump($arr3);
                //exit;
                if ($n > $obj->v) {
                    $db->qdirect("update yy_settings set $field = $n");
                }
                $db->qdirect("unlock tables");
            }
        }       
        
        $message = \yy::t('The table was made');
        return true;
        
    }
}