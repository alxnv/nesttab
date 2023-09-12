<?php

/**
 * Description of BasecTableAdapterModel
 *
 * Basis class for mysql adapters for models classes (Models/table/*.*)
 * 
 * @author Alexandr
 */

namespace Alxnv\Nesttab\Models\mysql\table;

class BasicTableAdapterModel {
    
    // main table object for which the adapter was applied
    protected $tableObj;
    
    
    public function init(object $tableObj) {
        $this->tableObj = $tableObj;
    }
}
