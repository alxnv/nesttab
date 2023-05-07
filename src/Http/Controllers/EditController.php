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
    public function index($id, Request $request) {
        
        global $db, $yy;

        if (!isset($id) || (intval($id) == 0)) {
            \yy::gotoErrorPage('Not valid table id as an argument');
        }
        $table_id = intval($id);
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);

        switch ($tbl['table_type']) {
            case 'O': //one record
                return $this->editOneRecTable($tbl, $request);
                break;
            default:
                \yy::gotoErrorPage('Table type is not specified');
                break;
        }
    }
    

    public function editOneRecTable($tbl, $request) {
        /**
         * Редактирование таблицы типа One Record - точка входа
         */
        // получаем строку с id=1 для one rec table (это единственная строка там)
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $recs = \Alxnv\Nesttab\Models\TableRecsModel::getRecAddObjects($columns, $tbl['name'], 1);
        return view('nesttab::edit-table.one_rec', ['tbl' => $tbl, 'recs' => $recs]);
        
    }
    
}

