<?php
/**
 * Редактирование структуры - добавление поля к таблице
 */
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class EditController extends BasicController {
    /**
     * Перемещение записи (изменение ordr) для L
     * @param int $id - table id
     * @param int $id2 - record id
     * @param int $id3 - new ordr value
     * @param int $id4 - page to return to (maybe)
     * @param int $id5 - parent record id (0 if it is top level table)
     */
    public function move(int $id, int $id2, int $id3, int $id4, int $id5) {
        // вызываем модель тип L, moveRec()
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar('L');
        $tableModel = \Alxnv\Nesttab\Models\Factory::createTableModel($type);
        return $tableModel->moveRec($id, $id2, $id3, $id4, $id5);
    }
    /**
     * Редактриование содержимого таблицы с идентификатором id
     * @global type $db
     * @global type $yy
     * @param int $id - id of the parent record in the parent table (if it is = 0, then its top level)
     * @param int $id2 - id of the table
     * @param int $id3 - id of the record in the table
     * @param type $request
     */
    public function index(int $id, int $id2, Request $request) {
        
        global $db, $yy;

        $this->maintain(); // поддержка работоспособности сервера
        $db->loadAllTablesData();
        $r = $request->all();
        //$table_id = \yy::testExistsNotZero($r, 'id');
        
        //$tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id2);
        $tbl = \Alxnv\Nesttab\core\TableHelper::getOneFromMemory($id2);
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $tableModel = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        return $tableModel->editTable($tbl, $r, $id, $id2);
    }
    
    /**
     * Редактриование содержимого таблицы с идентификатором id (кроме типа one)
     * @global type $db
     * @global type $yy
     * @param int $id - id of the parent record in the parent table (if it is = 0, then its top level)
     * @param int $id2 - id of the table
     * @param int $id3 - id of the record in the table
     * @param type $request
     */
    public function editRec(int $id, int $id2, int $id3, Request $request) {
        
        global $db, $yy;

        $this->maintain(); // поддержка работоспособности сервера
        $db->loadAllTablesData();
        $r = $request->all();
        //$table_id = \yy::testExistsNotZero($r, 'id');
        
        $tbl = \Alxnv\Nesttab\core\TableHelper::getOneFromMemory($id2);
        //$tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id2);
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
    public function saveOne(int $id, int $id2, Request $request) {
        global $db, $yy;
        //dd($r);
        $table_id = intval($id2);
        if ($table_id == 0) {
            \yy::gotoErrorPage('Zero id');
        }
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $recs = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        $recs->saveTable($tbl, $id, $id2, $request);
        
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
        $table_id = intval($id2);
        if ($table_id == 0) {
            \yy::gotoErrorPage('Zero id');
        }
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $recs = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        $recs->saveTableRec($tbl, $id, $id2, $id3, $request);
    }

    /**
     * delete table record (how is this function for 'one' table type?)
     * @global \Alxnv\Nesttab\Http\Controllers\type $db
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     * @param int $id3 - id of the record (0 for new record)
     * @param Request $request
     */
    public function delete(int $id, int $id2, int $id3, Request $request) {
        global $db, $yy;
        $table_id = intval($id2);
        if ($table_id == 0) {
            \yy::gotoErrorPage('Zero id');
        }
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $recs = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        if ($type === 'one') {
            throw new \Exception('not implemented');
        }
        $recs->deleteTableRec($tbl, $id, $id2, $id3, $request, $type);
        
    }
    
}

