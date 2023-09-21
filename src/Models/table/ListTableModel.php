<?php

/**
 * Model for list table type (list) - ordered list of records
 */

namespace Alxnv\Nesttab\Models\table;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class ListTableModel extends BasicTableModel {
    // create table structure, step 2, write to the tables
    // пытаемся создать таблицу указанного типа и с указанным именем
    /**
     * @param array $r - request
     * @param type $message - message here returned (ok or error)
     * @param type $tableId - id of created table in yy_tables
     * @return boolean - if table creation was successful
     */
    public function createTable(array $r, &$message, &$tableId) {

        global $yy, $db;
        
        $tableId = 0;
        $b = parent::createTable($r, $message, $tableId);
        if ($b) {
            // таблица создана, добавляем поле 'name' типа 'string'
            $dummy = 0;
            $strModel = \Alxnv\Nesttab\Models\Factory::createFieldModel($dummy, 'str');
            
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
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $requires = [];
        $parent_table_id = $tbl['parent_tbl_id'];
        if ($parent_table_id == 0) {
            // top level table
            $parent_table_rec = [];
        } else {
            // nested table
        }
        //$tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id2);
        $rec_id = $id3; // record 'id' field value !!! todo: replace
        //$recs = \Alxnv\Nesttab\Models\TableRecsModel::getRecAddObjects($columns, $tbl['name'], 1, $requires);
        $recs = $this->getRecAddObjects($columns, $tbl['name'], $id3, $requires);
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
        return view('nesttab::edit-table.list_rec', ['tbl' => $tbl, 'recs' => $recs,
                'r' => $r, 'requires' => $requires, 'table_id' => $id2, 'rec_id' => $rec_id,
                'parent_id' => $id,
                'parent_table_id' => $parent_table_id, 'parent_table_rec' => $parent_table_rec]);
        
    }
    /**
     * Редактирование таблицы типа List - точка входа
     * @param array $tbl - данные таблицы
     * @param array $r - Request в виде массива
     * @param int $id - id of the record of the parent table (0 for main level table)
     * @param int $id2 -  the id of the table in yy_tables
     */
    public function editTable(array $tbl, array $r, int $id, int $id2) {
        // получаем строку с id=1 для one rec table (это единственная строка там)
        global $yy;
        //$columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $requires = [];
        $parent_table_id = $tbl['parent_tbl_id'];
        if ($parent_table_id == 0) {
            // top level table
            $parent_table_rec = [];
            $recs = DB::table($tbl['name'])->paginate($yy->settings2['recs_per_page']);
        } else {
            // nested table
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
                'parent_table_id' => $parent_table_id, 'parent_table_rec' => $parent_table_rec]);
        
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
            if (function_exists('\callbacks\onValidate'))
                \callbacks\onValidate($value, $value_old, $columns, $i, $r, $this, $columns[$i]['name'], $isNewRec, $toContinue, $arFI);

            $columns[$i]['value'] = $value;
            
            if ($toContinue) {
                $columns[$i]['value'] = $columns[$i]['obj']
                    ->validate($value, $this,
                            $columns[$i]['name'], $columns, $i, $r);
            }
                
        }

        if (!$this->hasErr()) {
            // ошибок нет. записываем данные в БД
            $this->postProcess1($columns, $r); // постпроцессинг для всех типов данных
               // кроме image, file
            $b = $this->saveToDB($tbl, $columns, $id);
            if ($b) {
                // если основные поля сохранены без ошибок
                $this->postProcess($columns, $r); // записываем загруженные документы и изображения
                // ошибок нет. записываем данные в БД
                $this->saveToDBFiles($tbl, $columns, $id);
            } else {
                // todo - в случае если было нарушение (дублирование) ключа, здесь обрабатываем
                //  и возвращаем ошибку
            }
            $this->afterDataSaved($b, $columns); // вызываем коллбэк после сохранения
              // данных или ошибки сохранения
        }
    }
    /**
     * Записываем данные в БД
     * @param array $tbl - массив с данными о таблице
     * @param array $columns - массив с данными полей таблицы и их значениями
     */
    public function saveToDB(array $tbl, array $columns, int &$id) {
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
                $res = $this->adapter->insert($tbl['name'], $arr, $parentTableRec, $id5);
                if ($res) {
                    $id = $id5;
                }
                return $res;
            } else {
                $db->update($tbl['name'], $arr, "where id=" . $id);
                return ($db->errorCode == 0);
            }
        }
        return true;
    }

    

}