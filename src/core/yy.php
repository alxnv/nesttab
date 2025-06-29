<?php
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Cache;

    /**
     * Superglobals:
     * @global array $td 
     *   Все данные из таблицы yy_tables в суперглобальной переменной $td
     *    $td['dat'] - данные таблицы [][id,p_id,name,descr]
     *    $td['ind'] - индексы айдишников данных в 'dat'
     *    $td['cat'] - массив [p_id][id]
     * @global type $db - объект для работы с БД
     * @global object $yy - синглтон с вспомогательными функциями
     */


class yy { 
/**
	суперглобальный класс с основными функциями для работы
*/

    //public $settings;
    public $built_in_settings;
    public $settings2;
    public $baseurl=null;
    public $nurl; // url of nesttab with trailing slash
    public $uurl; // url of not secret pages
    public $basepath=null;
    //public $locale_loaded = false;
    //private $locale;
    public $localeObj; // object for current locale from Models\locales
    public $format; // current locale's datetime format
    public $user_data;
    public $Engine_Path;
    public $fieldObjectsPool = [];
    public $tableObjectsPool = [];
    public $db_settings;
    public $phpScripts; // names of loaded php scripts
    public $whithout_layout = 0; // отображать сообщения об ошибках без включения layout
    public $locksToReleaseBeforeExit = []; // lock-и, которые нужно освободить перед
       // выходом из скрипта
    
    function __construct() {
        $this->Engine_Path = base_path() . '/vendor/alxnv/nesttab';
        $this->baseurl=dirname($_SERVER['SCRIPT_NAME']).'/';
        if ($this->baseurl=='//') $this->baseurl='/';
        $this->nurl = $this->baseurl . config('nesttab.nurl') . '/';
        //$this->uurl = $this->baseurl . config('nesttab.uurl') . '/';
        $this->basepath = dirname($_SERVER["SCRIPT_FILENAME"]).'/'; //dirname(__DIR__);  
		//$this->settings = require($this->Engine_Path . '/settings/settings.php');
		//$this->built_in_settings = require('built_in_settings.php');
	$this->db_settings = require($this->Engine_Path . '/src/core/db/' . 
                config('nesttab.db_driver') . '/settings.php');	
        include $this->Engine_Path . "/settings/callbacks/user_data.php";
	$this->user_data = new user_data();
        // load locale info (datetime format)
        $s = '\\Alxnv\\Nesttab\\Models\locales\\' . config('app.locale');
        $this->localeObj = new $s();
        $this->format = $this->localeObj->format;
    }

    function init() {
        //$this->register_autoload();
	$this->settings2 = require('settings2.php');
        $mx = ini_get('max_execution_time');
        if (($mx === false) || ($mx === 0)) {
            $mx = 200;
        }
        $this->settings2['max_exec'] = intval($mx);

        global $db;
        $db = new \Alxnv\Nesttab\core\db\mysql\DbNesttab();
        //mysqli_report(MYSQLI_REPORT_ALL | MYSQLI_REPORT_STRICT); // перехватывать все сообщения об ошибках mysqli
    }

    /**
     * указывается Lock, который нужно освободить перед gotoErrorPage и gotoMessagePage
     * @param string $s
     */
    public function setExitReleaseLock(string $s) {
        $this->locksToReleaseBeforeExit[$s] = 1;
    }
    
    /**
     * освобождает lock-s, указанные в $this->locksToReleaseBeforeExit
     */
    public function releaseLocks() {
        $arr = array_keys($this->locksToReleaseBeforeExit);
        foreach ($arr as $item) {
            Cache::forget($item);
        }
    }
    
    public static function gotoErrorPage($s) {
        global $yy;
        $lnk = \yy::getErrorSession();
        session([$lnk => $s]);
        Session::save();
        static::redirectNow($yy->nurl . 'error?wl=' . $yy->whithout_layout);
        //exit;
    }

