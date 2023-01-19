<?php
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ErrorController extends BasicController {
    public function __invoke() {
        $wl = intval($_GET['wl']);
        $layout = ($wl ? 'nesttab::layout_blank' : config('nesttab.layout'));
        return view('nesttab::error', ['layout' => $layout, 'wl' => $wl]);
    }
    
}