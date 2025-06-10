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
	if (!isset($r['tbl_type']) || !isset($r['int_bytes']))  \yy::gotoErrorPage(__('Required parameter is not passed'));
	$tbl_idx = intval($r['tbl_type']);
        $intBytes = intval($r['int_bytes']);
	if ($tbl_idx < 0 || $tbl_idx >= count($arr2)) \yy::gotoErrorPage('Wrong index of table');
        $model = \Alxnv\Nesttab\Models\Factory::createTableModel($yy->settings2['table_names'][$tbl_idx]);
        
        $tableId = 0;
        $topTable = 0; // parent table id for top level table equal to 0
        if ($model->createTable($r, $message, $tableId, $topTable, $intBytes)) \yy::gotoMessagePage($message);
           else \yy::gotoErrorPage($message);
        
    }
    
}

