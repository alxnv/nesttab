<?php
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;
/**
 * Редактирование структуры заданной таблицы
 */

class StructChangeTableController extends BasicController {
    public function edit($id, Request $request) {
        
        global $db, $yy;

        $db->loadAllTablesData();
        $prev_link = ($request->has('prev') ? substr($request->input('prev'), 0, 500) : '');

        if (!isset($id) || (intval($id) == 0)) {
            \yy::gotoErrorPage('Not valid table id as an argument');
        }
        $n = intval($id);
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($n);

        $flds = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumns($n);
        
        
        return view('nesttab::struct_change_table', ['tbl' => $tbl, 'tblname' => $tbl['name'], 'tbl_id' => $n,
            'flds' => $flds, 'prev_link' => $prev_link]);
    }
    
    public function move($tbl_id, $id, $pos) {
        
        global $db, $yy;
        
        $r = ['t' => $tbl_id, 'id' => $id, 'moveto' => $pos];
        if (!isset($r['t']) || (intval($r['t']) ==0)) {
            \yy::gotoErrorPage('Not valid table id as an argument');
        }
        if (!isset($r['id']) || !isset($r['moveto'])) {
            \yy::gotoErrorPage('Not all required parameters passed');
        }
        $n = intval($r['t']);
        //$prev = substr($r['prev'], 0, 200);
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($n);

        \Alxnv\Nesttab\Models\ColumnsModel::move(intval($r['id']), intval($r['moveto']));
        \yy::redirectNow($yy->nurl . 'struct-change-table/edit/' . $n . '/0');
        exit;
    }
    
    public function delete($id) {
        // вызывается в режиме json
        global $yy, $db;
        $err = '';
        if (!isset($id)) $err .= chr(13) . 'Field id has not been passed';
        $r = ['id' => intval($id)];
        if ($err == '') {
            $column = $db->q("select * from yy_columns where id=$1", [$r['id']]);
            if (!$column) $err .= chr(13) . 'The record in yy_columns not found';
        }
        if ($err == '') {
            $fld =  (new \Alxnv\Nesttab\Models\ColTypesModel())->getOne(intval($column['field_type']));
            if (is_null($fld)) $err .= chr(13) . 'Field def in table is not found';
            
        }
        $table_id = $column['table_id'];
        $tbl = $db->q("select * from yy_tables where id=$1", [$table_id]);
        if (is_null($tbl)) $err .= 'Table definition is not found';
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $tableModel = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        if (($err == '') && (!$tableModel->isDeletableField($column['name']))) {
            $err = 'This field can not be deleted';
        }
        
        if ($err == '') {
            $field_model = \Alxnv\Nesttab\Models\Factory::createFieldModel($fld['id'], $fld['name']);
            $s = $field_model->delete($column, $fld, $tbl, $r);
            $err .= $s;
        }
        if ($err == '')
            $arr = [];
            else $arr = ['error' => nl2br(\yy::qs($err))];
        return response()->json($arr);

    }
    
}

