<?php
/**
 * Factory for field struct models and others (if any)
 */

namespace Alxnv\Nesttab\Models;

/**
 * Description of Factory
 *
 * @author Alexandr
 */
class Factory {
    
    /**
     * Factory method for field struct models
     * @param int $id -  id of the field
     * @param string $name - name of the field (from db)
     * @return object - created object
     */
    public static function createFieldModel(int $id, string $name) {
        $s1 = '\\Alxnv\\Nesttab\\Models\\' . config('nesttab.db_driver') . '\\FieldAdapterModel';
        $fa = new $s1();
        $s2 = '\\Alxnv\\Nesttab\\Models\\field_struct\\' 
                    . ucfirst($name) .'Model';
        $obj = new $s2($fa);
        $fa->init($obj);
        return $obj;
    }
    
    public static function createTableModel(string $type) {
        switch ($type) {
            case 'one' : 
            case 'list' :
            case 'ord' :
            case 'tree' :
                $s = ucfirst($type);
                $s2 = '\\Alxnv\\Nesttab\\Models\\mysql\\table\\' . $s . 'TableAdapterModel';
                $adapter = new $s2();
                $s2 = '\\Alxnv\\Nesttab\\Models\\table\\' . $s . 'TableModel';
                $obj = new $s2($adapter);
                $adapter->init($obj);
                return $obj;
                break;
            default: 
                throw new \Exception('Bad table type');
        }
    }
}
