<?php

namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

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
    public function getSelectListHtml(int $id, Request $request) {
        $columnsModel = new \Alxnv\Nesttab\Models\ColumnsModel();
        //Log::debug('req ' . print_r($request->all(), true));
        
        if (!$request->has('q')) {
            $search_value = '%';
        } else {
            $search_value = '%' . $request->input('q') . '%';
        }
        $table_name = '';
        $names = $columnsModel->getOneSelectFldNames($id, $table_name);
        $more = false;
        $aresult = $columnsModel->getSelectValuesList($table_name, $names, $search_value, $more);
        $arr = ['list' => $aresult, 'more' => $more];
        //Log::debug('ajax ' . response()->json($arr));
        //var_dump(aresult);
        //$aresult = [['id' => 1, 'text' => 'gfdghd']];
        return response()->json($arr);
        
    }
}