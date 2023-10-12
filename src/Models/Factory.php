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
     * @param bool $withPool - if this is true, tries to not load the class
     *   but return the value from object pool if it is there
     * @return object - created object
     */
    public static function createFieldModel(int $id, string $name, bool $withPool = true) {
        global $yy;
        $s1 = '\\Alxnv\\Nesttab\\Models\\' . config('nesttab.db_driver') . '\\FieldAdapterModel';
        $s2 = '\\Alxnv\\Nesttab\\Models\\field_struct\\' 
                    . ucfirst($name) .'Model';
        $isInPool = isset($yy->fieldObjectsPool[$name]);
        if ($withPool && $isInPool) {
            return $yy->fieldObjectsPool[$name];
        }
        $fa = new $s1();
        $obj = new $s2($fa);
        $fa->init($obj);
        if ($withPool) {
            $yy->fieldObjectsPool[$name] = $obj;
        }
        return $obj;
    }
    
    /**
     * Factory method for table struct models
     * @param string $type - type 
     * @param bool $withPool - if this is true, tries to not load the class
     *   but return the value from object pool if it is there
     * @return object - created object
     */
    public static function createTableModel(string $type, bool $withPool = true) {
        global $yy;
        switch ($type) {
            case 'one' : 
            case 'list' :
            case 'ord' :
            case 'tree' :
                $s = ucfirst($type);
                $name = $type;
                $s2 = '\\Alxnv\\Nesttab\\Models\\mysql\\table\\' . $s . 'TableAdapterModel';
                $s3 = '\\Alxnv\\Nesttab\\Models\\table\\' . $s . 'TableModel';
                $isInPool = isset($yy->tableObjectsPool[$name]);
                if ($withPool && $isInPool) {
                    return $yy->tableObjectsPool[$name];
                }

                $adapter = new $s2();
                $obj = new $s3($adapter);
                $adapter->init($obj);
                if ($withPool) {
                    $yy->tableObjectsPool[$name] = $obj;
                }
                return $obj;
                break;
            default: 
                throw new \Exception('Bad table type');
        }
    }
}
