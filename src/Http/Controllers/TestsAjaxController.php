<?php

namespace Alxnv\Nesttab\Http\Controllers;

//use App\Http\Controllers\Controller;
//use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TestsAjaxController extends BasicController
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function infiniteRun() {
        \yy::waitSeconds(100);
        return response()->json(['message' => 'Скрипт выполнялся в течение 100 секунд']);
    }
    
    public function infinite()
    {
        //dd(6);
        global $db;
        //$arr = $db->qlist("select * from yy_columns");
        //dd($arr);
        //session(['rr' => 55]);
        //Session::save();
        return view('nesttab::tests.ajax_infinite');
    }
}