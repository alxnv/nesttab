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

    /**
     * Сохранить поля для вывода на странице 'edit/' для списка записей для
     *   данной таблицы
     *  (Save $r[flds[]] в yy_ref с is_table = 1)
     * @param array $r - request data
     * @param int $tableId - id of the table
     */
    public function saveTableRefs(array $r, int $tableId) {
        global $db;
        //dd($r);
        $db->qdirectNoErrorMessage('lock tables yy_ref write');
        $db->qdirect("delete from yy_ref where is_table = 1 and src_id = $1",
                [$tableId]);
        $arr = [];
        if (isset($r['flds'])) {
            $i = 1;
            foreach ($r['flds'] as $value) {
                $arr[] = '(1, ' . $tableId  . ', ' .  $i .  ', '  . intval($value) . ')';
                $i++;
            }
        }
        if (count($arr) > 0) {
            $s = join(', ', $arr);
            $db->qdirect("insert into yy_ref (is_table, src_id, ordr, fld_id) "
                    . " values $s");
        }
        
        $db->qdirectNoErrorMessage('unlock tables');
        
    }
    
}
