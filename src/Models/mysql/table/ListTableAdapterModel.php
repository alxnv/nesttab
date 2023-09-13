<?php

/**
 * Description of OneTableAdapterModel
 * 
 * mysql adapter for table/ListTableModel
 *
 * @author Alexandr
 */
namespace Alxnv\Nesttab\Models\mysql\table;

class ListTableAdapterModel  extends BasicTableAdapterModel {

    /**
     * Add parent table record ids values to array for saving and to where clause
     * @param array $arrValues
     * @param array $parentTableRec
     * @param string $where - return where clause here
     * @return array
     */
    public function addSaveValues(array $arrValues, array $parentTableRec, &$where) {
        $where = '';
        return $arrValues;
    }
    
    
    /**
     * Try to insert value into database of list type
     * @param string $tbl_name - name of the table
     * @param array $arrValues - values to insert into db
     * @param array $parentTableRec - parent table record (or empty array if
     *    its a top level table)
     * @return boolean
     */
    public function insert(string $tbl_name, array $arrValues, array $parentTableRec, &$id) {

        global $db;
        $where = '';
        $arr2 = $this->addSaveValues($arrValues, $parentTableRec, $where);
        
        
        if (!$db->qdirectNoErrorMessage("lock tables $tbl_name write")){
            $this->tableObj->setErr('', __('The table does not exist'));
            $db->qdirect("unlock tables");
            return false;
        }
        $obj = $db->qobj("select max(ordr) as mx from $tbl_name $where");
        $n2 = ($obj ? $obj->mx : 0) + 1;
        $arr2['ordr'] = $n2;
        if (!$db->insert($tbl_name, $arr2)) {
            $this->tableObj->setErr('', __('Error modifying table'));
            $db->qdirect("unlock tables");
            return false;
        }
        $id = $db->handle->lastInsertId();
        $db->qdirect("unlock tables");
        return true;
    }
}
