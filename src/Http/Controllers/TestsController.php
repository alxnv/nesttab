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
    
    public function locktablesStartTest() {
        //return view('nesttab::tests.locktables');
        global $db;
        echo '1<br />';
        flush();
        $db->errorMode = $db::ERROR_MODE_RETURN_ERROR;
        $db->qdirect("drop table temp_s5");
        $db->qdirect("CREATE TABLE `temp_s5` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `id1` int(11) NOT NULL,
        `name` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        
        $db->qdirect("SET AUTOCOMMIT=0;");
        $db->qdirect("lock tables temp_s5 write");
        if ($db->errorCode <> 0) {
            echo 'Error: lock tables<br />' . $db->errorMessage . '<br /><br />';
            exit;
        }
        
        sleep(10);

        $db->qdirect("commit");
        $db->qdirect("unlock tables");
        if ($db->errorCode <> 0) {
            echo 'Error: unlock tables<br />' . $db->errorMessage . '<br /><br />';
            exit;
        }

        $db->qdirect("SET AUTOCOMMIT=1;");
        echo 'Success';
    }
    
    /**
     * Тестируется locktablesStartTest, Он должнен быть запущен перед запуском этой функции
     *   в другой вкладке браузера
     * При удаче висит 10 секунд и выдает success
     * @global \Alxnv\Nesttab\Http\Controllers\type $db
     */
    public function locktablesTestTest() {
        //return view('nesttab::tests.locktables');
        global $db;
        echo '2<br />';
        flush();
        $db->qdirect("SET AUTOCOMMIT=0;");
        $db->qdirect("lock tables temp_s5 write");
        if ($db->errorCode <> 0) {
            echo 'Error: lock tables<br />' . $db->errorMessage . '<br /><br />';
            exit;
        }

        $db->qdirect("commit");
        $db->qdirect("unlock tables");
        if ($db->errorCode <> 0) {
            echo 'Error: unlock tables<br />' . $db->errorMessage . '<br /><br />';
            exit;
        }

        $db->qdirect("SET AUTOCOMMIT=1;");
        echo 'Success';
    }
    
    public function locktablesTest() {
        return view('nesttab::tests.locktables');
    }
    
    
    public function saveInputNullTest(Request $request) {
        $r = $request->all();
        var_dump('$r["arr"][2]', $r['arr'][2]);
        dd($r);
        //return view('nesttab::tests.input-null-strings');
    }
    
    
}