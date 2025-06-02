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
     * stub
     * try to delete a table record
     * @global \Alxnv\Nesttab\Http\Controllers\type $db
     * @global \Alxnv\Nesttab\Http\Controllers\type $yy
     * @param array $tbl - table data
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     * @param int $id3 - id of the record (0 for new record)
     * @param Request $request - request data
     */
    public function deleteTableRec(array $tbl, int $id, int $id2, int $id3, object $request) {
    }
    /**
    }
     * Сохранить поля для вывода на странице 'edit/' для списка записей для
     *   данной таблицы
     *  (Save $r[flds[]] в yy_ref с is_table = 1)
     * @param array $arr - array of ids of columns
     * @param int $tableId - id of the table
     * @param int $selectedItem - selected item index in $arr
     */
    public function saveTableRefs(array $arr, int $tableId, int $selectedItem) {
        global $db;
        //dd($r);
        $db->qdirectNoErrorMessage('lock tables yy_ref write');
        $db->qdirect("delete from yy_ref where is_table = 1 and src_id = $1",
                [$tableId]);
        $arr2 = [];
        $i = 1;
        foreach ($arr as $value) {
            $parm = (object)($i - 1 == $selectedItem ? ['d' => 1] : []);
            $parm2 = json_encode($parm);
            $arr2[] = '(1, ' . $tableId  . ', ' .  $i .  ', '  . intval($value) 
                    . ", '" . $parm2 . "')";
            $i++;
        }
        if (count($arr2) > 0) {
            $s = join(', ', $arr2);
            //dd($s);
            $db->qdirect("insert into yy_ref (is_table, src_id, ordr, fld_id, parameters) "
                    . " values " . $s);
        }
        
        $db->qdirectNoErrorMessage('unlock tables');
        
    }
    
}
