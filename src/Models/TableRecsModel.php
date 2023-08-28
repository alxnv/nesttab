<?php
/**
 * модель, работающая с записями таблиц
 */

namespace Alxnv\Nesttab\Models;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

class TableRecsModel {
    /**
     * объект с массивом ошибок с индексом по наименованиям полей формы
     *  в которых ошибочные данные
     * @var type array
     */
    public $err; 

    public function __construct() {
        $this->err = new \Alxnv\Nesttab\Models\ErrorModel();
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
            $rec = $db->q("select " . $s . " from " . $tbl['name'] . " where id = $1", [$id]);
            for ($i = 0; $i < count($ar2); $i++) {
                $columns[$arr[$i]]['value_old'] = $rec[$i];
            }
        }
    }
    /**
     * Сохраняем данные редактирования в БД, либо устанваливаем сообщения об ошибках
     * @param array $tbl - массив данных о таблице
     * @param int $id - идентификатор записи
     * @param array $r - (array)Request
     */
    public function save(array $tbl, int $id, array &$r) {
        //$this->setErr('', 'fdsafd');
        global $yy;
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        $yy->loadPhpScript(app_path() . '/Models/nesttab/tables/' 
            . ucfirst($tbl['name']) . '.php');
        // get old values for image and file field types
        $this->getImageFileValues($columns, $tbl, $id);
        for ($i = 0; $i < count($columns); $i++)  {
            $columns[$i]['obj'] = \Alxnv\Nesttab\Models\Factory::createFieldModel($columns[$i]['field_type'], $columns[$i]['name_field']);
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
            if (function_exists('\callbacks\onValidate'))
                \callbacks\onValidate($value, $columns, $i, $r, $this, $columns[$i]['name'], $isNewRec, $toContinue);

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
            $db->update($tbl['name'], $arr, "where id=" . $id);
            return ($db->errorCode == 0);
        }
        return true;
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
    public static function setValues(array $recs, array $r) {
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
    public static function getRecAddObjects(array $columns, string $table, int $id, array &$requires = []) {
        global $db;
        $rec = $db->q("select * from $table where id=$1", [$id]);
        if (is_null($rec)) \yy::gotoErrorPage('Record not found');
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