<?php

namespace Alxnv\Nesttab\Http\Controllers;

use App\Http\Controllers\Controller;
//use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        //dd(6);
        return view('nesttab::index');
    }
}