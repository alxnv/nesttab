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
     * Сохраняем данные редактирования в БД, либо устанваливаем сообщения об ошибках
     * @param array $tbl - массив данных о таблице
     * @param int $id - идентификатор записи
     * @param array $r - (array)Request
     */
    public function save(array $tbl, int $id, array $r) {
        //$this->setErr('', 'fdsafd');
        $columns = \Alxnv\Nesttab\Models\ColumnsModel::getTableColumnsWithNames($tbl['id']);
        for ($i = 0; $i < count($columns); $i++)  {
            $s2 = '\\Alxnv\\Nesttab\\Models\\field_struct\\' . config('nesttab.db_driver') . '\\'
                    . ucfirst($columns[$i]['name_field']) .'Model';
            $columns[$i]['obj'] = new $s2();
            $columns[$i]['parameters'] = (array)json_decode($columns[$i]['parameters']);
            // значение для поля типа bool не будет в post массиве если он unchecked
            if ($columns[$i]['name_field'] == 'bool') {
                $columns[$i]['value'] = $columns[$i]['obj']
                        ->validate(isset($r[$columns[$i]['name']]) ? 1 : 0, $this, $columns[$i]['name'], $columns, $i);
            } else {
                //if (isset($r[$columns[$i]['name']])) {
                    // устанавливает сообщения об ошибках для $this
                $columns[$i]['value'] = $columns[$i]['obj']
                        ->validate(isset($r[$columns[$i]['name']]) ?
                                $r[$columns[$i]['name']] : '', $this,
                                $columns[$i]['name'], $columns, $i);
                
            }
        }

        if (!$this->hasErr()) {
            // ошибок нет. записываем данные в БД
            $this->postProcess($columns); // записываем загруженные документы и изображения
            $this->saveToDB($tbl, $columns, $id);
        }
    }
    
    /**
     * Записываем и обрабатываем загруженные документы и изображения
     * @param array $columns - массив колонок
     */
    public function postProcess(array $columns) {
        for ($i = 0; $i < count($columns); $i++) {
            $columns[$i]['obj']->postProcess($this, $columns, $i);
        }
    }
    
    /**
     * Записываем данные в БД
     * @param array $tbl - массив с данными о таблице
     * @param array $columns - массив с данными полей таблицы и их значениями
     */
    public function saveToDB(array $tbl, array $columns, int $id) {
        global $db;
        $arr = [];
        // определяем, какие данные записывать
        for ($i = 0; $i < count($columns); $i++) {
            if (isset($columns[$i]['value'])) {
                $arr[$columns[$i]['name']] = $columns[$i]['value'];
            }
        }
        
        if (count($arr) > 0) {
            $db->update($tbl['name'], $arr, "where id=" . $id);
        }
        
    }
    
    
    /**
     * Проставить в $recs соответствующие данные из $r ((array(Request) с предыдущими
     *   данными из post)
     * @param array $recs - массив с данными о полях, определенных в БД
     * @param array $r - бывший post для редактирования с ошибкой
     */
    public static function setValues(array $recs, array $r) {
        for ($i = 0; $i < count($recs); $i++) {
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
            if (in_array($columns[$i]['name_field'], ['image', 'file'])) {
                $requires['need_filepond'] = 1;
            }
            $s2 = '\\Alxnv\\Nesttab\\Models\\field_struct\\' . config('nesttab.db_driver') . '\\'
                    . ucfirst($columns[$i]['name_field']) .'Model';
            $columns[$i]['obj'] = new $s2();
            if (isset($rec[$columns[$i]['name']])) {
                $columns[$i]['value'] = $rec[$columns[$i]['name']];
            } else {
                $columns[$i]['value'] = null;
            }
        }
        return $columns;
    }
}