    public static function gotoMessagePage($s) {
        global $yy;
        $lnk = \yy::getMessageSession();
        session([$lnk => $s]);
        Session::save();
        static::redirectNow($yy->nurl . 'message');
        //header('Location: ' . $yy->baseurl . 'nesttab/message');
        //exit;
    }
    /**
     * Redirect the user no matter what. No need to use a return
     * statement. Also avoids the trap put in place by the Blade Compiler.
     * !!!!!! ПЕРЕД РЕДИРЕКТОМ ЕСЛИ ИЗМЕНЯЛИСЬ ДАННЫЕ СЕССИИ СДЕЛАТЬ Session::save()
     *
     * @param string $url
     * @param int $code http code for the redirect (should be 302 or 301)
     */
    public static function redirectNow($url, $code = 302)
    {
        global $yy;
        $yy->releaseLocks();


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

    /*
    public static function isPost() {
        return (isset($_POST) && count($_POST) > 0);
    }
    */

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
    public static function getErrorSession() {
        return 'error8732';
    }

    /**
     * 
     * @return string Имя сессионной переменной, в которой передается сообщение об ошибке
     */
    public static function getErrorEditSession() {
        return 'error7735';
    }

    /**
     * 
     * @return string Имя сессионной переменной, в которой передается сообщение об ошибке
     */
    public static function getEditSession() {
        return 'data7735';
    }

    /**
     * 
     * @return string Имя сессионной переменной, в которой передается сообщение о благополучном
     *  завершении операции
     */
    public static function getMessageSession() {
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
    
    public static function pathDefend(string $path) {
        $s = str_replace('..', '', $path);
        $s = str_replace('"', '', $s);
        $s = str_replace("'", '', $s);
        return ($path === $s ? $s : static::pathDefend($s)); // если нечего заменять, то возвращаем
         // саму строку, иначе рекурсия
    }
    /**
     * Защита пути от использования '\..\'
     * @param string $path
     * @return string
     */
    
    public static function pathDefend2(string $path) {
        $s = str_replace('..', '', $path);
        return ($path === $s ? $s : static::pathDefend2($s)); // если нечего заменять, то возвращаем
         // саму строку, иначе рекурсия
    }
    /*

    public function translateAliases($path) {
        $n = strpos($path, '\\');
        if ($n !== false) {
            $s = substr($path, 0, $n);
            //var_dump($n, $s);
            if (isset($this->settings2['aliases'][$s])) {
                return \yy::pathDefend($this->settings2['aliases'][$s] . substr($path, $n));
            }
        }
        return \yy::pathDefend($path);
    }
    
    /*public function register_autoload() {
        spl_autoload_register(function ($className) {
            $cn = $this->translateAliases($className);
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
    public static function addKeys(array $arr, array $arr2) {
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
	
        public static  function getJsLangFile() {
            global $yy;
		return 'lang/' . Lang::getLocale() . '/names.js';
            
        }
        
        /*public function loadlocale() {
		$s =  $this->basepath . 'locale/' . $this->settings['language'] . '/lang.php';
		//var_dump($s);
		$this->locale=require($s);
		$this->locale_loaded = true;
	}*/

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

    public function loadPhpScript($scriptName) {
        if (isset($this->phpScripts[$scriptName])) return true;
        try {
            include($scriptName);
        } catch (\Exception $exception) {
            return false;
        }
        return true;

    }
    
    /**
     * Test if the user function for table is loaded from 'app/Models/nettab/tables'
     *   and returns the full function name with namespace if this function is loaded
     * @param string $tableName - name of the table
     * @param string $functName - name of the function
     * @return string
     */
    public static function userFunctionIfExists(string $tableName, string $functName) {
        $s = '\callbacks\tables\\' . $tableName . '\\' . $functName;
        if (function_exists($s)) {
            return $s;
        } else {
            return '';
        }
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
    static function dbEscape($s, $arr) {
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
     
    
    /**
     * Преобразует многострочную строку к такому виду, чтобы ее можно было вывести
     *   в js заключенной в ""
     * @param string $s - строка для преобразования
     * @return string
     */
    public static function jsmstr(string $s) {
        $s2 = str_replace("\\", "\\\\", $s);
        $s2 = str_replace("\r", "\\\r", $s2);
        $s2 = str_replace('"', "'", $s2);
        return $s2;
    }
    
    /**
     * Подождать заданное количество секунд, запустив цикл ожидания
     * @param int $seconds - количество секунд ожидания
     */
    public static function waitSeconds(int $seconds) {
        $time = microtime(true);
        do {
            $t = microtime(true);
            usleep(1); // ожидание 1 миллисекунду
            for ($i=0; $i < 50000000; $i++) {};
        }
        while ($t - $time < $seconds);
    }
    
    /**
     * Создает поле Html-редактирования текста
     * @param string $fieldName - имя поля
     * @param string $value - начальное содержание поля
     * @param int $heightPixels - высота поля в пикселях
     *    (ширина поля - 100% от ширины родительского элемента)
     */
    public static function htmlEditor(string $fieldName, string $value, int $heightPixels = 800) {
        echo '<textarea class="html_editor" id="' . $fieldName . '"'
                . ' name="' . $fieldName . '" height="' . $heightPixels . 'px">';
        echo $value;
        echo '</textarea>';
        // jquery domready block add
        //\blocks::add('jquery', "new nicEditor().panelInstance('" . $fieldName . "');");
        echo "<script>
	CKEDITOR.replace( '" . $fieldName . "' );
        </script>";
    //        $('" . $fieldName . "').ckeditor();
/*        echo "<script>
                ClassicEditor
		.create( document.querySelector( '#" . $fieldName . "' ) )
		.catch( error => {
			console.error( error );
		} );
</script>";*/
        /*echo "
<ckeditor v-model='editorData' />
        ";*/
    }
    
    /**
     * Возвращает intval($r[$index])
     * Проверяем что указанное значение есть в массиве
     *  если нет, выдаем ошибку
     * затем если значение равно нулю, также выдаем ошибку
     * @param array $r - массив из которого берем значение ((array)Request)
     * @param string $index - индекс значения в массиве, которое получаем
     * @return type
     */
    public static function testExistsNotZero(array $r, string $index) {
        if (!isset($r[$index])) {
            \yy::gotoErrorPage($index . ' not found');
        }
        $value = intval($r[$index]);

        if ($value == 0) {
            \yy::gotoErrorPage('Zero value ' . $index);
        }
        return $value;
        
    }
    
    /**
     * Возвращает табличку с сообщением об успешном завершении либо об ошибке(
     *  если установленны флаги "success_message" либо $e->hasErr()
     * иначе возвращает пустую строку
     * @param array $r - (array)Request
     * @param object $e - ErrorModel() (заполнен данными в случае ошибки)
     * @return string - в случае установленных флагов, возвращает табличку,
     *   в ином случае - пустую строку
     */
    public static function getSuccessOrErrorMessage(array $r, object $e) {
        if (Session::has('saved_successfully')) {
            Session::remove('saved_successfully');
            return '<div class="success_message">' . __('Data has been saved') .
                    '</div><br /><br />';
        }
        if ($e->hasErr()) {
            return '<div class="error_message">' . __('Error') .
                    '</div><br /><br />';
        }
        return '';
    }
    
    /**
     * Текстовое представление даты
     * @param int $date
     * @return string
     */
    public static function ds(int $date) {
        return date('d.m.Y H:i:s', $date);
    }
    
    /**
     * prevent session vars from saving
     * !!! сейчас отключено
     * !!!  пытался сделать отключение записи сессий в UploadImageController 
     *    (не выводится сообщение об ошибке когда поле image required и не до конца загружено изобра-
     *        жение в поле ввода)
     */
    public static function dontSaveSession() {
        /**session_id( 'trash' ); // or call session_regenerate_id() as someone else suggested
        $_SESSION = array(); // clear the session variables for 'trash'.
        // 2-nd variant (for laravel)
        Session::invalidate();**/
    }
    
}

global $yy;
$yy = new yy();
$yy->init();
?>