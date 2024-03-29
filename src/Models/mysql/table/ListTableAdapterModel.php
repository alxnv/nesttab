<?php

/**
 * Description of OneTableAdapterModel
 * 
 * mysql adapter for table/ListTableModel
 *
 * @author Alexandr
 */
namespace Alxnv\Nesttab\Models\mysql\table;

class ListTableAdapterModel  extends BasicTableAdapterModel {

    /**
     * execute db commands for table creation
     * @param string $table_name - table name
     * @param string &$message - error message
     * @param string $idFieldDef - definition(type) of 'id' field
     * @param int $parentTableId - id of parent table, or
     *   0, if there is no parent table (top level table creation)
     * @param array|null $parentTbl - record of parent table from yy_tables,
     *    or null if there is no parent table (top level table creation)
     * @return bool - was the creation successfull
     */
    public function createTableDbCommands(string $table_name, string &$message,
            string $idFieldDef, int $parentTableId, $parentTbl) {
        global $yy, $db;
        //var_dump($arr_commands);exit;

        // выполняем команду создания таблицы
        $command = "create table $table_name (id " . $idFieldDef . " unsigned NOT NULL AUTO_INCREMENT,"
                        . " ordr int not null,"
                        . " primary key (id))";                
        $sth = $db->qdirectNoErrorMessage($command);
        if (!$sth) {
            if ($db->errorCode == '42S01') { # table already exists 
                $message = __('The table') . ' ' . \yy::qs($table_name) 
                           . ' ' . __('is already exists');
            } else {
                $message = $db->errorMessage;
            }
            return false;
        }


        // выполняем дополнительные команды
        // todo: если есть parent table, то вместо этого
        //   делать индекс по parent_id, ordr
        $arr_commands = [
                    "alter table $table_name add key(ordr)",
                    ];
        foreach ($arr_commands as $command) {
                $sth = $db->qdirectNoErrorMessage($command);
                if (!$sth) { # table already exists 
                    $message = $db->errorMessage;
                    return false;
                }
            
        }
        return true;
        
    }
    /**
     * Add parent table record ids values to array for saving and to where clause
     * @param array $arrValues
     * @param array $parentTableRec
     * @param string $where - return where clause here
     * @return array
     */
    public function addSaveValues(array $arrValues, array $parentTableRec, &$where) {
        $where = '';
        return $arrValues;
    }
    
    
    /**
     * Try to insert value into database of list type
     * @param string $tbl_name - name of the table
     * @param array $arrValues - values to insert into db
     * @param array $parentTableRec - parent table record (or empty array if
     *    its a top level table)
     * @return string - '' if there is no error, or error message otherwise
     */
    public function insert(string $tbl_name, array $arrValues, array $parentTableRec, &$id) {

        global $db;
        $where = ''; // todo: устанвить его в другое значение для таблиц с parent_id
        $arr2 = $this->addSaveValues($arrValues, $parentTableRec, $where);
        
        $error = '';
        
        if (!$db->qdirectNoErrorMessage("lock tables $tbl_name write")){
            $error = __('The table does not exist');
            $db->qdirect("unlock tables");
        }
        if ($error == '') {
            $obj = $db->qobj("select max(ordr) as mx from $tbl_name $where");
            $n2 = ($obj ? $obj->mx : 0) + 1;
            $arr2['ordr'] = $n2;
            if (($error = $db->insert($tbl_name, $arr2)) <> '') {
                $db->qdirect("unlock tables");
            }
        }
        if ($error == '') {
            $id = $db->handle->lastInsertId();
            $db->qdirect("unlock tables");
            return '';
        } else {
            return $error;
        }
    }
}
