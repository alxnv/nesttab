<?php
/**
 * basic class for table models (one, ord, tree, list)
 */

namespace Alxnv\Nesttab\Models\table;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BasicTableModel {
    
    protected $adapter;
    /**
     * объект с массивом ошибок с индексом по наименованиям полей формы
     *  в которых ошибочные данные
     * @var type array
     */
    public $err; 

    
    /**
     * object constructor
     * @param object $adapter - adapter object for database
     */
    public function __construct(object $adapter) {
        $this->adapter = $adapter;
        $this->err = new \Alxnv\Nesttab\Models\ErrorModel();
    }

    /**
     * can the name of the field be changed
     * @param string $name
     * @return boolean
     */
    public function canChangeFieldName(string $name) {
        return true;
    }

    /**
     * is this field deletable
     * @param string $fieldName 
     * @return boolean
     */
    public function isDeletableField(string $fieldName) {
        return true;
    }
    /**
     * save table data for all table types (now for 'one' type)
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
        $this->getRecAddObjects($columns, $tbl['name'], $id3, $requires_stub);
        $this->save($columns, $tbl, $id3, $r); // сохраняем запись
        if (!$this->hasErr()) {
            $request ->session()->flash('saved_successfully', 1);
            Session::save();
            \yy::redirectNow($yy->nurl . 'edit/' . $id . '/' . $tbl['id']);
            exit;
        } else {
            //\yy::gotoErrorPage($s);
            $lnk = \yy::getErrorEditSession();
            //session([$lnk => $recs->err->err]);
            $request->session()->flash($lnk, $this->err->err);
            //dd($recs->err->err);
            $lnk2 = \yy::getEditSession();
            //session([$lnk2 => $r]);
            $request->session()->flash($lnk2, $r);
            Session::save();
            \yy::redirectNow($yy->nurl . 'editrec/' . $id . '/' . $tbl['id'] . '/' . $id3);
            exit;
        }
        
        
    }

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
     * @return boolean - if table creation was successful
     */
    public function createTable(array $r, &$message, &$tableId, int $parentTableId, 
            int $idFieldSizeInBytes) {

        global $yy, $db;

        $arr2 = $yy->settings2['table_types'];
        $arr_table_names_short = $yy->settings2['table_names_short'];
	if (!isset($r['tbl_type']) || !isset($r['tbl_name']) || !isset($r['tbl_descr']))  die('Required parameter is not passed');
	$tbl_idx = intval($r['tbl_type']);
	if ($tbl_idx < 0 || $tbl_idx >= count($arr2)) {
            $message = 'Wrong index of table';
            return false;
        }
        if (!isset($arr_table_names_short[$tbl_idx])) {
            $message = 'Not valid table type';
            return false;
        }
        $tbl_name = substr($r['tbl_name'], 0, $yy->db_settings['max_table_name_size']);
	$tbl_descr = trim(substr($r['tbl_descr'], 0, 200));
        $err = '';
	if (($s72 = $db->valid_table_name($tbl_name)) <>'') {
                $err .= chr(13) . $s72;
	}
        if ($tbl_descr == '') {
            $err .= chr(13) . __("The table's description could not be empty");
        }

        $s = '\\Alxnv\\Nesttab\\core\\db\\' . config('nesttab.db_driver') . '\\TableHelper';
        $th = new $s();
        if (($idFieldDef = $th->getIntTypeDef($idFieldSizeInBytes)) === false) {
            $err .= chr(13) . 'No int types of this size';
        }

        if ($err <>'') {
                $message = $err;
                return false;
        }

        if ($parentTableId <> 0) {
            $errorMessage = '';
            // читаем данные о родительской таблице из yy_tables
            $parentTbl = \Alxnv\Nesttab\Models\TablesModel::getOneRetError($parentTableId, $errorMessage);
            if (is_null($parentTbl)) { // если не найдена родительская таблица
                                       //  в yy_tables
                $message = $errorMessage;
                return false;
            }
        } else {
            $parentTbl = null;
        }
        
        $tbl_name2 = $db->escape($tbl_name);
        /*
         * создаем саму таблицу
         */
        //$s = "\\Alxnv\\Nesttab\\core\\db\\" . config('nesttab.db_driver') . "\\TableHelper";
        //$th = new $s();

        $message = '';
        if (!$this->adapter->createTableDbCommands($tbl_name, $message, $idFieldDef, $parentTableId, $parentTbl)) {
            return false;
        }

        if ($parentTableId == 0) {
            $topId = 0; // временно присваиваем 0, после записи 
            // присвоим идентификатор этой созданной таблицы
        } else {
            $topId = $parentTbl['lvl1_tbl_id'];
        }
        
        // Записываем данные таблицы в yy_tables
        $s3 = $db->escape($tbl_descr);
        $arr2 = ['name' => $tbl_name,
            'descr' => $tbl_descr,
            'parent_tbl_id' => $parentTableId,
            'lvl1_tbl_id' => $topId,
            'id_bytes' => $idFieldSizeInBytes,
            'table_type' => $arr_table_names_short[$tbl_idx]];
        if (!$db->insert('yy_tables', $arr2)) {
            $message = $db->errorMessage;
            return false;
        }
        $tableId = $db->handle->lastInsertId();
        
        // если получилась таблица верхнего уровня, то присваиваем lvl1_tbl_id
        //   равный id
        if ($parentTableId == 0) {
            $db->qdirectNoErrorMessage("update yy_tables set lvl1_tbl_id = id "
                    . " where id = $1", [$tableId]);
        }

        // Если имя таблицы создано по шаблону, то проставляем номер таблицы в yy_settings 
        //   как следующий номер для автонумерации
        $arr_names = $yy->settings2['table_names'];
        $def_db_prefix = config('nesttab.db_prefix');
        $s = $def_db_prefix . $arr_names[$tbl_idx];
        //var_dump($s);
        if (substr($tbl_name, 0, strlen($s)) == $s) {
            $b = false;
            if (strlen($s) == strlen($tbl_name)) {
                $n = 1;
                $b = true;
            } else {
                $s2 = substr($tbl_name, strlen($s));
                if (preg_match('/^\d+$/', $s2)) {
                    $n = intval($s2);
                    $b=true;
                }
            }
            
            if ($b) {
                $field = $arr_names[$tbl_idx] . '_counter';
                $db->qdirectNoErrorMessage("lock tables yy_settings write");
                $obj = $db->qobj("select $field as v from yy_settings");
                //var_dump($arr3);
                //exit;
                if ($n > $obj->v) {
                    $db->qdirectNoErrorMessage("update yy_settings set $field = $n");
                }
                $db->qdirectNoErrorMessage("unlock tables");
            }
        }       
        
        $message = __('The table was made');
        return true;
        
    }
    /**
     * Устанавливаем ошибку для указанного поля
     * @param string $field - поле, для которого устанавливается ошибка
     * @param string $errorString - сообщение об ошибке
     */
    public function setErr(string $field, string $errorString) {
        $this->err->setErr($field, $errorString);
    }
    
