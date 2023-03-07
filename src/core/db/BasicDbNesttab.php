<?php
// функции для работы с БД
// уровень записи для getkrohi возвращается в db->prcnt, топ uid корня в db->toplid
namespace Alxnv\Nesttab\core\db;

use Illuminate\Support\Facades\DB;

class BasicDbNesttab {
    /**
     * Типы данных представленные в БД
     */
    const BOOL_TYPE = 1; 
    const INT_TYPE = 2; 
    const STR_TYPE = 3;
    const TEXT_TYPE = 4; 
    const HTML_TYPE = 5;
    const FILE_TYPE = 7;
    const IMAGE_TYPE = 8;
    const FLOAT_TYPE = 9;
/*
    function connect() {
		global $yy;
        if (!isset($this->handle)) {
            $this->handle = mysqli_connect ($yy->settings['db']['host'],
                    $yy->settings['db']['user'],
                    $yy->settings['db']['password'])
                    or \yy::gotoErrorPage("Can't open MySQL connection");
			//var_dump($this->handle);

            @mysqli_select_db ($this->handle, $yy->settings['db']['dbname'])
                    or \yy::gotoErrorPage(sprintf ("Can't selct database [%s]: %s", mysqli_errno ($this->handle), mysqli_error ($this->handle)));
        };


    }
*/
    public $handle;
    public $errorCode; // код ошибки БД
    public $errorMessage; // сообщение обо ошибке БД
    public function __construct() {
        $this->handle = DB::connection()->getPdo();
        $this->handle->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
    }
    
    /**
     * escape name of field or table
     * @param string $s
     * @return string
     */
    public function nameEscape(string $s) {
        return '`' . $s . '`';
    }
    
    public function escape($s) {
	//	global $db;
//        return mysqli_real_escape_string($this->handle, $s);
        return $this->handle->quote($s);
    }

    function qlist($s, $params = []) {
        $sth = $this->handle->query(\yy::dbEscape($s, $params))
                or \yy::gotoErrorPage(sprintf ("Error %s\n", mysqli_error($this->handle)));
/*        if ($sth) {
	    $rs = [];
		while ($obj = $sth->fetch_object()) {
			$rs[] = $obj;
		}*/
        $rows = $sth->fetchAll(\PDO::FETCH_CLASS);
        return $rows;
    }

    function qlistArr($s, $params = []) {
        $sth = $this->handle->query(\yy::dbEscape($s, $params))
                or \yy::gotoErrorPage(sprintf ("Error %s\n", mysqli_error($this->handle)));
/*        if ($sth) {
	    $rs = [];
		while ($obj = $sth->fetch_object()) {
			$rs[] = $obj;
		}*/
        $rows = $sth->fetchAll();
        return $rows;
    }

    function qdirect($s, $params = []) {
        $affected = $this->handle->exec(\yy::dbEscape($s, $params));
        if (intval($this->handle->errorInfo()[0]) <> 0) {
           \yy::gotoErrorPage(sprintf ("Error %s\n", $this->handle->errorInfo()[2]));
        };
        //if (!$sth) throw new \Exception('Table already exists', 1050);
        return $affected;
    }

    /**
     * Вызывается иногда для сохранения кода и сообщения об ошибке БД
     * @param type $code - код ошибки БД
     * @param type $message - сообщение об ошибке БД
     */
    protected function setExceptionReturnValues($code, $message) {
        $this->errorCode = $code;
        $this->errorMessage = $message;
    }
    /**
     * Выполняется Mysqli запрос. в случае ошибки из $error_codes (например 
     *    1050 (таблица уже существует))
     *   не прерывается выполнение программы
     * @param string $s
     * @param array $error_codes
     * @return handler
     */
    function qdirectSpec($s, array $error_codes, $params = []) {
        try {
            $sth = $this->handle->exec(\yy::dbEscape($s, $params));
        } catch (\Exception $e) {
            $this->setExceptionReturnValues($e->getCode(), $e->getMessage());
        }
        if (intval($this->errorCode) <> 0) {
            if (!in_array($this->errorCode, $error_codes)) {
                \yy::gotoErrorPage(sprintf ("Error %s\n", $this->handle->errorInfo()[2]));
                
            } else {
                return false;
            }
        }
        //if (!$sth) throw new \Exception('Table already exists', 1050);
        return true;
    }

