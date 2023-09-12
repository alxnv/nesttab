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

        switch ($tbl['table_type']) {
            case 'O': // one record
                return $this->editOneRecTable($tbl, $r, $id, $id2);
                break;
            case 'L': // list table
                return $this->editListTable($tbl, $r, $id, $id2);
                break;
            default:
                \yy::gotoErrorPage('Table type is not specified');
                break;
        }
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

        switch ($tbl['table_type']) {
            case 'L': // list table
                return $this->editListTableRec($tbl, $r, $id, $id2, $id3);
                break;
            default:
                \yy::gotoErrorPage('Table type is not specified');
                break;
        }
    }

    /**
     * Редактирование таблицы типа One Record - точка входа
     * @param array $tbl - данные таблицы
     * @param array $r - Request в виде массива
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     */
    public function editOneRecTable(array $tbl, array $r, int $id, int $id2) {
        // получаем строку с id=1 для one rec table (это единственная строка там)
        if ($id <> 0) die('This table must be on the top level');
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $requires = [];
        $rec_id = 1; // record 'id' field value
        $tableModel = \Alxnv\Nesttab\Models\Factory::createTableModel('one'); 
        $recs = $tableModel->getRecAddObjects($columns, $tbl['name'], 1, $requires);
        $lnk2 = \yy::getEditSession();
        if (Session::has($lnk2)) {
            $lnk = \yy::getErrorEditSession();
            $er2 = session($lnk);
            //dd($er2);
            $r_edited = session($lnk2);
            $r = $r_edited; //\yy::addKeys($r, $r_edited);
            // проставить значения полей из сессии (бывший post) в $recs
            $recs = $tableModel->setValues($recs, $r);
        }
        //dd($r);
        return view('nesttab::edit-table.one_rec', ['tbl' => $tbl, 'recs' => $recs,
                'r' => $r, 'requires' => $requires, 'table_id' => $id2, 'rec_id' => $rec_id]);
        
    }
    
    /**
     * Редактирование таблицы типа List - точка входа
     * @param array $tbl - данные таблицы
     * @param array $r - Request в виде массива
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     */
    public function editListTable(array $tbl, array $r, int $id, int $id2) {
        // получаем строку с id=1 для one rec table (это единственная строка там)
        global $yy;
        //$columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $requires = [];
        $parent_table_id = $tbl['parent_tbl_id'];
        if ($parent_table_id == 0) {
            // top level table
            $parent_table_rec = [];
            $recs = DB::table($tbl['name'])->paginate($yy->settings2['recs_per_page']);
        } else {
            // nested table
        }
        //$tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id2);
        $rec_id = 1; // record 'id' field value !!! todo: replace
        //$recs = \Alxnv\Nesttab\Models\TableRecsModel::getRecAddObjects($columns, $tbl['name'], 1, $requires);
        $lnk2 = \yy::getEditSession();
        if (Session::has($lnk2)) {
            $lnk = \yy::getErrorEditSession();
            $er2 = session($lnk);
            //dd($er2);
            $r_edited = session($lnk2);
            $r = $r_edited; //\yy::addKeys($r, $r_edited);
            // проставить значения полей из сессии (бывший post) в $recs
            //$recs = \Alxnv\Nesttab\Models\TableRecsModel::setValues($recs, $r);
        }
        //dd($r);
        return view('nesttab::edit-table.list', ['tbl' => $tbl, 'recs' => $recs,
                'r' => $r, 'requires' => $requires, 'table_id' => $id2, 'rec_id' => $rec_id,
                'parent_id' => $id,
                'parent_table_id' => $parent_table_id, 'parent_table_rec' => $parent_table_rec]);
        
    }

    /**
     * Редактирование одной записи таблицы типа List
     * @param array $tbl - данные таблицы
     * @param array $r - Request в виде массива
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     * @param int $id3 - id of the record (0 for new record)
     */
    public function editListTableRec(array $tbl, array $r, int $id, int $id2, int $id3) {
        // получаем строку с id=1 для one rec table (это единственная строка там)
        global $yy;
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $requires = [];
        $parent_table_id = $tbl['parent_tbl_id'];
        if ($parent_table_id == 0) {
            // top level table
            $parent_table_rec = [];
        } else {
            // nested table
        }
        //$tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id2);
        $rec_id = $id3; // record 'id' field value !!! todo: replace
        //$recs = \Alxnv\Nesttab\Models\TableRecsModel::getRecAddObjects($columns, $tbl['name'], 1, $requires);
        $tableModel = \Alxnv\Nesttab\Models\Factory::createTableModel('list'); 
        $recs = $tableModel->getRecAddObjects($columns, $tbl['name'], $id3, $requires);
        $lnk2 = \yy::getEditSession();
        if (Session::has($lnk2)) {
            $lnk = \yy::getErrorEditSession();
            $er2 = session($lnk);
            //dd($er2);
            $r_edited = session($lnk2);
            $r = $r_edited; //\yy::addKeys($r, $r_edited);
            // проставить значения полей из сессии (бывший post) в $recs
            //$recs = \Alxnv\Nesttab\Models\TableRecsModel::setValues($recs, $r);
        }
        //dd($r);
        return view('nesttab::edit-table.list_rec', ['tbl' => $tbl, 'recs' => $recs,
                'r' => $r, 'requires' => $requires, 'table_id' => $id2, 'rec_id' => $rec_id,
                'parent_id' => $id,
                'parent_table_id' => $parent_table_id, 'parent_table_rec' => $parent_table_rec]);
        
    }

    /**
     * save data for 'one' table type
     * @global \Alxnv\Nesttab\Http\Controllers\type $db
     * @global \Alxnv\Nesttab\Http\Controllers\type $yy
     * @param type $id
     * @param Request $request
     */
    public function saveOne($id, Request $request) {
        global $db, $yy;
        $r = $request->all();
        //dd($r);
        $table_id = intval($id);
        if ($table_id == 0) {
            \yy::gotoErrorPage('Zero id');
        }
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $requires_stub = [];
        $recs = \Alxnv\Nesttab\Models\Factory::createTableModel('one'); 
        $recs->getRecAddObjects($columns, $tbl['name'], 1, $requires_stub);
        $recs->save($columns, $tbl, 1, $r); // сохраняем запись с id=1
        if (!$recs->hasErr()) {
            $request ->session()->flash('saved_successfully', 1);
            Session::save();
            \yy::redirectNow($yy->nurl . 'edit/0/' . $table_id);
            exit;
        } else {
            //\yy::gotoErrorPage($s);
            $lnk = \yy::getErrorEditSession();
            //session([$lnk => $recs->err->err]);
            $request->session()->flash($lnk, $recs->err->err);
            //dd($recs->err->err);
            $lnk2 = \yy::getEditSession();
            //session([$lnk2 => $r]);
            $request->session()->flash($lnk2, $r);
            Session::save();
            \yy::redirectNow($yy->nurl . 'edit/0/' . $table_id);
            exit;
        }
        
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
        $r = $request->all();
        //dd($r);
        $table_id = intval($id2);
        if ($table_id == 0) {
            \yy::gotoErrorPage('Zero id');
        }
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $requires_stub = [];
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $recs = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        $recs->getRecAddObjects($columns, $tbl['name'], $id3, $requires_stub);
        $recs->save($columns, $tbl, $id3, $r); // сохраняем запись
        if (!$recs->hasErr()) {
            $request ->session()->flash('saved_successfully', 1);
            Session::save();
            \yy::redirectNow($yy->nurl . 'edit/' . $id . '/' . $table_id);
            exit;
        } else {
            //\yy::gotoErrorPage($s);
            $lnk = \yy::getErrorEditSession();
            //session([$lnk => $recs->err->err]);
            $request->session()->flash($lnk, $recs->err->err);
            //dd($recs->err->err);
            $lnk2 = \yy::getEditSession();
            //session([$lnk2 => $r]);
            $request->session()->flash($lnk2, $r);
            Session::save();
            \yy::redirectNow($yy->nurl . 'editrec/' . $id . '/' . $table_id . '/' . $id3);
            exit;
        }
        
    }
    
}

