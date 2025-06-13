<?php

/**
 * Model for list table type (list) - ordered list of records
 */

namespace Alxnv\Nesttab\Models\table;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class ListTableModel extends BasicTableModel {

    /**
     * Save settings of particular table
     *   this BasicTableModel method called for L, D
     * @param array $tbl - table info data
     * @param int $id - id of the table
     */
    public function saveSettings(array $tbl, int $id, object $request) {
        global $yy;
        $r = $request->all();
        if (isset($r['selectedItem'])) {
            $sI = intval($r['selectedItem']);
        } else {
            $sI = -1;
        }
        $arr =  (isset($r['flds']) ? $r['flds'] : []); 
        $this->adapter->saveTableRefs($arr, $id, $sI);
        $request ->session()->flash('saved_successfully', 1);
        Session::save();
        \yy::redirectNow($yy->nurl . 'struct-table-show-settings/' . $id);
        //exit;
    }
    /**
     * Show settings of particular table
     *   this BasicTableModel method called for O, C
     * @param array $tbl - table info data
     * @param int $id - id of the table
     */
    public function showSettings(array $tbl, int $id) {
        $recCnt = \Alxnv\Nesttab\Models\ArbitraryTableModel::getCount($tbl['name']);
        return view('nesttab::table-settings.list', ['tbl' => $tbl, 'id' => $id,
            'recCnt' => $recCnt,
            'fieldModel' => $this]);
    }
    /**
     *  получаем встроенные типы полей для данного типа таблицы
     */
    public function builtInFieldsForView() {
        return $this->fieldsForView([-1, -2]);
    }
    
