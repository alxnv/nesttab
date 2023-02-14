<?php
/**
 * Methods for tables
 */
namespace Alxnv\Nesttab\core\db\mysql;


class TableHelper extends \Alxnv\Nesttab\core\db\BasicTableHelper {
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
            case $db::TEXT_TYPE :
                return 'mediumtext';
            case $db::HTML_TYPE :
                return 'mediumtext';
            case $db::STR_TYPE :
                return 'varchar(255)';
            case $db::INT_TYPE :
                return 'int';
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
    function getCreateTableStrings(string $table_type, string $table_name):array {
        global $db;
        switch ($table_type) {
            case 'O': // one record table
                return ["create table $table_name (`id` int NOT NULL default 1, " 
                    . " PRIMARY KEY (`id`))",
                    "insert into $table_name (id) values (1)"];
            case 'L': // list table
                return ["create table $table_name (id int NOT NULL AUTO_INCREMENT,"
                        . " ordr int not null,"
                        . " name varchar(255) not null,"
                        . " primary key (id))",
                    "alter table $table_name add key(ordr)",
                    "alter table $table_name add key(name(40))",
                    ];
            case 'C': // tree table
                return ["create table $table_name (id int NOT NULL AUTO_INCREMENT,"
                        . " parent_leaf int not null,"
                        . " ordr int not null,"
                        . " name varchar(255) not null,"
                        . " primary key (id))",
                    "alter table $table_name add key(parent_leaf, ordr)",
                    "alter table $table_name add key(parent_leaf, name(40))",
                    ];
            case 'V': // ord table
                return ["create table $table_name (`id` int NOT NULL AUTO_INCREMENT, " 
                    . " PRIMARY KEY (`id`))"
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