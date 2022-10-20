<?php
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;

class ErrorController extends BasicController {
    public function __invoke() {
        return view('nesttab::error');
    }
    
}