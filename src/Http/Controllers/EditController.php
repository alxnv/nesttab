<?php
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
/**
 * Редактирование структуры - добавление поля к таблице
 */

class EditController extends BasicController {
    /**
     * Редактриование содержимого таблицы с идентификатором id
     * @global type $db
     * @global type $yy
     * @param type $r
     */
    public function index(Request $request) {
        
        global $db, $yy;

        $r = $request->all();
        $table_id = \yy::testExistsNotZero($r, 'id');
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);

        switch ($tbl['table_type']) {
            case 'O': //one record
                return $this->editOneRecTable($tbl, $r);
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
     */
    public function editOneRecTable(array $tbl, array $r) {
        // получаем строку с id=1 для one rec table (это единственная строка там)
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $recs = \Alxnv\Nesttab\Models\TableRecsModel::getRecAddObjects($columns, $tbl['name'], 1);
        return view('nesttab::edit-table.one_rec', ['tbl' => $tbl, 'recs' => $recs,
                'r' => $r]);
        
    }
    
    public function saveOne($id, Request $request) {
        global $db, $yy;
        $r = $request->all();
        $table_id = intval($id);
        if ($table_id == 0) {
            \yy::gotoErrorPage('Zero id');
        }
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);
        $recs = new \Alxnv\Nesttab\Models\TableRecsModel();
        $recs->save($tbl, 1, $r); // сохраняем запись с id=1
        if (!$recs->hasErr()) {
            Session::save();
            \yy::redirectNow($yy->baseurl . 'nesttab/edit?id=' . $table_id . 
                    '&saved_succesfully=1');
            exit;
        } else {
            //\yy::gotoErrorPage($s);
            $lnk = \yy::getErrorEditSession();
            $request->session()->flash($lnk, $recs->err->err);
            $lnk2 = \yy::getEditSession();
            $request->session()->flash($lnk2, $r);
            Session::save();
            \yy::redirectNow($yy->baseurl . 'nesttab/edit?id=' . $table_id);
            exit;
        }
        
        dd($id);
    }

    
}

