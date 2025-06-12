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
     * @param string $idFieldDef - definition(type) of 'id' field
     * @param int $parentTableId - id of parent table, or
     *   0, if there is no parent table (top level table creation)
     * @param array|null $parentTbl - record of parent table from yy_tables,
     *    or null if there is no parent table (top level table creation)
     * @param array $options : key 'toAddRec' - значит добавлять запись после создания таблицы
     *   (только для таблицы типа 'one')
     * @return bool - was the creation successfull
     */
    public function createTableDbCommands(string $table_name, string &$message,
            string $idFieldDef, int $parentTableId, $parentTbl, array $options) {
        global $yy, $db;
        // выполняем команду создания таблицы
        if ($parentTableId == 0) {
            $command = "create table $table_name (`id` " . $idFieldDef . " unsigned NOT NULL default 1, " 
                    . " PRIMARY KEY (`id`))";                
        } else {
            $k = $parentTbl['id_bytes'];
            $s = '\\Alxnv\\Nesttab\\core\\db\\' . config('nesttab.db_driver') . '\\TableHelper';
            $th = new $s();
            if (($parentFieldDef = $th->getIntTypeDef($k)) === false) {
                $message = 'No int types of this size';
                return false;
            }
            $command = "create table $table_name (`id` " . $idFieldDef . " unsigned NOT NULL default 1,"
                    . " parent_id "  . $parentFieldDef . " unsigned not null,"
                    . " PRIMARY KEY (`id`))";                
            
        }
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

        if ($parentTableId <> 0) {
           // добавляем индекс по parent_id 
            $arr_commands = [
                        "alter table $table_name add index parent_id(parent_id)",
                        ];
            foreach ($arr_commands as $command) {
                    $sth = $db->qdirectNoErrorMessage($command);
                    if (!$sth) { # table already exists 
                        $message = $db->errorMessage;
                        return false;
                    }

            }
        }

        // выполняем дополнительные команды
        if (isset($options['toAddRec'])) {
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
        }
        return true;
        
    }
}
