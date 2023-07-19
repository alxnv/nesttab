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
        $s2 = '\\Alxnv\\Nesttab\\Models\\field_struct\\' . config('nesttab.db_driver') . '\\'
                    . ucfirst($name) .'Model';
        return new $s2();
    }
    
}
