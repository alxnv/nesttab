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
        $this->setErr('', 'fdsafd');
    }
    /**
     * Получаем запись таблицы, добавляя к ней соответствующие объекты для 
     *   различных типов полей
     * @param array $columns - массив структуры полей для данной таблицы
     * @param string $table - имя таблицы
     * @param int $id - идентификатор записи таблицы
     */
    public static function getRecAddObjects(array $columns, string $table, int $id) {
        global $db;
        $rec = $db->q("select * from $table where id=$1", [$id]);
        if (is_null($rec)) \yy::gotoErrorPage('Record not found');
        for ($i = 0; $i < count($columns); $i++) {
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