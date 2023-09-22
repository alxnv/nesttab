<?php

/**
 * Description of OneTableAdapterModel
 * 
 * mysql adapter for table/TreeTableModel
 *
 * @author Alexandr
 */
namespace Alxnv\Nesttab\Models\mysql\table;

class TreeTableAdapterModel  extends BasicTableAdapterModel {
    //put your code here
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
        // выполняем команду создания таблицы
        $command = "create table $table_name (id int NOT NULL AUTO_INCREMENT,"
                        . " parent_leaf int not null,"
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
                    "alter table $table_name add key(parent_leaf, ordr)",
                    "alter table $table_name add key(parent_leaf, name(40))",
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
}
