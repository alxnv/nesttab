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
    public function index(int $id, Request $request) {
        
        global $db, $yy;

        $this->maintain(); // поддержка работоспособности сервера
        $r = $request->all();
        //$table_id = \yy::testExistsNotZero($r, 'id');
        
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id);

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
        $requires = [];
        $recs = \Alxnv\Nesttab\Models\TableRecsModel::getRecAddObjects($columns, $tbl['name'], 1, $requires);
        $lnk2 = \yy::getEditSession();
        if (Session::has($lnk2)) {
            $lnk = \yy::getErrorEditSession();
            $er2 = session($lnk);
            $lnk = \yy::getErrorEditSession();
            $er2 = session($lnk);
            //dd($er2);
            $r_edited = session($lnk2);
            $r = $r_edited; //\yy::addKeys($r, $r_edited);
            // проставить значения полей из сессии (бывший post) в $recs
            $recs = \Alxnv\Nesttab\Models\TableRecsModel::setValues($recs, $r);
        }
        //dd($r);
        return view('nesttab::edit-table.one_rec', ['tbl' => $tbl, 'recs' => $recs,
                'r' => $r, 'requires' => $requires]);
        
    }
    
    public function saveOne($id, Request $request) {
        global $db, $yy;
        $r = $request->all();
        //dd($r);
        $table_id = intval($id);
        if ($table_id == 0) {
            \yy::gotoErrorPage('Zero id');
        }
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);
        $recs = new \Alxnv\Nesttab\Models\TableRecsModel();
        $recs->save($tbl, 1, $r); // сохраняем запись с id=1
        if (!$recs->hasErr()) {
            $request ->session()->flash('saved_successfully', 1);
            Session::save();
            \yy::redirectNow($yy->baseurl . 'nesttab/edit/' . $table_id);
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
            \yy::redirectNow($yy->baseurl . 'nesttab/edit/' . $table_id);
            exit;
        }
        
        //dd($id);
    }

    
}

