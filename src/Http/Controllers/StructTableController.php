<?php

namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;

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

        return $tableModel->showSettings($tbl, $id);
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