<?php

/**
 * Model for one record table (one)
 */

namespace Alxnv\Nesttab\Models\table;
use Illuminate\Support\Facades\Session;

class OneTableModel extends BasicTableModel {
    // create table structure, step 2, write to the tables
    // пытаемся создать таблицу указанного типа и с указанным именем
    /**
     * 
     * @global \Alxnv\Nesttab\Http\Controllers\type $yy
     * @global \Alxnv\Nesttab\Http\Controllers\type $db
     * @param array $r - request
     * @param type $message - message here returned (ok or error)
     * @param type $tableId - id of created table in yy_tables (no input value,
     *   returns it)
     * @param int $parentTableId - id of parent table for this table, or
     *    0, if its a top level table
     * @param int $idFieldSizeInBytes - size of field 'id' in bytes
     * @param array $parent_tbl - данные родительской таблицы (либо ['id' => 0] для таблицы
     *    верхнего уровня)
     * @param array $options : key 'toAddRec' - значит добавлять запись после создания таблицы
     *   (только для таблицы типа 'one')
     * @return boolean - if table creation was successful
     */
    public function createTable(array $r, &$message, &$tableId, int $parentTableId, 
            int $idFieldSizeInBytes, array $parent_tbl, array $options = []) {

        global $yy, $db;
        if ($parentTableId == 0) {
            $options = ['toAddRec' => 1];
        } else {
            $options = [];
        }
        return parent::createTable($r, $message, $tableId, $parentTableId, 
                $idFieldSizeInBytes, $parent_tbl, $options);

    }
     /**
     * Редактирование таблицы типа One Record - точка входа
     * @param array $tbl - данные таблицы
     * @param array $r - Request в виде массива
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     */
    public function editTable(array $tbl, array $r, int $id, int $id2) {
        // получаем строку с id=1 для one rec table (это единственная строка там)

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
        $id3 = ($id == 0 ? 1 : $id); // Id записи таблицы типа 'one'
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
        $rec = \Alxnv\Nesttab\Models\ArbitraryTableModel::getOne($tbl['name'], $id3, false);
        $hasRec = (!is_null($rec)); // найдена ли запись
        if (!$hasRec) {
            $rec = [];
        }
        /**
         * Добавляем к $columns данные из БД $rec 
         *  также добавляем соответствующие объекты типов полей к полям $columns,
         *  преобразуем данные в формат для отображения на странице редактирования
         * @return array - измененный $columns
         */
        if ($hasRec) {
            $recs = $this->getRecAddObjects($columns, $rec, $requires, $r);
        } else {
            $recs = $this->getDefaults($columns, $requires); // получаем значения по умолчанию для новой записи
        }
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
                'errorMsg' => $errorMsg, 'hasRec' => $hasRec, 'rec' => $rec, 'parent_id' => $id,
                'r' => $r, 'requires' => $requires, 'table_id' => $id2, 'rec_id' => $rec_id]);
        
    }
    /**
     * Сохраняем данные редактирования в БД, либо устанваливаем сообщения об ошибках
     * @param array &$columns 
     * @param array $tbl - массив данных о таблице
     * @param int $id - идентификатор записи
     * @param array $r - (array)Request
     * @param bool $isNewRec - false если данная запись еще не существует
     */
    public function save(array &$columns, array $tbl, int $id, array &$r, bool $isNewRec) {
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
            $error = $this->saveToDB($tbl, $columns, $id, $isNewRec);
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
     * @param int $id - parent id for record
     * @param int $id2 - id of the table
     * @param Request $request
     */
    
    public function saveTable(array $tbl, int $id, int $id2, object $request) {
        global $yy;
        $r = $request->all();
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $requires_stub = [];
        // get data from db
        $k = ($id == 0 ? 1 : $id); // id записи равен 1, если $id = 0
        $rec = \Alxnv\Nesttab\Models\ArbitraryTableModel::getOne($tbl['name'], $k, false);
        if (is_null($rec)) {
            $isNewRec = true;
            $rec = [];
        } else {
            $isNewRec = false;
        }
        $this->getRecAddObjects($columns, $rec, $requires_stub);
        $this->save($columns, $tbl, $k, $r, $isNewRec); // сохраняем запись с id=$k
        if (!$this->hasErr()) {
            //$request ->session()->flash('saved_successfully', 1);
            session(['saved_successfully' => 1]);
            Session::save();
            \yy::redirectNow($yy->nurl . 'edit/' . $id . '/' . $tbl['id']);
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
            \yy::redirectNow($yy->nurl . 'edit/' . $id . '/' . $tbl['id']);
            exit;
        }
        
    }
}