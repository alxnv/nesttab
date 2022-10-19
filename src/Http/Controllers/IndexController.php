<?php

namespace Alxnv\Nesttab\Http\Controllers;

//use App\Http\Controllers\Controller;
//use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;

class IndexController extends BasicController
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
        $arr = $db->qlist("select * from yy_columns");
        dd($arr);
        return view('nesttab::index');
    }
}