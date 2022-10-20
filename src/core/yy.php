<?php
use Illuminate\Support\Facades\Session;

class yy { 
/**
	суперглобальный класс с основными функциями для работы
*/

    public $settings;
    public $built_in_settings;
    public $settings2;
    public $baseurl=null;
    public $basepath=null;
    public $locale_loaded = false;
    private $locale;
    public $user_data;
    public $Engine_Path;
	
    function __construct() {
        $this->Engine_Path = base_path() . '/vendor/alxnv/nesttab';
        $this->baseurl=dirname($_SERVER['SCRIPT_NAME']).'/';
        if ($this->baseurl=='//') $this->baseurl='/';
        $this->basepath = dirname($_SERVER["SCRIPT_FILENAME"]).'/'; //dirname(__DIR__);  
		$this->settings = require($this->Engine_Path . '/settings/settings.php');
		$this->built_in_settings = require('built_in_settings.php');
		
		include $this->Engine_Path . "/settings/callbacks/user_data.php";
		$this->user_data = new user_data();
    }

    function init() {
        //$this->register_autoload();
	$this->settings2 = require('settings2.php');
        global $db;
        $db = new \Alxnv\Nesttab\core\DbNesttab();
        //mysqli_report(MYSQLI_REPORT_ALL | MYSQLI_REPORT_STRICT); // перехватывать все сообщения об ошибках mysqli
    }

    
    public static function gotoErrorPage($s) {
        global $yy;
        $lnk = \yy::get_error_session();
        session([$lnk => $s]);
        Session::save();
        static::redirect_now($yy->baseurl . 'nesttab/error');
        //exit;
    }

    public static function gotoMessagePage($s) {
        global $yy;
        $lnk = \yy::get_message_session();
        session([$lnk => $s]);
        header('Location: ' . $yy->baseurl . 'nesttab/message');
        exit;
    }
    /**
     * Redirect the user no matter what. No need to use a return
     * statement. Also avoids the trap put in place by the Blade Compiler.
     *
     * @param string $url
     * @param int $code http code for the redirect (should be 302 or 301)
     */
    public static function redirect_now($url, $code = 302)
    {
        try {
            \App::abort($code, '', ['Location' => $url]);
        } catch (\Exception $exception) {
            // the blade compiler catches exceptions and rethrows them
            // as ErrorExceptions :(
            //
            // also the __toString() magic method cannot throw exceptions
            // in that case also we need to manually call the exception
            // handler
            $previousErrorHandler = set_exception_handler(function () {
            });
            restore_error_handler();
            call_user_func($previousErrorHandler, $exception);
            die;
        }
    }

    public static function isPost() {
        return (isset($_POST) && count($_POST) > 0);
    }


    /**
     * Проверяет, залогинен ли пользователь, и если нет, то переходит на страницу login.php
     * @global yy $yy
     * @return array массив с данными о текущем пользователе
     */
    public static function testlogged():array {
        /*
        if (!isset($_SESSION['logged_user7237'])) {
                header("Location: ./login.php");
                exit;
        }

        return $_SESSION['logged_user7237'];        */
    }

    /**
     * 
     * @return string Имя сессионной переменной, в которой передается сообщение об ошибке
     */
    public static function get_error_session() {
        return 'error8732';
    }

    /**
     * 
     * @return string Имя сессионной переменной, в которой передается сообщение об ошибке
     */
    public static function get_error_edit_session() {
        return 'error7735';
    }

    /**
     * 
     * @return string Имя сессионной переменной, в которой передается сообщение об ошибке
     */
    public static function get_edit_session() {
        return 'data7735';
    }

    /**
     * 
     * @return string Имя сессионной переменной, в которой передается сообщение о благополучном
     *  завершении операции
     */
    public static function get_message_session() {
        return 'message8732';
    }
    /**
     * Получить url директории сайта
     * @return <type> 
     */
    /*
    public function baseUrl() {
        global $yy;
        return \yy::$baseurl;
    }*/
    
    /**
     * Защита пути от использования '\..\'
     * @param string $path
     * @return string
     */
    
    public static function path_defend(string $path) {
        $s = str_replace('..', '', $path);
        $s = str_replace('"', '', $s);
        $s = str_replace("'", '', $s);
        return $s;
    }

    public function translate_aliases($path) {
        $n = strpos($path, '\\');
        if ($n !== false) {
            $s = substr($path, 0, $n);
            //var_dump($n, $s);
            if (isset($this->settings2['aliases'][$s])) {
                return \yy::path_defend($this->settings2['aliases'][$s] . substr($path, $n));
            }
        }
        return \yy::path_defend($path);
    }
    
    /*public function register_autoload() {
        spl_autoload_register(function ($className) {
            $cn = $this->translate_aliases($className);
            //var_dump($cn);//exit;
            include $cn . '.php';
        });
        
    }*/

    /**
     * Добавляет элементы одного массива к другому. Если этот ключ уже есть, то он 
     *   не заменяется
     * @param array $arr
     * @param array $arr2
     */
    public static function add_keys(array $arr, array $arr2) {
        $arr3 = $arr;
        foreach ($arr2 as $key => $value) {
            if (!isset($arr3[$key])) $arr3[$key] = $value;
        }
        return $arr3;
    }


