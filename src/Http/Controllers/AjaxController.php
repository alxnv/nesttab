<?php

namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;

class AjaxController extends BasicController
{
    /**
     * вернуть все поля таблицы $table_id, которые могут быть использованы в 
     *   поле типа select
     */
    public function selectFldGetFieldsForTable(int $table_id)
    {
        global $yy, $db;
        $arr = \Alxnv\Nesttab\Models\ColumnsModel::getSelectColumns($table_id);
        $ar2 = [];
        foreach ($arr as $key => $value) {
            $ar2[] = ['id' => $key, 'name' => $value];
        }
        return response()->json(['arr' => $ar2]);
        
    }
    
    /**
     * Получить даные для содержимого тэга <select> поля типа select
     * @param int $id - id поля типа select
     * @param int $id2 - id текущей записи в таблице на которую ссылается поле
     *   (оно проставляется как текущее значение)
     */
    public function getSelectListHtml(int $id) {
        
    }
}