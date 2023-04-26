<?php

namespace Alxnv\Nesttab\Http\Controllers;

//use App\Http\Controllers\Controller;
//use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

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
    
    public function infiniteSql()
    {
        return view('nesttab::tests.ajax_infinite_sql');
    }
    public function infiniteSqlRun() {
        $t = microtime(true);
        while (microtime(true) - $t < 90) {
            $arr = DB::select("select * from test_ajax12");
        }
        return response()->json(['message' => 'Скрипт db выполнялся в течение 90 секунд']);

    }
    
    public function infiniteSqlMakeTable() {
        //return response()->json(['message' => 'Таблиа']);
        DB::statement("create table test_ajax12 (id int NOT NULL auto_increment, " 
                    . " PRIMARY KEY (id))");
        DB::insert("insert into test_ajax12 values (0), (0), (0), (0), (0), (0), (0), (0), (0), (0)");
        return response()->json(['message' => 'Таблиа создана и заполнена 10 значениями']);
    
    }
    
}