    /**
     * Проверяем, есть ли ошибка в данных
     * @return boolean
     */
    public function hasErr() {
        return $this->err->hasErr();
    }


    /**
     * get old values for image and file field types
     * @param array $columns
     * @param array $tbl - table data
     * @param int $id - id of the table record
     */
    public function getImageFileValues(array &$columns, array $tbl, int $id) {
        global $db;
        $arr = [];
        $ar2 = [];
        for ($i = 0; $i < count($columns); $i++)  {
            if (in_array($columns[$i]['name_field'], ['image', 'file'])) {
                $arr[] = $i;
                $ar2[] = $columns[$i]['name'];
            }
        }
        
        if (count($arr) > 0) {
            // if there are image of file type fields
            $ar3 = $db->massNameEscape($ar2);
            $s = join(', ', $ar3);
            if ($id <> 0) {
                $rec = $db->q("select " . $s . " from " . $tbl['name'] . " where id = $1", [$id]);
                for ($i = 0; $i < count($ar2); $i++) {
                    $columns[$arr[$i]]['value_old'] = $rec[$i];
                }
            }
        }
    }
    /**
     *  установить поле value_old для всех полей
     *   кроме image, file
     * @param array &$columns - array of fields with values
     */
    public function setOldValues(array &$columns) {
        for ($i = 0; $i < count($columns); $i++) {
            if (!in_array($columns[$i]['name_field'], ['image, file'])
                    && isset($columns[$i]['value'])) {
                $columns[$i]['value_old'] = $columns[$i]['value'];
            }
        }
    }
    
    
    /**
     * Записываем и обрабатываем загруженные документы и изображения
     * @param array $columns - массив колонок
     * @param array $r - (array)Request
     */
    public function postProcess1(array &$columns, array &$r) {
        $isNewRec = false; // todo: change it to appropriate value
        for ($i = 0; $i < count($columns); $i++) {
            // не обрабатываем поля типа image, file
            if (in_array($columns[$i]['name_field'], ['image', 'file'])) continue;
            $toContinue = true;
            if (function_exists('\callbacks\onPostProcess'))
                \callbacks\onPostProcess($this, $columns, $i, $r, $columns[$i]['name'], $isNewRec, $toContinue);

            if ($toContinue) $columns[$i]['obj']->postProcess($this, $columns, $i, $r);
        }
    }

