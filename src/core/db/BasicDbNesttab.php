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
    const DATETIME_TYPE = 6;
    const FILE_TYPE = 7;
    const IMAGE_TYPE = 8;
    const FLOAT_TYPE = 9;
    const SELECT_TYPE = 10;

    const BAD_DBESCAPE_PARAMS = 44333; // error code
    const ERROR_MODE_GOTO_PAGE = 0; // переход на страница вывода ошибки в случае ошибки
    const ERROR_MODE_EXCEPTION = 1; // exception в случае ошибки
    const ERROR_MODE_RETURN_ERROR = 2; // нет exception, в $errorCode, $errorMessage
      // сохраняются данные об ошибке в случае ошибки
      //  $errorCode = 0 если не было ошибки
    public $handle;
    public $errorCode; // код ошибки БД
    public $errorMessage; // сообщение обо ошибке БД
    // use standart exception handler 
    public $errorMode = self::ERROR_MODE_GOTO_PAGE;
    public $isDbException = false; // is the last exception a db exception
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
        if (is_null($s)) return 'null';
        return $this->handle->quote($s);
    }

    /**
     * получить из бд набор записей в виде массива объектов,
     *     перейти к странице ошибки в случае ошибки если используем стандартный
     *       обработчик ошибок, иначе вызвать исключение
     * @param string $s - строка запроса, в ней могут быть параметры вида $1,...
     * @param array $params - параметры, если есть
     * @param int $errorMode - temporary set errorMode for this call
     * @return array
     */
    function qlist(string $s, array $params = [], int $errorMode = null) {
        global $yy;
        if (!is_null($errorMode)) {
            $errorMode2 = $errorMode;
        } else {
            $errorMode2 = $this->errorMode;
        }
        
        $this->setExceptionReturnValues(0, '');
        $this->isDbException = false;
        try {
            $s5 = \yy::dbEscape($s, $params);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = static::BAD_DBESCAPE_PARAMS;
            if ($errorMode2 == static::ERROR_MODE_GOTO_PAGE) {
                \yy::gotoErrorPage(sprintf ("Error %s\n",  $message));
            } else {
                $this->setExceptionReturnValues($code, $message);
                if ($errorMode2 == static::ERROR_MODE_EXCEPTION) {
                    throw new \Exception($message, $code); // rethrow the exception
                }
            }
            
        }
        
        try {
        $sth = $this->handle->query($s5);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if ($yy->settings2['extended_db_messages']) {
                $message = (\yy::dbEscape($s, $params) . chr(13) . chr(10) . 
                    $message);
            }
            if ($errorMode2 == static::ERROR_MODE_GOTO_PAGE) {
                \yy::gotoErrorPage(sprintf ("Error %s\n",  $message));
            } else {
                $this->setExceptionReturnValues($e->getCode(), $message);
                if ($errorMode2 == static::ERROR_MODE_EXCEPTION) {
                    $this->isDbException = true;
                    throw new \Exception($message, 1, $e); // rethrow the exception
                } else {
                    // just return, error is set
                    return [];
                }
            }
        }
        $rows = $sth->fetchAll(\PDO::FETCH_CLASS);
        return $rows;
    }

    /**
     * запросить из бд набор записей в виде массива массивов,
     *     перейти к странице ошибки в случае ошибки если используем стандартный
     *       обработчик ошибок, иначе вызвать исключение
     * @param string $s - строка запроса, в ней могут быть параметры вида $1,...
     * @param array $params - параметры, если есть
     * @param int $errorMode - temporary set errorMode for this call
     * @return array - массив полученных строк
     */
    function qlistArr(string $s, array $params = [], int $errorMode = null) {
        global $yy;
        if (!is_null($errorMode)) {
            $errorMode2 = $errorMode;
        } else {
            $errorMode2 = $this->errorMode;
        }
        
        $this->setExceptionReturnValues(0, '');
        $this->isDbException = false;
        try {
            $s5 = \yy::dbEscape($s, $params);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = static::BAD_DBESCAPE_PARAMS;
            if ($errorMode2 == static::ERROR_MODE_GOTO_PAGE) {
                \yy::gotoErrorPage(sprintf ("Error %s\n",  $message));
            } else {
                $this->setExceptionReturnValues($code, $message);
                if ($errorMode2 == static::ERROR_MODE_EXCEPTION) {
                    throw new \Exception($message, $code); // rethrow the exception
                }
            }
            
        }
        
        try {
        $sth = $this->handle->query($s5);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if ($yy->settings2['extended_db_messages']) {
                $message = (\yy::dbEscape($s, $params) . chr(13) . chr(10) . 
                    $message);
            }
            if ($errorMode2 == static::ERROR_MODE_GOTO_PAGE) {
                \yy::gotoErrorPage(sprintf ("Error %s\n",  $message));
            } else {
                $this->setExceptionReturnValues($e->getCode(), $message);
                if ($errorMode2 == static::ERROR_MODE_EXCEPTION) {
                    $this->isDbException = true;
                    throw new \Exception($message, 1, $e); // rethrow the exception
                } else {
                    // just return, error is set
                    return [];
                }
            }
        }
        $rows = $sth->fetchAll();
        return $rows;
    }


    /**
     * запросить из бд набор записей в виде массива массивов,
     *     установить код ошибки в $db->errorCode, $db->errorMessage
     *   в случае ошибки
     * @param string $s
     * @param array $params
     * @param mode (\PDO::FETCH_ASSOC (возвращаем массив) или другой режим pdo)
     * @return array - массив полученных строк
     */
    function qlistN(string $s, array $params = [], $mode = \PDO::FETCH_ASSOC) {
        $this->setExceptionReturnValues(0, '');
        try {
        $sth = $this->handle->query(\yy::dbEscape($s, $params));
        } catch (\Exception $e) {
            $this->setExceptionReturnValues($e->getCode(), $e->getMessage());
        }
        $rows = $sth->fetchAll($mode);
        return $rows;
    }

    /**
     * выполнить команду бд, перейти к странице ошибки в случае ошибки
     * @param string $s - строка запроса, в ней могут быть параметры вида $1,...
     * @param array $params - параметры, если есть
     * @param int $errorMode - temporary set errorMode for this call
     * @return int - number of affected rows
     */
    function qdirect($s, $params = [], int $errorMode = null) {
        global $yy;
        if (!is_null($errorMode)) {
            $errorMode2 = $errorMode;
        } else {
            $errorMode2 = $this->errorMode;
        }
        
        $this->setExceptionReturnValues(0, '');
        $this->isDbException = false;
        try {
            $s5 = \yy::dbEscape($s, $params);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = static::BAD_DBESCAPE_PARAMS;
            if ($errorMode2 == static::ERROR_MODE_GOTO_PAGE) {
                \yy::gotoErrorPage(sprintf ("Error %s\n",  $message));
            } else {
                $this->setExceptionReturnValues($code, $message);
                if ($errorMode2 == static::ERROR_MODE_EXCEPTION) {
                    throw new \Exception($message, $code); // rethrow the exception
                }
            }
            
        }
        
        try {
        //$sth = $this->handle->query($s5);
        $affected = $this->handle->exec($s5);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if ($yy->settings2['extended_db_messages']) {
                $message = (\yy::dbEscape($s, $params) . chr(13) . chr(10) . 
                    $message);
            }
            if ($errorMode2 == static::ERROR_MODE_GOTO_PAGE) {
                \yy::gotoErrorPage(sprintf ("Error %s\n",  $message));
            } else {
                $this->setExceptionReturnValues($e->getCode(), $message);
                if ($errorMode2 == static::ERROR_MODE_EXCEPTION) {
                    $this->isDbException = true;
                    throw new \Exception($message, 1, $e); // rethrow the exception
                } else {
                    // just return, error is set
                    return 0;
                }
            }
        }
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
     * переходит к странице ошибки в случае ошибки
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

    /**
    выполняет запрос и возвращает одну строчку с полученным объектом из строки
    * @param string $s - строка запроса, в ней могут быть параметры вида $1,...
    * @param array $params - параметры, если есть
    * @param int $errorMode - temporary set errorMode for this call
    * @return object (или null если 0 записей)
    */
    function qobj($s, $params = [], int $errorMode = null) {
        global $yy;
        if (!is_null($errorMode)) {
            $errorMode2 = $errorMode;
        } else {
            $errorMode2 = $this->errorMode;
        }
        
        $this->setExceptionReturnValues(0, '');
        $this->isDbException = false;
        try {
            $s5 = \yy::dbEscape($s, $params);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = static::BAD_DBESCAPE_PARAMS;
            if ($errorMode2 == static::ERROR_MODE_GOTO_PAGE) {
                \yy::gotoErrorPage(sprintf ("Error %s\n",  $message));
            } else {
                $this->setExceptionReturnValues($code, $message);
                if ($errorMode2 == static::ERROR_MODE_EXCEPTION) {
                    throw new \Exception($message, $code); // rethrow the exception
                }
            }
            
        }
        
        try {
        $sth = $this->handle->query($s5);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if ($yy->settings2['extended_db_messages']) {
                $message = (\yy::dbEscape($s, $params) . chr(13) . chr(10) . 
                    $message);
            }
            if ($errorMode2 == static::ERROR_MODE_GOTO_PAGE) {
                \yy::gotoErrorPage(sprintf ("Error %s\n",  $message));
            } else {
                $this->setExceptionReturnValues($e->getCode(), $message);
                if ($errorMode2 == static::ERROR_MODE_EXCEPTION) {
                    $this->isDbException = true;
                    throw new \Exception($message, 1, $e); // rethrow the exception
                } else {
                    // just return, error is set
                    return (object)[];
                }
            }
        }

        $f = $sth->fetchObject();
        return ($f ? $f : null);
    }

    /**
     * mass nameEscape of an array
     * @param array $fields
     * @return array
     */
    function massNameEscape(array $fields) {
        $arr = \Alxnv\Nesttab\core\ArrayHelper::forArray($fields, 
                function($value) {
                    global $db;
                    return $db->nameEscape($value);
                });
        return $arr;
    }
            
    
    /**
    * Выполняет запрос и возвращает одну строчку с полученным массивом
    * @param string $s - строка запроса, в ней могут быть параметры вида $1,...
    * @param array $params - параметры, если есть
    * @param int $errorMode - temporary set errorMode for this call
    * @return array (или null если 0 записей)
    */
    function q($s, $params = [], int $errorMode = null) {
        global $yy;
        if (!is_null($errorMode)) {
            $errorMode2 = $errorMode;
        } else {
            $errorMode2 = $this->errorMode;
        }
        
        $this->setExceptionReturnValues(0, '');
        $this->isDbException = false;
        try {
            $s5 = \yy::dbEscape($s, $params);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = static::BAD_DBESCAPE_PARAMS;
            if ($errorMode2 == static::ERROR_MODE_GOTO_PAGE) {
                \yy::gotoErrorPage(sprintf ("Error %s\n",  $message));
            } else {
                $this->setExceptionReturnValues($code, $message);
                if ($errorMode2 == static::ERROR_MODE_EXCEPTION) {
                    throw new \Exception($message, $code); // rethrow the exception
                }
            }
            
        }
        
        try {
        $sth = $this->handle->query($s5);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if ($yy->settings2['extended_db_messages']) {
                $message = (\yy::dbEscape($s, $params) . chr(13) . chr(10) . 
                    $message);
            }
            if ($errorMode2 == static::ERROR_MODE_GOTO_PAGE) {
                \yy::gotoErrorPage(sprintf ("Error %s\n",  $message));
            } else {
                $this->setExceptionReturnValues($e->getCode(), $message);
                if ($errorMode2 == static::ERROR_MODE_EXCEPTION) {
                    $this->isDbException = true;
                    throw new \Exception($message, 1, $e); // rethrow the exception
                } else {
                    // just return, error is set
                    return [];
                }
            }
        }

        $f = $sth->fetch();
        return ($f ? $f : null);
    }
    
    /**
     * Выполнить команду sql insert для таблицы $tbl
     * @param string $tbl - имя таблицы
     * @param array $arr - массив вида 'поле' => 'значение'
     * @return string - '', если не было ошибок, иначе сообщение об ошибке
     * ----------------------@return mixed sth | null (null если была ошибка)
     */
    function insert(string $tbl, array $arr) {
        $arr2 = [];
        $arr3 = [];
        foreach ($arr as $key => $value) {
            $arr2[] = $this->nameEscape($key);
            $arr3[] = $this->escape($value);
        }
        $s2 = join(', ', $arr2);
        $s3 = join(', ', $arr3);
        $s = "insert into $tbl ($s2) values ($s3)";
        $res = $this->qdirect($s, [], static::ERROR_MODE_RETURN_ERROR);
        if ($this->errorCode == 0) {
            return '';
        } else {
            return $this->errorMessage;
        }
    }
    /**
     * Выполнить команду sql update для таблицы $tbl
     * @param string $tbl - имя таблицы
     * @param array $arr - массив вида 'поле' => 'значение'
     * @param string $postfix - эта строка добавляется в конце к команде update
     * return string - '' если не было ошибки, иначе сообщение об ошибке
     *      * @return mixed sth | null (null если была ошибка)
     */
    function update(string $tbl, array $arr, string $postfix) {
        $arr2 = [];
        foreach ($arr as $key => $value) {
            $arr2[] = $this->nameEscape($key) . '=' . $this->escape($value);
        }
        $s = join(', ', $arr2);
        
        $this->q("update $tbl set " . $s . ' ' . $postfix, [], static::ERROR_MODE_RETURN_ERROR);
        if ($this->errorCode == 0) {
            return '';
        } else {
            return $this->errorMessage;
        }
    }
    
    /**
     * Загружает все данные из таблицы yy_tables в суперглобальную переменную $td
     * @global type $td 
     *    $td['dat'] - данные таблицы
     *    $td['ind'] - индексы айдишников данных в 'dat'
     *    $td['cat'] - массив [p_id][id]
     * @global type $db
     */
    public function loadAllTablesData() {
        global $td, $db;
        $td = [];
        $td['dat'] = [];
        $td['ind'] = [];
        $td['cat'] = [];
        DB::table('yy_tables')->select('id','p_id','name','descr', 'table_type')->orderBy('p_id', 'asc')
                ->orderBy('descr', 'asc')->chunk(100,
                function($rows) {
                    $rows->each(function (object $item) {
                        global $td;
                        $td['ind'][$item->id] = count($td['dat']);
                        $td['dat'][] = [$item->id, $item->p_id, $item->name, $item->descr,
                            $item->table_type];
                        if (!isset($td['cat'][$item->p_id])) {
                            $td['cat'][$item->p_id] = [];
                        }
                        $td['cat'][$item->p_id][] = $item->id;
                    });
                });
        //while (false) {};
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