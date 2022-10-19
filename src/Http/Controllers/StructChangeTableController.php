<?php
namespace app\controllers;

/**
 * Редактирование структуры заданной таблицы
 */

class StructChangeTableController extends \app\backend\controllers\StructPrevController {
    public function editAction($r) {
        
        global $db, $yy;
        
        if (!isset($r['t']) || (intval($r['t']) ==0)) {
            \yy::gotoErrorPage('Not valid table id as an argument');
        }
        $n = intval($r['t']);
        $tbl = $db->q("select * from yy_tables where id=$1", [$n]);
        if (is_null($tbl)) \yy::gotoErrorPage('Table not found');

        $flds = \app\models\StructColumnsModel::getTableColumns($n);
        
        
        $this->render(['tbl' => $tbl, 'tblname' => $tbl['name'], 'tbl_id' => $n,
            'flds' => $flds]);
    }
    
    public function moveAction($r) {
        
        global $db, $yy;
        
        if (!isset($r['t']) || (intval($r['t']) ==0)) {
            \yy::gotoErrorPage('Not valid table id as an argument');
        }
        if (!isset($r['prev']) || !isset($r['id']) || !isset($r['moveto'])) {
            \yy::gotoErrorPage('Not all required parameters passed');
        }
        $n = intval($r['t']);
        $prev = substr($r['prev'], 0, 200);
        $tbl = $db->q("select * from yy_tables where id=$1", [$n]);
        if (is_null($tbl)) \yy::gotoErrorPage('Table not found');

        \app\models\StructTableFieldsModel::move(intval($r['id']), intval($r['moveto']));
        header('Location: ' . $yy->baseurl . 'struct-change-table/edit/t/' . $n . '/prev/' . $prev);
        exit;
    }
    
    public function deleteAction($r) {
        // вызывается в режиме json
        global $yy, $db;
        $this->switchToJson();
        $err = '';
        if (!isset($r['id'])) $err .= chr(13) . 'Field id has not been passed';
        if ($err == '') {
            $column = $db->q("select * from yy_columns where id=$1", [$r['id']]);
            if (!$column) $err .= chr(13) . 'The record in yy_columns not found';
        }
        if ($err == '') {
            $fld =  (new \app\models\StructTableFieldsModel())->getOne(intval($column['field_type']));
            if (is_null($fld)) $err .= chr(13) . 'Field def in table is not found';
            
        }
        $table_id = $column['table_id'];
        $tbl = $db->q("select * from yy_tables where id=$1", [$table_id]);
        if (is_null($tbl)) $err .= 'Table definition is not found';
        
        if ($err == '') {
            $s2 = '\\app\\models\\field_struct\\' . ucfirst($fld['name']) .'Model';
            $field_model = new $s2();
            $s = $field_model->delete($column, $fld, $tbl, $r);
            $err .= $s;
        }
        if ($err == '')
            echo json_encode([]);
            else echo json_encode(['error' => nl2br(\yy::qs($err))]);

    }
    
}