    /**
     * Записываем и обрабатываем загруженные документы и изображения
     * @param array $columns - массив колонок
     * @param array $r - (array)Request
     */
    public function postProcess(array &$columns, array &$r) {
        $isNewRec = false; // todo: change it to appropriate value
        for ($i = 0; $i < count($columns); $i++) {
            // обрабатываем только поля типа image, file
            if (!in_array($columns[$i]['name_field'], ['image', 'file'])) continue;
            $toContinue = true;
            if (function_exists('\callbacks\onPostProcess'))
                \callbacks\onPostProcess($this, $columns, $i, $r, $columns[$i]['name'], $isNewRec, $toContinue);

            if ($toContinue) $columns[$i]['obj']->postProcess($this, $columns, $i, $r);
        }
    }
    

    /**
     * 
     * @param bool $wasSaved - was the record saved or not
     * @param array $columns
     */
    public function afterDataSaved(bool $wasSaved, array &$columns) {
        $isNewRec = false; // todo: change it to appropriate value
        if (function_exists('\callbacks\onAfterDataSaved'))
            \callbacks\onAfterDataSaved($wasSaved, $isNewRec, $columns);
        
    }
    
    /**
     * Записываем данные файлов и изображений в БД
     * @param array $tbl - массив с данными о таблице
     * @param array $columns - массив с данными полей таблицы и их значениями
     */
    public function saveToDBFiles(array $tbl, array $columns, int $id) {
        global $db;
        $arr = [];
        $arind = [];
        // определяем, какие данные записывать (поля типа image и file)
        for ($i = 0; $i < count($columns); $i++) {
            // $columns[$i]['name_field'] - тип поля
            $value = $columns[$i]['value'];
            if (isset($value) && ($value <>'')
                    && in_array($columns[$i]['name_field'], ['image', 'file'])) {
                if ($value == '$') $value = ''; // "delete" checkbox was checked
                $arr[$columns[$i]['name']] = $value;
                $arind[$columns[$i]['name']] = $i;
            }
        }
        
        //--- удаляем файлы, которые были ранее указаны в БД
        //$this->deletePrevious($columns, $tbl['name'], $arr, $arind, $id);
        // записываем значения
        if (count($arr) > 0) {
            $db->update($tbl['name'], $arr, "where id=" . $id);
            return ($db->errorCode == 0);
        }
        return true;
    }

