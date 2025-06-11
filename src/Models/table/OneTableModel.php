<?php

/**
 * Model for one record table (one)
 */

namespace Alxnv\Nesttab\Models\table;
use Illuminate\Support\Facades\Session;

class OneTableModel extends BasicTableModel {
    /**
     * Редактирование таблицы типа One Record - точка входа
     * @param array $tbl - данные таблицы
     * @param array $r - Request в виде массива
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     */
    public function editTable(array $tbl, array $r, int $id, int $id2) {
        // получаем строку с id=1 для one rec table (это единственная строка там)
        if ($id <> 0) die('This table must be on the top level');

        $columnsModel = new \Alxnv\Nesttab\Models\ColumnsModel();
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        // получаем имена полей участвующих в отображении всех полей типа select данной таблицы
        $selectFldNames = \Alxnv\Nesttab\Models\ColumnsModel::getSelectFldNames($tbl['id'], $columns);
        $requires = [];
        $parent_table_id = $tbl['p_id'];
        if ($parent_table_id == 0) {
            // top level table
            $parent_table_rec = [];
        } else {
            // nested table
        }
        $id3 = 1;
        //$tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id2);
        $rec_id = $id3; // record 'id' field value !!! todo: replace
        //$recs = \Alxnv\Nesttab\Models\TableRecsModel::getRecAddObjects($columns, $tbl['name'], 1, $requires);
        // get data from db
        $lnk2 = \yy::getEditSession();
        if (Session::has($lnk2)) {
            $lnk = \yy::getErrorEditSession();
            $er2 = session($lnk);
            //dd($er2);
            $r_edited = session($lnk2);
            $r = $r_edited; //\yy::addKeys($r, $r_edited);
        }
        if ($id3 == 0) {
            $rec = [];
        } else {
            $rec = \Alxnv\Nesttab\Models\ArbitraryTableModel::getOne($tbl['name'], $id3);
        }
        /**
         * Добавляем к $columns данные из БД $rec 
         *  также добавляем соответствующие объекты типов полей к полям $columns,
         *  преобразуем данные в формат для отображения на странице редактирования
         * @return array - измененный $columns
         */
        $recs = $this->getRecAddObjects($columns, $rec, $requires, $r);
        $errorMsg = '';
        // получаем текущие значения всех полей select данной записи
        $selectsInitialValues = $columnsModel->getSelectsInitialValues($rec, $recs, $selectFldNames, $errorMsg);

        if (Session::has($lnk2)) {
            // проставить значения полей из сессии (бывший post) в $recs
            $recs = $this->setValues($recs, $r);
            Session::forget($lnk2);
        }
        return view('nesttab::edit-table.one_rec', ['tbl' => $tbl, 'recs' => $recs,
                'extra' => ['selectsInitialValues' => $selectsInitialValues],
                'errorMsg' => $errorMsg,
                'r' => $r, 'requires' => $requires, 'table_id' => $id2, 'rec_id' => $rec_id]);
        
    }
    /**
     * Сохраняем данные редактирования в БД, либо устанваливаем сообщения об ошибках
     * @param array &$columns 
     * @param array $tbl - массив данных о таблице
     * @param int $id - идентификатор записи
     * @param array $r - (array)Request
     */
    public function save(array &$columns, array $tbl, int $id, array &$r) {
        //$this->setErr('', 'fdsafd');
        global $yy;
        $yy->loadPhpScript(app_path() . '/Models/nesttab/tables/' 
            . ucfirst($tbl['name']) . '.php');
        // get old values for image and file field types
        $this->getImageFileValues($columns, $tbl, $id);
        $this->setOldValues($columns); // установить поле value_old для всех полей
        // кроме image, file
        $arFI = \Alxnv\Nesttab\core\ArrayHelper::getArrayIndexes($columns, 'name');
        for ($i = 0; $i < count($columns); $i++)  {
            //$columns[$i]['obj'] = \Alxnv\Nesttab\Models\Factory::createFieldModel($columns[$i]['field_type'], $columns[$i]['name_field']);
            $columns[$i]['parameters'] = (array)json_decode($columns[$i]['parameters']);
            // значение для поля типа bool не будет в post массиве если он unchecked
            if ($columns[$i]['name_field'] == 'bool') {
                $value = (isset($r[$columns[$i]['name']]) ? 1 : 0);
            } else {
                $value = isset($r[$columns[$i]['name']]) ?
                                $r[$columns[$i]['name']] : '';
            }
                // устанавливает сообщения об ошибках для $this
            $toContinue = true;
            $isNewRec = false; // todo: change it to appropriate value
            $value_old = (isset($columns[$i]['value_old']) ? $columns[$i]['value_old'] 
                    : null);
            if ('' <> ($s77 = \yy::userFunctionIfExists($tbl['name'], 'onValidate'))) 
                $s77($value, $value_old, $columns, $i, $r, $this, $columns[$i]['name'], $isNewRec, $toContinue, $arFI);

            $columns[$i]['value'] = $value;
            
            if ($toContinue) {
                $columns[$i]['value'] = $columns[$i]['obj']
                    ->validate($value, $this,
                            $columns[$i]['name'], $columns, $i, $r);
            }
                
        }

        if (!$this->hasErr()) {
            // ошибок нет. записываем данные в БД
            $this->postProcess1($tbl, $columns, $r); // постпроцессинг для всех типов данных
               // кроме image, file
            $yy->settings2['extended_db_messages'] = false; // short error messages
            $error = $this->saveToDB($tbl, $columns, $id);
            if ($error == '') {
                // если основные поля сохранены без ошибок
                $this->postProcess($tbl, $columns, $r); // записываем загруженные документы и изображения
                // ошибок нет. записываем данные в БД
                $this->saveToDBFiles($tbl, $columns, $id);
            }
            $this->afterDataSaved($tbl, $error, $id, $columns); // вызываем коллбэк после сохранения
              // данных или ошибки сохранения
        }
    }
    /**
     * Save table data
     * @param array $tbl - table data
     * @param int $id - id of the table
     * @param Request $request
     */
    
    public function saveTable(array $tbl, int $id, object $request) {
        global $yy;
        $r = $request->all();
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $requires_stub = [];
        // get data from db
        $rec = \Alxnv\Nesttab\Models\ArbitraryTableModel::getOne($tbl['name'], 1);
        $this->getRecAddObjects($columns, $rec, $requires_stub);
        $this->save($columns, $tbl, 1, $r); // сохраняем запись с id=1
        if (!$this->hasErr()) {
            //$request ->session()->flash('saved_successfully', 1);
            session(['saved_successfully' => 1]);
            Session::save();
            \yy::redirectNow($yy->nurl . 'edit/0/' . $tbl['id']);
            exit;
        } else {
            //\yy::gotoErrorPage($s);
            $lnk = \yy::getErrorEditSession();
            session([$lnk => $this->err->err]);
            //$request->session()->flash($lnk, $this->err->err);
            //dd($recs->err->err);
            $lnk2 = \yy::getEditSession();
            session([$lnk2 => $r]);
            //$request->session()->flash($lnk2, $r);
            Session::save();
            \yy::redirectNow($yy->nurl . 'edit/0/' . $tbl['id']);
            exit;
        }
        
    }
}