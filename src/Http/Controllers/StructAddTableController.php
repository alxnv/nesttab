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
        $r = $request->all();
        $sa = new \Alxnv\Nesttab\Models\StructAddTableModel();
        if ($sa->execute($r, $message)) \yy::gotoMessagePage($message);
           else \yy::gotoErrorPage($message);
        
    }
    
}

