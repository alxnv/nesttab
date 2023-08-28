<?php

/**
	Models\field_struct\<object> Adapter model for mysql
	
*/
namespace Alxnv\Nesttab\Models\mysql;

class FieldAdapterModel {
    protected $fs;
    /**
     * 
     * @param type $fs - Models\field_struct\<model>
     */
    public function init($fs) {
        $this->fs = $fs;
    }
}