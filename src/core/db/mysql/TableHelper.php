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
    function getFieldDef(int $n, array $params = []) {
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
            case $db::DATETIME_TYPE :    
                return 'datetime';
            case $db::INT_TYPE :
                return 'int';
            case $db::FILE_TYPE :
                return 'text';
            case $db::IMAGE_TYPE :
                return 'text';
            case $db::FLOAT_TYPE :
                $m = $params['m'];
                $d = $params['d'];
                if ($m == 0) {
                    return 'double';
                } else {
                    return 'double(' . $m . ',' . $d . ')';
                }
            default:
                throw new \Exception("Table type is not defined");
        }
    }


}
?>