    /**
     * Получить base path директории сайта
     * @return <type> 
     */
    static public function basePath() {
        global $yy;
        return $yy->basepath;
    }
	
	public static function makeselsimp(&$arr,$n=0) {
	$s='';
	for ($i=0,$j=count($arr);$i<$j;$i++) {
	 $s.='<option '.($i==$n ? 'selected ' : '').'value='.$i.'>'.$arr[$i];
	 };
	return $s;
	}
	/*
	public static function t(string $text) {
	/**
		Функция для работы с локалями
		@param string $text текст для приведения к локали
		@return string возвращаемый приведенный текст
            global $yy;
		if ($yy->settings['language'] <> $yy->built_in_settings['language'] 
			&& !$yy->locale_loaded) $yy->loadlocale();
			//var_dump($yy->locale);
		if ($yy->locale_loaded) {
			if (isset($yy->locale[$text])) {
				return $yy->locale[$text];
			} else {
				return $text;
			}
		}			
		return $text;
	}
	*/
	
        public static  function get_js_lang_file() {
            global $yy;
		return 'locale/' . $yy->settings['language'] . '/names.js';
            
        }
        
        public function loadlocale() {
		$s =  $this->basepath . 'locale/' . $this->settings['language'] . '/lang.php';
		//var_dump($s);
		$this->locale=require($s);
		$this->locale_loaded = true;
	}

    public static  function qobj($s) {
        global $db;
        return $db->qobj($s);
    }
    public static function q($s) {
        global $db;
        return $db->q($s);
    }

    public static  function qs($s) {
        // экранирует для вывода на экран
        return htmlspecialchars($s);
    }

    /**
	* функция возвращает пустую строку для аргумента равного 1 или сам аргумент
	* @param int $num
	* @return int|string
	*/
	public static function num1(int $num):string {
		return ($num == 1 ? '' : $num);
	}

    /**
     * возвращает транслитерацию строки с русского на английский
     * @param <type> $s
     * @return <type>
     */
    static public function alphatrans($s) {
$alphas=array(
    "а" => "a",
    "б" => "b",
    "в" => "v",
    "г" => "g",
    "д" => "d",
    "е" => "e",
    "э" => "e",
    "ё" => "yo",
    "ж" => "zh",
    "з" => "z",
    "и" => "i",
    "й" => "j",
    "к" => "k",
    "л" => "l",
    "м" => "m",
    "н" => "n",
    "о" => "o",
    "п" => "p",
    "р" => "r",
    "с" => "s",
    "т" => "t",
    "у" => "u",
    "ф" => "f",
    "х" => "h",
    "ц" => "ts",
    "ч" => "ch",
    "ш" => "sh",
    "щ" => "sch",
    "ь" => "",
    "ъ" => "",
    "ы" => "y",
    "ю" => "yu",
    "я" => "ya"
    );
        
    $s2=strtr(mb_strtolower($s),$alphas);
        return $s2;
    }

/**
 * заменяет в строке вхождения распарсенные parsestrall
 * @param string $str1 - строка в которой заменять
 * @param array $afrom - массив исходных данных для замены
 * @param array $ato - массив конечных данных для замены
 * @return string 
 * 
 */
static function repltextarray3($str1,$afrom,$ato) {
        $s=$str1;
        $arr1=$afrom;
        $arr2=$ato;
        $last=strlen($s);
        $s2='';
        $modifiedindex1=-1;
        for ($j=count($arr1)-1;$j>=0;$j--) {
            if ($arr1[$j][0][0]!=$arr2[$j][0][0]) {
                $joffset=$arr1[$j][0][1]; // смещение строки тэга в основной строке
                $ps2=$joffset+strlen($arr1[$j][0][0]);
                $s2=$arr2[$j][0][0].substr($s,$ps2,$last-$ps2).$s2;
                $last=$joffset; //-$arr1[$j][0][1];
                $modifiedindex1=$j;
            }
        }
        if ($modifiedindex1==-1) {
            $s2=$s;
        } else {
            $s2=substr($s,0,$last).$s2;
        }
        
        /*$this->str1=$s2;
        $this->afrom=$arr1;
        $this->ato=$arr2;*/
        return $s2;
    }
    
static function parsestrall(&$mtch,&$mtchto,$regex,$str1) {
        $mtch=array();
        preg_match_all($regex,$str1,$mtch, PREG_SET_ORDER+PREG_OFFSET_CAPTURE);
        $mtchto=$mtch;
}

/**
 * 'db->escape()' для обращения к бд со строками вида 'update table set s=$1, n=$2'
 * @param string $s
 * @return string 
 * 
 */
    static function db_escape($s, $arr) {
        global $db;
        if (count($arr) == 0) return $s;
        $afrom=array();
        $ato=array();
        \yy::parsestrall($afrom,$ato,'/\$[\d]+/',$s);
        //echo '<pre>';
        //var_dump($ato);
        for ($i=0;$i<count($ato);$i++) {
            $ato[$i][0][0]= $db->escape($arr[intval(substr($ato[$i][0][0],1)) - 1]);
        }
        $s2=\yy::repltextarray3($s,$afrom,$ato);
        return $s2;
    }
    
    public static function mb_ucfirst($string) {
        return mb_strtoupper(mb_substr($string, 0, 1)).mb_substr($string, 1);
    }
     
}

global $yy;
$yy = new yy();
$yy->init();
?>