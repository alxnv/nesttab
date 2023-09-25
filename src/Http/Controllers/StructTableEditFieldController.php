<?php
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;
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
        /*$s = session('trw', '8888');
Session::put('trw', '777');
Session::save();
dd($s);*/

        
        //var_dump($r);exit;
        $prev_link = ($request->has('prev') ? substr($request->input('prev'), 0, 500) : '');
        
        if (!isset($id) || (intval($id) == 0)) {
            \yy::gotoErrorPage('Not valid table id as an argument');
        }
        $table_id = intval($id);
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);

        
        $arr = (new \Alxnv\Nesttab\Models\ColTypesModel())->getFieldsList();
        
        return view('nesttab::struct-table-edit-field.index', ['tbl' => $tbl, 'tblname' => $tbl['id'], 'table_id' => $table_id,
            'field_types' => $arr, 'prev_link' => $prev_link]);
    }
    

    /**
     * По выбранному типу поля вывести форму редактирования данного типа поля
     * @param int $id - id таблицы
     * @param int $parm - id редактируемого поля в yy_columns (0, если новое поле)
     *  если 0, то $request->input('field_type_id') == id типа поля
     * @param type $request
     */
    public function step2($id, $parm, Request $request) {
        global $db, $yy;
        //Session::put('ttt', 7777);
        //$b = $request->has('field_type_id');
        //dd($request->parm);
        $r = $request->all();
        if (intval($parm) <> 0) $r['id']=intval($request->parm);
        if (!isset($id) || (intval($id) == 0)) {
            \yy::gotoErrorPage('Not valid table id as an argument');
        }
        $table_id = intval($id);
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $tableModel = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        $lnk2 = \yy::getEditSession();
        $b = false;
        if (Session::has($lnk2)) {
            $r_edited = session($lnk2);
            $r = \yy::addKeys($r, $r_edited);
            //Session::remove($lnk2);
            $b = true;
        }

        if (isset($r['id'])) {
            $rec = $db->q("select * from yy_columns where id=$1", [$r['id']]);
            if (!$rec)  \yy::gotoErrorPage('No record in table yy_columns');
            $r['field_type_id'] = $rec['field_type'];
            $params = json_decode($rec['parameters']);
            $rec = \yy::addKeys($rec, (array)$params);
            if ($b) {
                $r['ordr'] = $rec['ordr'];
            } else {
                $r = \yy::addKeys($r, $rec);
            }
            $fld =  (new \Alxnv\Nesttab\Models\ColTypesModel())->getOne(intval($r['field_type_id']));
        } else {
            if (!$request->has('field_type_id')) \yy::gotoErrorPage('Field type is not defined');
            $fld =  (new \Alxnv\Nesttab\Models\ColTypesModel())->getOne(intval($request->field_type_id));
            if (!$b) $r['name'] = \Alxnv\Nesttab\Models\ColumnsModel::getNextNameOfType($tbl['id'], $fld['name']);
            
        }
        if (is_null($fld)) \yy::gotoErrorPage('Field def in table is not found');

        // $r['field_type_id'] - id типа записи
        // получаем объект данного типа поля
        $fieldModel = \Alxnv\Nesttab\Models\Factory::createFieldModel($r['field_type_id'], $fld['name']);
        return view('nesttab::struct-table-edit-field.' . $fld['name'], ['tbl' => $tbl, 'tblname' => $tbl['name'], 'tbl_id' => $table_id,
            'field_type_id' => intval($r['field_type_id']), 'fld' => $fld, 'r' => $r,
            'tableModel' => $tableModel, 'fieldModel' =>$fieldModel] 
                ); // вызываем контроллер
                  // названный по $fld['name']

    }
    
    /**
     * сохранить поле в структуре таблицы
     * @param type $id
     */
    public function save($id, Request $request) {
        global $db, $yy;
        $r = $request->all();
        $r['t'] = intval($id);
        if (!isset($r['field_type_id'])) \yy::gotoErrorPage('Field type is not defined');
        if (!isset($r['t']) || (intval($r['t']) == 0)) {
            \yy::gotoErrorPage('Not valid table id as an argument');
        }
        $table_id = intval($r['t']);
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($table_id);
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $tableModel = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        $old_values = [];
        if (isset($r['id'])) {
            $r['id'] = intval($r['id']);
            $old_values = $db->q("select * from yy_columns where id=$1", [$r['id']]);
            if (!$old_values) \yy::gotoErrorPage('No record in table yy_columns');
        }
        $fld =  (new \Alxnv\Nesttab\Models\ColTypesModel())->getOne(intval($r['field_type_id']));
        if (is_null($fld)) \yy::gotoErrorPage('Field def in table is not found');
        $field_model = \Alxnv\Nesttab\Models\Factory::createFieldModel($fld['id'], $fld['name']);
    
        // adding or editing field, lock this process for max_execution_time seconds
        $lock = Cache::lock('addfield', $yy->settings2['max_exec'] + 1);
        try {
            $yy->setExitReleaseLock('addfield');
            $lock->block($yy->settings2['time_to_lock_add_field']);
            $field_model->tableModel = $tableModel;
            $field_model->save($tbl, $fld, $r, $old_values);

            // Lock acquired after waiting a maximum of 5 seconds...
        } catch (LockTimeoutException $e) {
            // Unable to acquire lock...
            \yy::gotoErrorPage('Unable to lock process');
        } finally {
            optional($lock)->release();
        }

        if (!$field_model->hasErr()) {
            Session::save();
            \yy::redirectNow($yy->nurl . 'struct-change-table/edit/' . $table_id . '/0');
            exit;
        } else {
            //\yy::gotoErrorPage($s);
            $lnk = \yy::getErrorEditSession();
            $request->session()->flash($lnk, $field_model->err->err);
            //session([$lnk => $field_model->err->err]);
            $lnk2 = \yy::getEditSession();
            $request->session()->flash($lnk2, $r);
            //session([$lnk2 => $r]);
            Session::save();
            $ids = (isset($r['id']) ? 'id=' . intval($r['id']) . '&' : '');
            \yy::redirectNow($yy->nurl . 'struct-table-edit-field/step2/' . $table_id .
                    '/0?' . $ids . 'is_error=1&field_type_id=' . intval($r['field_type_id']));
            exit;
        }
    }
    
}

