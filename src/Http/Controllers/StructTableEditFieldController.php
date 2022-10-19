<?php
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Редактирование структуры - добавление поля к таблице
 */

class StructTableEditFieldController extends BasicController {
    /**
     * Выбрать тип поля
     * @global type $db
     * @global type $yy
     * @param type $r
     */
    public function index($id, Request $request) {
        
        global $db, $yy;
        
        //var_dump($r);exit;
        $prev_link = ($request->has('prev') ? substr($request->input('prev'), 0, 500) : '');
        
        if (!isset($id) || (intval($id) == 0)) {
            \yy::gotoErrorPage('Not valid table id as an argument');
        }
        $table_id = intval($id);
        $tbl = $db->q("select * from yy_tables where id=$1", [$table_id]);
        if (is_null($tbl)) \yy::gotoErrorPage('Table not found');

        
        $arr = (new \Alxnv\Nesttab\Models\StructTableFieldsModel())->getFieldsList();
        
        return view('nesttab::struct-table-edit-field.index', ['tbl' => $tbl, 'tblname' => $tbl['id'], 'table_id' => $table_id,
            'field_types' => $arr, 'prev_link' => $prev_link]);
    }
    

    /**
     * По выбранному типу поля вывести форму редактирования данного типа поля
     * @param type $r
     */
    public function step2Action($r) {
        global $db, $yy;
        if (!isset($r['t']) || (intval($r['t']) == 0)) {
            \yy::gotoErrorPage('Not valid table id as an argument');
        }
        $table_id = intval($r['t']);
        $tbl = $db->q("select * from yy_tables where id=$1", [$table_id]);
        if (is_null($tbl)) \yy::gotoErrorPage('Table not found');
        $lnk2 = \yy::get_edit_session();
        $b = false;
        if (isset($_SESSION[$lnk2])) {
            $r_edited = $_SESSION[$lnk2];
            $r = \yy::add_keys($r, $r_edited);
            unset($_SESSION[$lnk2]);
            $b = true;
        }

        if (isset($r['id'])) {
            $rec = $db->q("select * from yy_columns where id=$1", [$r['id']]);
            if (!$rec)  \yy::gotoErrorPage('No record in table yy_columns');
            $r['field_type_id'] = $rec['field_type'];
            $params = json_decode($rec['parameters']);
            $rec = \yy::add_keys($rec, (array)$params);
            if (!$b) $r = \yy::add_keys($r, $rec);
            $fld =  (new \app\models\StructTableFieldsModel())->getOne(intval($r['field_type_id']));
        } else {
            if (!isset($r['field_type_id'])) \yy::gotoErrorPage('Field type is not defined');
            $fld =  (new \app\models\StructTableFieldsModel())->getOne(intval($r['field_type_id']));
            if (!$b) $r['name'] = \app\models\StructTableFieldsModel::getNextNameOfType($tbl['id'], $fld['name']);
            
        }
        if (is_null($fld)) \yy::gotoErrorPage('Field def in table is not found');
        $this->render(['tbl' => $tbl, 'tblname' => $tbl['name'], 'tbl_id' => $table_id,
            'field_type_id' => intval($r['field_type_id']), 'fld' => $fld, 'r' => $r], 
                $fld['name']); // вызываем контроллер
                  // названный по $tbl['name']
    }
    
    /**
     * сохранить поле в структуре таблицы
     * @param type $r
     */
    public function saveAction($r) {
        global $db, $yy;
        if (!isset($r['field_type_id'])) \yy::gotoErrorPage('Field type is not defined');
        if (!isset($r['t']) || (intval($r['t']) == 0)) {
            \yy::gotoErrorPage('Not valid table id as an argument');
        }
        $table_id = intval($r['t']);
        $tbl = $db->q("select * from yy_tables where id=$1", [$table_id]);
        if (is_null($tbl)) \yy::gotoErrorPage('Table not found');
        $old_values = [];
        if (isset($r['id'])) {
            $r['id'] = intval($r['id']);
            $old_values = $db->q("select * from yy_columns where id=$1", [$r['id']]);
            if (!$old_values) \yy::gotoErrorPage('No record in table yy_columns');
        }
        $fld =  (new \app\models\StructTableFieldsModel())->getOne(intval($r['field_type_id']));
        if (is_null($fld)) \yy::gotoErrorPage('Field def in table is not found');
        $s2 = '\\app\\models\\field_struct\\' . ucfirst($fld['name']) .'Model';
        $field_model = new $s2();
        $s = $field_model->save($tbl, $fld, $r, $old_values);
        if ($s == '') {
            header('Location: ' . $yy->baseurl . 'struct-change-table/edit/t/' . $table_id .
                    '/prev/' . $this->prev_link);
            exit;
        } else {
            //\yy::gotoErrorPage($s);
            $lnk = \yy::get_error_edit_session();
            $_SESSION[$lnk] = $s;
            $lnk2 = \yy::get_edit_session();
            $_SESSION[$lnk2] = $r;
            $ids = (isset($r['id']) ? '/id/' . intval($r['id']) : '');
            header('Location: ' . $yy->baseurl . 'struct-table-edit-field/step2/t/' . $table_id .
                    $ids . '/prev/' . $this->prev_link . '/is_error/1/field_type_id/' . intval($r['field_type_id']));
            exit;
        }
    }
    
}

