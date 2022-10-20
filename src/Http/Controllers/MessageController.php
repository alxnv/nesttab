<?php
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends BasicController {
    public function __invoke() {
        return view('nesttab::message');
    }
    
}