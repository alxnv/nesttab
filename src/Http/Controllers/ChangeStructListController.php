<?php
namespace Alxnv\Nesttab\Http\Controllers;

/**
 * Редактирование структуры таблиц - общий список
 */

class ChangeStructListController extends BasicController {
    public function __invoke() {
        global $db;
        $db->loadAllTablesData();
        //$list = (new \Alxnv\Nesttab\Models\TablesModel())->getAll();
        return view('nesttab::change_struct_list');
    }
    
    
}

