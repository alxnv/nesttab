<?php
/**
 * functions for tables
 */


/**
 * 
 * @param string $table_type тип таблицы ('O','L','C','V')
 * @param string $table_name - имя создаваемой таблицы
 * @param array $additional_commands - массив с возвращаемыми дополнительными командами
 * @return array массив строк для создания пустой таблицы заданного типа (с возможными
 *    дополнительными коммандами
 */
function get_init_table_struct(string $table_type, string $table_name):array {
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
            throw new Exception("Table type is not defined");
    }
}
?>