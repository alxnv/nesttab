<?php
/**
 * Methods for tables
 */
namespace Alxnv\Nesttab\core\db\mysql;


class TableHelper {
    /**
     * Вернуть определение поля для функции create table
     * @param int $n - номер типа поля
     * @return string - дефиниция поля для функции create table
     * @throws Exception
     */
    function getFieldDef(int $n) {
        global $db;
        switch ($n) {
            case $db::BOOL_TYPE :
                return 'bool';
            default:
                throw new \Exception("Table type is not defined");
        }
    }

    /**
     * Создать начальную структуру таблицы верхнего уровня
     * @param string $table_type тип таблицы ('O','L','C','V')
     * @param string $table_name - имя создаваемой таблицы
     * @param array $additional_commands - массив с возвращаемыми дополнительными командами
     * @param mixed $default_value - значение для поля по умолчанию
     * @return array массив строк для создания пустой таблицы заданного типа (с возможными
     *    дополнительными коммандами
     */
    function getCreateTableStrings(string $table_type, string $table_name, string $field_def, string $field_name, $default_value):array {
        global $db;
        $df = $db->escape($default_value);
        switch ($table_type) {
            case 'O':
                return ["create table $table_name (`id` int NOT NULL default 1, " 
                    . $field_name . ' ' . $field_def . ' not null'
                    . " default " . $df
                    . " ,PRIMARY KEY (`id`))",
                    "insert into $table_name ($field_name) values ($df)"];
            case 'L':
                return ["create table $table_name (id int NOT NULL AUTO_INCREMENT,"
                        . " ordr int not null,"
                        . $field_name . ' ' . $field_def . ' not null'
                        . " default " . $df
                        . " primary key (id))",
                    "alter table $table_name add unique key(ordr)"];
            case 'C':
                return ["create table $table_name (id int NOT NULL AUTO_INCREMENT,"
                        . " parent_id int not null,"
                        . " ordr int not null,"
                        . $field_name . ' ' . $field_def . ' not null'
                        . " default " . $df
                        . " primary key (id))",
                    "alter table $table_name add unique key(parent_id, ordr)"];
            case 'V':
                return ["create table $table_name ("
                        . $field_name . ' ' . $field_def . ' not null'
                        . " default " . $df
                        . ")"
                       ];
            default:
                throw new \Exception("Table type is not defined");
        }
    }

    /**
     * Создать начальную структуру вложенной таблицы 
     * @param string $table_type тип таблицы ('O','L','C','V')
     * @param string $table_name - имя создаваемой таблицы
     * @param array $additional_commands - массив с возвращаемыми дополнительными командами
     * @return array массив строк для создания пустой таблицы заданного типа (с возможными
     *    дополнительными коммандами
     */
    function get_init_secondary_table_struct(string $table_type, string $table_name):array {
        switch ($table_type) {
            case 'O':
                return ["create table $table_name (id int NOT NULL AUTO_INCREMENT,"
                        . " primary key (id))"];
            case 'L':
                return ["create table $table_name (id int NOT NULL AUTO_INCREMENT,"
                        . " ordr int not null,"
                        . " primary key (id))",
                    "alter table $table_name add unique key(ordr)"];
            case 'C':
                return ["create table $table_name (id int NOT NULL AUTO_INCREMENT,"
                        . " parent_id int not null,"
                        . " ordr int not null,"
                        . " primary key (id))",
                    "alter table $table_name add unique key(parent_id, ordr)"];
            case 'V':
                return ["create table $table_name (id int NOT NULL AUTO_INCREMENT,"
                        . " ordr int not null,"
                        . " primary key (id))",
                    "alter table $table_name add unique key(ordr)"];
            default:
                throw new \Exception("Table type is not defined");
        }
    }

}
?>