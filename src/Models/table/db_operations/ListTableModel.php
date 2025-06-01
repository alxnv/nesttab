<?php

/**
 * Model for list table type (list) - ordered list of records
 *  !!! db operations only
 */

namespace Alxnv\Nesttab\Models\table\db_operations;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class ListTableModel extends BasicTableModel {
    /**
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
        global $db, $yy;
        $r = $request->all();
        // get columns data for all file columns of the table
        if ($id3 == 0) {
            \yy::gotoErrorPage('New record can not be deleted');
        } else {
            $rec = \Alxnv\Nesttab\Models\ArbitraryTableModel::getOne($tbl['name'], $id3);
        }
        // получить список колонок таблицы типа "файл" и "изображение"
        $fileColumns = \Alxnv\Nesttab\Models\ColumnsModel::getTableFileColumns($tbl['id']);
        foreach ($fileColumns as $fileColumn) {
            $fldName = $fileColumn['name'];
            // если есть поле с таким именем в записи
            if (array_key_exists($fldName, $rec)) {
                $gt = gettype($rec[$fldName]);
                if (('string' === $gt) && 
                        ($rec[$fldName] <> '')) {
                    $obj = \Alxnv\Nesttab\Models\Factory::createFieldModel($fileColumn['field_type'], $fileColumn['name_field']);
                    $obj->deleteFiles($rec[$fldName]);
                    unset($obj);
                    
                }
            }
        }

        $db->qdirect("lock tables " . $tbl['name'] . " write");
        $db->q("delete from " . $tbl['name'] . " where id = $1", [$id3]);
        $db->q("update " . $tbl['name'] . " set ordr=ordr-1 where ordr>$1", [$rec['ordr']]);
        $db->qdirect("unlock tables");
    }    

}