    /**
     * !!! not used Удалить предыдущие версии файлов image, file
     * @global type $db
     * @param array $columns - $columns array
     * @param string $tbl - имя таблицы
     * @param array $arr - массив имен полей типа image, file которые поменялись
     * @param array $arind - массив индексов в $columns имен полей из $arr
     * @param int $id - id в таблице
     */
    /**
    public function deletePrevious(array $columns, string $tbl, array $arr, array $arind, int $id) {
        global $db;
        if (count($arr) == 0) return;
        $ar2 = array_keys($arr);
        $ar3 = [];
        for ($i = 0; $i < count($ar2); $i++) {
            $ar3[] = $db->nameEscape($ar2[$i]) . ' as v' . $i;
        }
        $s = join(', ', $ar3);
        $tbl2 = $db->nameEscape($tbl);
        
        $ar4 = $db->q("select $s from $tbl2 where id = $id");
        for ($i = 0; $i < count($ar2); $i++) {
            $value = $ar4['v' . $i];
            if ($value <> '') {
                //--- удаляем предыдущие файлы
                //$columns[$arind[$ar2[$i]]]['obj']->deleteFiles($value);
            }
        }
        
    }*/
    
    /**
     * Проставить в $recs[$i]['value'] соответствующие данные из $r 
     *   ((array(Request) с предыдущими
     *   данными из post)
     *  в value_old сохраняем значение из бд
     * @param array $recs - массив с данными о полях, определенных в БД
     * @param array $r - бывший post для редактирования с ошибкой
     */
    public function setValues(array $recs, array $r) {
        for ($i = 0; $i < count($recs); $i++) {
            /*$recs[$i]['value_old'] = (isset($recs[$i]['value']) 
                        ? $recs[$i]['value'] : '');*/
            if ($recs[$i]['name_field'] == 'bool') {
                $recs[$i]['value'] = (isset($r[$recs[$i]['name']]) ? 1 : 0);
            } else {
                if (isset($r[$recs[$i]['name']])) {
                    $recs[$i]['value'] = $r[$recs[$i]['name']];
                } else {
                    $recs[$i]['value'] = '';
                }
            }
        }
        return $recs;
    }
    
    /**
     * Получаем запись таблицы, добавляя к ней соответствующие объекты для 
     *   различных типов полей
     * @param array $columns - массив структуры полей для данной таблицы
     * @param string $table - имя таблицы
     * @param int $id - идентификатор записи таблицы
     * @param array $requires - сюда заносятся ключи 'need_html_editor', 'need_filepond'
     *   этой функцией, если они нужны
     */
    public function getRecAddObjects(array &$columns, string $table, int $id, array &$requires = []) {
        global $db;
        $rec = $db->q("select * from $table where id=$1", [$id]);
        //if (is_null($rec)) \yy::gotoErrorPage('Record not found');
        for ($i = 0; $i < count($columns); $i++) {
            if ($columns[$i]['name_field'] == 'html') {
                $requires['need_html_editor'] = 1;
            }
            if ($columns[$i]['name_field'] == 'datetime') {
                $requires['need_datetimepicker'] = 1;
            }
            if (in_array($columns[$i]['name_field'], ['image', 'file'])) {
                $requires['need_filepond'] = 1;
            }
            $columns[$i]['obj'] = \Alxnv\Nesttab\Models\Factory::createFieldModel($columns[$i]['field_type'], $columns[$i]['name_field']);
            if (isset($rec[$columns[$i]['name']])) {
                if (in_array($columns[$i]['name_field'], ['image', 'file'])) {
                    $columns[$i]['value_old'] = $rec[$columns[$i]['name']];
                } else {
                    $columns[$i]['value'] = $rec[$columns[$i]['name']];
                    $columns[$i]['value_old'] = $rec[$columns[$i]['name']];
                }
            } else {
                $columns[$i]['value'] = null;
            }
            $columns[$i]['obj']->convertDataForInput($columns, $i); // если нужно, то
               // преобразовываем данные из БД в данном поле
        }
        return $columns;
    }
}