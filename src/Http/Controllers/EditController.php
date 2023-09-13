<?php
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
/**
 * Редактирование структуры - добавление поля к таблице
 */

class EditController extends BasicController {
    /**
     * Редактриование содержимого таблицы с идентификатором id
     * @global type $db
     * @global type $yy
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 - id of the table
     * @param type $request
     */
    public function index(int $id, int $id2, Request $request) {
        
        global $db, $yy;

        $this->maintain(); // поддержка работоспособности сервера
        $r = $request->all();
        //$table_id = \yy::testExistsNotZero($r, 'id');
        
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id2);
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $tableModel = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        return $tableModel->editTable($tbl, $r, $id, $id2);
    }
    
    /**
     * Редактриование содержимого таблицы с идентификатором id (кроме типа one)
     * @global type $db
     * @global type $yy
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 - id of the table
     * @param type $request
     */
    public function editRec(int $id, int $id2, int $id3, Request $request) {
        
        global $db, $yy;

        $this->maintain(); // поддержка работоспособности сервера
        $r = $request->all();
        //$table_id = \yy::testExistsNotZero($r, 'id');
        
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id2);
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $tableModel = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        return $tableModel->editTableRec($tbl, $r, $id, $id2, $id3);
    }

    


    /**
     * save data for 'one' table type
     * @global \Alxnv\Nesttab\Http\Controllers\type $db
     * @global \Alxnv\Nesttab\Http\Controllers\type $yy
     * @param int $id
     * @param Request $request
     */
    public function saveOne($id, Request $request) {
        global $db, $yy;
        //dd($r);
        $table_id = intval($id);
        if ($table_id == 0) {
            \yy::gotoErrorPage('Zero id');
        }
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $recs = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        $recs->saveTable($tbl, $id, $request);
        
        //dd($id);
    }

    /**
     * save data for all table types (except 'one' type)
     * @global \Alxnv\Nesttab\Http\Controllers\type $db
     * @global \Alxnv\Nesttab\Http\Controllers\type $yy
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     * @param int $id3 - id of the record (0 for new record)
     * @param Request $request
     */
    public function save(int $id, int $id2, int $id3, Request $request) {
        global $db, $yy;
        //dd($id, $id2, $id3);
        //dd($r);
        $table_id = intval($id2);
        if ($table_id == 0) {
            \yy::gotoErrorPage('Zero id');
        }
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $recs = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        $recs->saveTableRec($tbl, $id, $id2, $id3, $request);
    }
    
}

