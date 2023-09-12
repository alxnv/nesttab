<?php
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;

class StructAddTableController extends BasicController {
    public function index() {
        return view('nesttab::struct_add_table');
    }
    
    public function step22(Request $request) {
        // create table structure, step 2, write to the tables
	// пытаемся создать таблицу указанного типа и с указанным именем
        global $yy;
        $r = $request->all();
        $arr2 = $yy->settings2['table_types'];
	if (!isset($r['tbl_type']))  die('Required parameter is not passed');
	$tbl_idx = intval($r['tbl_type']);
	if ($tbl_idx < 0 || $tbl_idx >= count($arr2)) die('Wrong index of table');
        $model = \Alxnv\Nesttab\Models\Factory::createTableModel($yy->settings2['table_names'][$tbl_idx]);
        if ($model->createTable($r, $message)) \yy::gotoMessagePage($message);
           else \yy::gotoErrorPage($message);
        
    }
    
}

