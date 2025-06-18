<?php

namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class StructTableController extends BasicController
{
    /**
     * Вывести настройки таблицы
     * @param int $id - id таблицы
     */
    public function showSettings(int $id) {
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id);
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $tableModel = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        $lnk2 = \yy::getEditSession();
        if (Session::has($lnk2)) {
            $lnk = \yy::getErrorEditSession();
            $r = session($lnk2);
        } else {
            $r = $tbl;
        }

        return $tableModel->showSettings($tbl, $id, $r);
    }
    /**
     * Сохранить настройки таблицы (поля, выводимые для списка записей в 'edit/')
     * @param int $id - id таблицы
     */
    public function saveSettings(int $id, Request $request) {
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOne($id);
        $type = \Alxnv\Nesttab\core\TableHelper::getTableTypeByOneChar($tbl['table_type']);
        $tableModel = \Alxnv\Nesttab\Models\Factory::createTableModel($type); 
        
        return $tableModel->saveSettings($tbl, $id, $request);
    }
}