<?php

/**
 * Description of OneTableAdapterModel
 * 
 * mysql adapter for table/OneTableModel
 *
 * @author Alexandr
 */
namespace Alxnv\Nesttab\Models\mysql\table;

class OneTableAdapterModel  extends BasicTableAdapterModel {
    //put your code here
    /**
     * execute db commands for table creation
     * @param string $table_name - table name
     * @param string &$message - error message
     * @return bool - was the creation successfull
     */
    public function createTableDbCommands(string $table_name, string &$message) {
        global $yy, $db;
        // выполняем команду создания таблицы
        $command = "create table $table_name (`id` int NOT NULL default 1, " 
                    . " PRIMARY KEY (`id`))";                
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
                    "insert into $table_name (id) values (1)",
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