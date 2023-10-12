<?php

namespace Alxnv\Nesttab\Http\Controllers;

//use App\Http\Controllers\Controller;
//use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TestsController extends BasicController
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        //dd(6);
        global $db;
        //$arr = $db->qlist("select * from yy_columns");
        //dd($arr);
        //session(['rr' => 55]);
        //Session::save();
        return view('nesttab::tests.index');
    }
    
    public function inputNullTest() {
        return view('nesttab::tests.input-null-strings');
    }
    
    public function saveInputNullTest(Request $request) {
        $r = $request->all();
        var_dump('$r["arr"][2]', $r['arr'][2]);
        dd($r);
        //return view('nesttab::tests.input-null-strings');
    }
    
    
}