    /**
     * Перемещение записи (изменение ordr) для L
     * @param int $tableId - table id
     * @param int $recId - record id
     * @param int $newOrdr - new ordr value
     * @param int $page - page to return to (maybe)
     * @param int $parentId - id of the parent record in the parent table
     *  (0 if it is a top level table)
     */
    public function moveRec(int $tableId, int $recId, int $newOrdr, int $page,
            int $parentId) {
        global $yy;
        if ($recId == 0) {
            die('can not move record with id 0');
        }
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($tableId);
        $rec = \Alxnv\Nesttab\Models\ArbitraryTableModel::getOne($tbl['name'], $recId);
        //$parentId = (isset($rec['parent_id']) ? $rec['parent_id'] : 0);
        $aModel = new \Alxnv\Nesttab\Models\ArbitraryTableModel();
        $aModel->adapter->move($tbl['name'], $rec, $newOrdr, $parentId);
        $page2 = $this->getMovePage($page);
        \yy::redirectNow($yy->nurl . 'edit/' . $parentId . '/' . $tbl['id'] 
                    . '?page=' . $page2);
        exit;
    }
    
    
    /**
     * get the page to return for 'move' action
     * @param int $page
     * @return int
     */
    public function getMovePage(int $page) {
        return 1;
    }
    /**
     * can the name of the field be changed?
     * @param string $name
     * @return boolean
     */
    public function canChangeFieldName(string $name) {
        switch ($name) {
            case 'name':
                return false;
        }
        return true;
    }
    /**
     * is this field deletable
     * @param string $fieldName 
     * @return boolean
     */
    public function isDeletableField(string $fieldName) {
        switch ($fieldName) {
            case 'name':
                return false;
        }
        return true;
    }
    // create table structure, step 2, write to the tables
    // пытаемся создать таблицу указанного типа и с указанным именем
    /**
     * @param array $r - request
     * @param type $message - message here returned (ok or error)
     * @param type $tableId - id of created table in yy_tables (no input value,
     *   returns it)
     * @param int $parentTableId - id of parent table for this table, or
     *    0, if its a top level table
     * @param array $options : key 'toAddRec' - значит добавлять запись после создания таблицы
     *   (только для таблицы типа 'one')
     * @return boolean - if table creation was successful
     */
    public function createTable(array $r, &$message, &$tableId, int $parentTableId, 
            int $idFieldSizeInBytes, array $parent_tbl, array $options = []) {

        global $yy, $db;
        
        $tableId = 0;
        $b = parent::createTable($r, $message, $tableId, $parentTableId, 
               $idFieldSizeInBytes, $parent_tbl);
        if ($b) {
            // таблица создана, добавляем поле 'name' типа 'string'
            $dummy = 0;
            $strModel = \Alxnv\Nesttab\Models\Factory::createFieldModel($dummy, 'str');
            $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($tableId);
            $fld =  (new \Alxnv\Nesttab\Models\ColTypesModel())->getByName('str');
            $old_values = [];
            $r = ['name' => 'name', 'descr' => __('Name '),
                'req' => 1 /* не пустая строка */];
            $strModel->save($tbl, $fld, $r, $old_values);
            if ($strModel->hasErr()) {
                $message = $strModel->err->getAll();
                return false;
            } else {
            // добавляем индексы с полем 'name'
                if ($parentTableId == 0) {
                    $arr_commands = [
                            "alter table " . $tbl['name'] . " add key(name(40))",
                            ];
                } else {
                    $arr_commands = [
                            "alter table " . $tbl['name'] . " add key(parent_id,name(40))",
                            ];
                }    
                foreach ($arr_commands as $command) {
                        $sth = $db->qdirectNoErrorMessage($command);
                        if (!$sth) { # table already exists 
                            $message = $db->errorMessage;
                            return false;
                        }

                }
            }
        }
        return $b;

    }
    /**
     * Редактирование одной записи таблицы типа List
     * @param array $tbl - данные таблицы
     * @param array $r - Request в виде массива
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     * @param int $id3 - id of the record (0 for new record)
     */
    public function editTableRec(array $tbl, array $r, int $id, int $id2, int $id3) {
        // получаем строку с id=1 для one rec table (это единственная строка там)
        global $yy;
        $page = (isset($r['page']) ? intval($r['page']) : 1); // page to return to
        $columnsModel = new \Alxnv\Nesttab\Models\ColumnsModel();
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        // получаем имена полей участвующих в отображении всех полей типа select данной таблицы
        $selectFldNames = \Alxnv\Nesttab\Models\ColumnsModel::getSelectFldNames($tbl['id'], $columns);
        $requires = [];
        $parent_table_id = $tbl['p_id'];
        $parent_table_rec = [];
        if ($parent_table_id == 0) {
            // top level table
        } else {
            // nested table
        }
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
            // проставить значения полей из сессии (бывший post) в $recs
            //$recs = \Alxnv\Nesttab\Models\TableRecsModel::setValues($recs, $r);
        }
        if ($id3 == 0) {
            $rec = [];
            $recs = $this->getDefaults($columns, $requires); // получаем значения по умолчанию для новой записи
        } else {
            $rec = \Alxnv\Nesttab\Models\ArbitraryTableModel::getOne($tbl['name'], $id3);
            /**
             * Добавляем к $columns данные из БД $rec 
             *  также добавляем соответствующие объекты типов полей к полям $columns,
             *  преобразуем данные в формат для отображения на странице редактирования
             * @return array - измененный $columns
             */
            $recs = $this->getRecAddObjects($columns, $rec, $requires, $r);
        }
        $errorMsg = '';
        // получаем текущие значения всех полей select данной записи
        $selectsInitialValues = $columnsModel->getSelectsInitialValues($rec, $recs, $selectFldNames, $errorMsg);
        // На какую страницу возвращаться после редактирования записи, или
        //   при нажатии "Назад" на странице редактирования
        if ($id3 == 0) {
            // new record
            $returnToPage = $this->getReturnToPage($tbl, $parent_table_id, $id3, $recs, $rec);  
        } else {
            $returnToPage = $page;
        }
        if (Session::has($lnk2)) {
            // проставить значения полей из сессии (бывший post) в $recs
            $recs = $this->setValues($recs, $r);
            Session::forget($lnk2);
        }
        return view('nesttab::edit-table.list_rec', ['tbl' => $tbl, 'recs' => $recs,
                'r' => $r, 'requires' => $requires, 'table_id' => $id2, 'rec_id' => $rec_id,
                'parent_id' => $id, 'returnToPage' => $returnToPage, 'rec' => $rec,
                'extra' => ['selectsInitialValues' => $selectsInitialValues],
                'errorMsg' => $errorMsg, 'id2' => $id2, 'id3' => $id3, 'id' => $id,
                'parent_table_id' => $parent_table_id, 'parent_table_rec' => $parent_table_rec]);
        
    }
    
    
    /**
     * На какую страницу возвращаться после редактирования записи, или
     *   при нажатии "Назад" на странице редактирования
     *    подразумевается, что список сортируется по ordr
     * 
     * @param array $tbl - данные о таблице
     * @param int $parentTableId - id родительской таблицы для данной,
     *    либо 0 если это таблица верхнего уровня
     * @param int $idRec - идентификатор редактируемой записи, либо 0 если новая запись
     * @param array $recs - массив полей редактируемой записи
     * @param array $rec - запись БД с данными
     * @return int - номер страницы (начиная с 1)
     */
    public function getReturnToPage(array $tbl, int $parentTableId, int $idRec, array $recs, array $rec) {
        global $yy, $db;
        // !todo: Для 'id asc' сделать то же определение страницы что и для 'ord asc'
        if ($parentTableId == 0) {
            if ($idRec == 0) {
                // новая запись
                // подсчитываем исходя из текущего количества записей + 1
                $tableName = $db->nameEscape($tbl['name']);
                $res = $db->qobj("select count(*) as cnt from $tableName");
                if (is_null($res)) {
                    return 1; // ошибка, возвращаем любое значение
                }
                $page = 1 + (int)floor(($res->cnt + 1) / $yy->settings2['recs_per_page']);
                return $page;
            } else {
                $page = 1 + (int)floor(($rec['ordr'] - 1)
                        / $yy->settings2['recs_per_page']);
                return $page;
            }
        } else {
            // todo
            // now temporary set value
            return 1;
        }
    }  

    /**
     * Редактирование таблицы типа List - точка входа
     * @param array $tbl - данные таблицы
     * @param array $r - Request в виде массива
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     */
    public function editTable(array $tbl, array $r, int $id, int $id2) {
        global $yy;
        //$columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $requires = [];
        $parent_table_id = $tbl['p_id'];
        $errorMsg = '';
        $parent_table_rec = [];
        if ($parent_table_id == 0) {
            // top level table
            try {
                $recs = DB::table($tbl['name'])->orderBy('ordr')->paginate($yy->settings2['recs_per_page']);
            } catch (\Exception $e) {
                $errorMsg = sprintf(__('Table %s does not exist'), $tbl['name']);
                \yy::gotoErrorPagePage($errorMsg);
            }
        } else {
            // nested table
            try {
                $recs = DB::table($tbl['name'])->where('parent_id', '=', $id)->orderBy('ordr')->paginate($yy->settings2['recs_per_page']);
            } catch (\Exception $e) {
                $errorMsg = sprintf(__('Table %s does not exist'), $tbl['name']);
                \yy::gotoErrorPagePage($errorMsg);
            }
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
                'errorMsg' => $errorMsg,
                'parent_table_id' => $parent_table_id, 'parent_table_rec' => $parent_table_rec]);
        
    }
    /**
     * save table data for all table types
     *   it is called from saving main table data page
     * @global \Alxnv\Nesttab\Http\Controllers\type $db
     * @global \Alxnv\Nesttab\Http\Controllers\type $yy
     * @param array $tbl - table data
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     * @param int $id3 - id of the record (0 for new record)
     * @param Request $request - request data
     */
    public function saveTableRec(array $tbl, int $id, int $id2, int $id3, object $request) {
        global $yy;
        $r = $request->all();
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $requires_stub = [];
        if ($id3 == 0) {
            $rec = [];
        } else {
            $rec = \Alxnv\Nesttab\Models\ArbitraryTableModel::getOne($tbl['name'], $id3);
        }
        $this->getRecAddObjects($columns, $rec, $requires_stub);
        $this->save($columns, $tbl, $id3, $id, $r); // сохраняем запись
        // на какую страницу списка записей возвращаемся
        $retPage = (isset($r['return_to_page5871']) 
                ? intval($r['return_to_page5871']) : 1);
        if (!$this->hasErr()) {
            session(['saved_successfully' => 1]);
            //$request ->session()->flash('saved_successfully', 1);
            Session::save();
            \yy::redirectNow($yy->nurl . 'edit/' . $id . '/' . $tbl['id'] 
                    . '?page=' . $retPage);
            exit;
        } else {
            //\yy::gotoErrorPage($s);
            $lnk = \yy::getErrorEditSession();

            // не делать session()->flash(), так как при обращении к загрузке изображения флеш удаляется
            session([$lnk => $this->err->err]);

            //$request->session()->flash($lnk, $this->err->err);
            //dd($recs->err->err);
            $lnk2 = \yy::getEditSession();
            session([$lnk2 => $r]);
            //$request->session()->flash($lnk2, $r);
            Session::save();
            \yy::redirectNow($yy->nurl . 'editrec/' . $id . '/' . $tbl['id'] . '/' . $id3);
            exit;
        }
        
        
    }
    
    /**
     * Сохраняем данные редактирования в БД, либо устанавливаем сообщения об ошибках
     * @param array &$columns 
     * @param array $tbl - массив данных о таблице
     * @param int $id - идентификатор записи
     * @param int $parentId - идентификатор родительской записи
     * @param array $r - (array)Request
     */
    public function save(array &$columns, array $tbl, int $id, int $parentId, array &$r) {
        //$this->setErr('', 'fdsafd');
        global $yy, $db;
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
            $error = $this->saveToDB2($tbl, $columns, $id, $parentId);
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
     * Записываем данные в БД
     * @param array $tbl - массив с данными о таблице
     * @param array $columns - массив с данными полей таблицы и их значениями
     * @param int &$id - id записи, или при вставке новой записи
     *     сюда возвращается id новой записи
     * @param bool $isNewRec - признак новой записи (также таким признаком является $id == 0)
     * @return sting - '', если не было ошибки, иначе сообщение об ошибке
     */
    public function saveToDB2(array $tbl, array $columns, int &$id, int $parentId) {
        global $db;
        $arr = [];
        // определяем, какие данные записывать (кроме полей типа image и file
        for ($i = 0; $i < count($columns); $i++) {
            // $columns[$i]['name_field'] - тип поля
            if (isset($columns[$i]['value']) 
                    && !in_array($columns[$i]['name_field'], ['image', 'file'])) {
                // if set $columns[$i]['value_for_db'], save it, or value
                $arr[$columns[$i]['name']] = $columns[$i]['value'];
            }
            if (isset($columns[$i]['value_for_db']) 
                    && !in_array($columns[$i]['name_field'], ['image', 'file'])) {
                // if set $columns[$i]['value_for_db'], save it, or value
                $arr[$columns[$i]['name']] = $columns[$i]['value_for_db'];
            }
        }
        
        if (count($arr) > 0) {
            if ($id == 0) {
                // new record
                $parentTableRec = []; // todo: determine this record
                $id5 = 0;
                if ($tbl['p_id'] <> 0) { // таблица ненулевого уровня
                    $arr['parent_id'] = $parentId;
                    $arr['id'] = $id;
                }
                $error = $this->adapter->insert($tbl['name'], $arr, $parentTableRec, $id5, $parentId);
                if ($error == '') {
                    $id = $id5;
                }
                return $error;
            } else {
                $error = $db->update($tbl['name'], $arr, "where id=" . $id);
                return $error;
            }
        }
        return true;
    }
    /**
     * try to delete a table record
     * @global \Alxnv\Nesttab\Http\Controllers\type $db
     * @global \Alxnv\Nesttab\Http\Controllers\type $yy
     * @param array $tbl - table data
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     * @param int $id3 - id of the record (0 for new record)
     * @param Request $request - request data
     * @param string $type
     */
    public function deleteTableRec(array $tbl, int $id, int $id2, int $id3, object $request, string $type) {
        global $yy;
        $this->adapter->deleteTableRec($tbl, $id, $id2, $id3, $request);
        \yy::redirectNow($yy->nurl . 'edit/' . $id . '/' . $id2 . '?page=1');
        exit;
    }    
}