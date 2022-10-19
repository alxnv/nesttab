<?php
namespace Alxnv\Nesttab\Http\Controllers;

/**
 * Редактирование структуры таблиц - общий список
 */

class ChangeStructListController extends BasicController {
    public function __invoke() {
        $list = (new \Alxnv\Nesttab\Models\StructModel())->getAll();
        return view('nesttab::change_struct_list', ['list' => $list]);
    }
    
    
}