    /**
     * Выполняется Mysqli запрос. в случае ошибки
     *   не прерывается выполнение программы и возвращается false
     * @param string $s
     * @param array $error_codes
     * @return handler
     */
    function qdirectNoErrorMessage($s, $params = []) {
        //dd(\yy::dbEscape($s, $params));
        try {
            $sth = $this->handle->exec(\yy::dbEscape($s, $params));
        } catch (\Exception $e) {
            $this->setExceptionReturnValues($e->getCode(), $e->getMessage());
            return false;
        }
        return true;
    }

    function qobj($s, $params =[]) {
		/**
			выполняет запрос и возвращает одну строчку с полученным объектом из строки
			@return array (или null если 0 записей)
		*/
        $sth = $this->handle->prepare(\yy::dbEscape($s, $params));
        $sth->execute();
        
        //$sth=$this->qdirect($s, $params);
        $f = $sth->fetchObject();
        return ($f ? $f : null);
    }

    function q($s, $params = []) {
		/**
			выполняет запрос и возвращает одну строчку с полученным массивом
			@return array (или null если 0 записей)
		*/
        $sth = $this->handle->prepare(\yy::dbEscape($s, $params));
        $sth->execute();
        
        //$sth=$this->qdirect($s, $params);
        $f = $sth->fetch();
        return ($f ? $f : null);
    }
/*
    function getkrohi($tab,$uid2) {
        $sth=$this->q("select a.uid as uid1,a.ordr as ord1,a.naim as naim1,a.topid as top1,
        b.uid as uid2,b.ordr as ord2,b.naim as naim2,b.topid as top2,
        c.uid as uid3,c.ordr as ord3,c.naim as naim3,c.topid as top3,
        d.uid as uid4,d.ordr as ord4,d.naim as naim4,d.topid as top4,
        e.uid as uid5,e.ordr as ord5,e.naim as naim5,e.topid as top5
        from $tab a left join $tab b on a.topid=b.uid left join $tab c
        on b.topid=c.uid left join $tab d on c.topid=d.uid left join $tab e
        on d.topid=e.uid where a.uid=$uid2");
        $arr=mysql_fetch_array($sth);
        for ($i=1;$i<6;$i++) {
            if (is_null($arr['uid'.$i])) break;
        };
        $this->prcnt=$i-1; // уровень записи
        $this->toplid=$arr['uid'.$this->prcnt];
        return $arr;
    }
	
	function hasintreefrom($pos,$val,$ar) {
		// проверка $val содержится ли в массиве $ar начиная с $ar['uid'.$pos] 
        for ($i=$pos;$i<6;$i++) {
            if ($ar['uid'.$i]==$val) return true;
        };
		return false;
	}

	function kroh_delone($ar,$n) {
		// удалить n-й элемент в массиве крох (например uid2,ordr2,naim2,top2) со сдвигом следующих значений на это место
		$ar2=$ar;
		//var_dump($ar);
		for ($i=1;$i<=$this->prcnt;$i++) {
			if ($i>=$n) {
				$ar2['uid'.$i]=$ar2['uid'.($i+1)];
				$ar2['ord'.$i]=$ar2['ord'.($i+1)];
				$ar2['naim'.$i]=$ar2['naim'.($i+1)];
				$ar2['top'.$i]=$ar2['top'.($i+1)];
			}
		}
		
		return $ar2;
	}
	
    function printkrohi($ar,$sn,$linkfirst) {
        // linkfirst - показывать ли первую ссылку
        $b=0;
        //my3::log('t',$ar);
        $s='';
        for ($i=$this->prcnt;$i>0;$i--) {
            if ($b) $s.=' / ';
            if (($linkfirst || $b) && $i>1) $s.='<a href="'.$sn.$ar['uid'.$i].'">';
            $s.=my3::nbsh($ar['naim'.$i]);
            if (($linkfirst || $b) && $i>1) $s.='</a>';
            $b=1;
        }
        return $s;
    }
*/	

} // enc class

?>