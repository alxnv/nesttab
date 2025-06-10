<?php

namespace Alxnv\Nesttab\Http\Controllers;

//use App\Http\Controllers\Controller;
//use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TestsController extends BasicController
{
    
    public function doubleClickTest() {
        return view('nesttab::tests.double-click');
        
    }
    /**
     * insert $num records into table temp_bench
     * @global type $db
     * @param type $num
     */
    public function populateDB($num) {
        global $db;
        $arr =[];
        for ($i = 0; $i<$num; $i++) {
            $arr[] = '(1, "jkjkljlkjlkjkljlkjkl")';
        };
        $s = join(', ', $arr);
        $db->q("insert into temp_bench_i (id1, name) values " . $s);
        echo 'db populated<br/>';
    }
    
    public function showDbSelectTime() {
        global $db;
        //$this->populateDB(11000);
        
        $t = microtime(true);
        $db->qlist("select * from temp_bench_i");
        $time = (microtime(true) - $t);
        echo 'Time: ' . $time;

    }
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