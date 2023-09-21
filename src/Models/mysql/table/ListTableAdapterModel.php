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
     * @return bool - was the creation successfull
     */
    public function createTableDbCommands(string $table_name, string &$message) {
        global $yy, $db;
        //var_dump($arr_commands);exit;

        // выполняем команду создания таблицы
        $command = "create table $table_name (id int NOT NULL AUTO_INCREMENT,"
                        . " ordr int not null,"
                        . " name varchar(255) not null default '',"
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
        $arr_commands = [
                    "alter table $table_name add key(ordr)",
                    "alter table $table_name add key(name(40))",
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
     * @return boolean
     */
    public function insert(string $tbl_name, array $arrValues, array $parentTableRec, &$id) {

        global $db;
        $where = '';
        $arr2 = $this->addSaveValues($arrValues, $parentTableRec, $where);
        
        
        if (!$db->qdirectNoErrorMessage("lock tables $tbl_name write")){
            $this->tableObj->setErr('', __('The table does not exist'));
            $db->qdirect("unlock tables");
            return false;
        }
        $obj = $db->qobj("select max(ordr) as mx from $tbl_name $where");
        $n2 = ($obj ? $obj->mx : 0) + 1;
        $arr2['ordr'] = $n2;
        if (!$db->insert($tbl_name, $arr2)) {
            $this->tableObj->setErr('', __('Error modifying table'));
            $db->qdirect("unlock tables");
            return false;
        }
        $id = $db->handle->lastInsertId();
        $db->qdirect("unlock tables");
        return true;
    }
}
