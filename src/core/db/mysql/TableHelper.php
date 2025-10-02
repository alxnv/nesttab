<?php
/**
 * Methods for tables
 */
namespace Alxnv\Nesttab\core\db\mysql;


class TableHelper extends \Alxnv\Nesttab\core\db\BasicTableHelper {
    /**
     * Вернуть определение поля для функции create table
     * @param int $n - номер типа поля
     * @param array $params - параметры которые сохраняются в поле 'params' yy_columns
     * @param array $saveParams - дополнительные параметры передающиеся в функцию
     *    сохранения определения поля
     * @return string - дефиниция поля для функции create table
     * @throws Exception
     */
    public function getFieldDef(int $n, array $params = [], array $saveParams = []) {
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
            case $db::DATE_TYPE :    
                return 'date';
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
            case $db::SELECT_TYPE :
                $def = $this->getIntTypeDef($saveParams['intSize']);
                if ($def === false) throw new \Exception("Bad int type");
                return $def;
            default:
                throw new \Exception("Table type is not defined");
        }
    }
    
    /**
     * returns array of possible sizes of integer db fields for mysql
     *  values starting from $from
     * @return array
     */
    public function arrayOfIntFieldSizes(int $from = 1) {
        $arr = [1, 2, 3, 4, 8];
        $arr2 = [];
        foreach ($arr as $value) {
            if ($value >= $from) {
                $arr2[] = $value;
            }
        }
        return $arr2;
    }
    
    /**
     * Returns field definition of int field this size in bytes,
     *   or false if there is no field def for this size
     * @param int $bytes - size in bytes of int field
     * @return boolean|string - field definition for this size
     */
    public function getIntTypeDef(int $bytes) {
        switch ($bytes) {
            case 1:
                return 'tinyint';
            case 2: 
                return 'smallint';
            case 3:
                return 'mediumint';
            case 4:
                return 'int';
            case 8:
                return 'bigint';
            default:
                return false;
        }
    }


}
?>