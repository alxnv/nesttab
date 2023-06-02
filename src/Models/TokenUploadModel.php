<?php

/**
 * Модель для загрузки файлов на сервер в каталог upload/temp
 *   это файлы, загружаемые FileModel и ImageModel и сохраняемые в случае
 *    ошибки валидации данных как временные
 *   файлы хранятся в поддиректории upload/temp/<токен>,
 *    где токен - это 8-символьная hex строка
 *    файлы хранятся под своим оригинальным именем
 *   также, файлы (токены) хранятся в таблице БД 'yy_tokens' в виде 
 *     [токен, время создания токена] для отслеживания момента, когда их можно будет 
 *         удалять
 *     токены удаляются методом этого класса deleteOldTokens(), который вызывается
 *         практически во всех методах, вызываемых из web.php, в начале метода
 *         (кроме методов, где критично время исполнения)
 *   
 */

namespace Alxnv\Nesttab\Models;

use Illuminate\Support\Facades\DB;


class TokenUploadModel {
    
    
    /**
     * Считается что токены можно удалять, если время их создания - 
     *   текущее время минус это число * 60 (указано в минутах)
     */        
    const oldIfCurrentTimeMinus = 60;
    /**
     * time span для времени удаляемых токенов (в минутах)
     */
    const timeSpan = 10;
    /**
     * количество токенов, одновременно читаемых для удаления
     */
    const batchSize = 10; 
   
    /**
     * Сюда записывается массив токенов для удаления (из БД)
     * @var array
     */
    protected $_tokenList;

 /**
  * Удаляет устаревшие токены
 *     токены удаляются методом этого класса deleteOldTokens(), который вызывается
 *         практически во всех методах, вызываемых из web.php, в начале метода
 *         (кроме методов, где критично время исполнения)
 * 
 */
    public function deleteOldTokens() {
        $time = \Alxnv\Nesttab\core\DateTimeHelper::dateSpan(time()
                - static::oldIfCurrentTimeMinus * 60, static::timeSpan);
        $list = DB::select("select * from yy_tokens where time < ? order by time"
                . " limit 0,?", [date('Y-m-d H:i:s', $time), static::batchSize]);
        //echo \yy::ds($time);
        //dd($list);
        $this->_tokenList = $list;
        foreach ($list as $rec) {
            $this->deleteTokenDir($rec->token);
        }
        $this->deleteTokensFromDB();
    }
 
    /**
     * Удаляет директорию с токеном (токен проверяется на валидность)
     * @param string $token
     */   
    public function deleteTokenDir(string $token) {
        if (preg_match('/^[0-9a-f]{8}$/', $token)) {
            \Alxnv\Nesttab\core\FileHelper::deleteDir(public_path() . '/upload/temp/' . $token);
            $this->deleteFromDBToken($token);
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Получить имя файла, загруженного в каталог токена
     *  (в каталоге еще может быть файл вида thumbnail.{ext})
     * @param string $token
     * @return mixed string | boolean (имя файла, либо false если произошла ошибка)
     */
    public function getFileName(string $token) {
        $dir = public_path() . '/upload/temp/' . $token;
        try {
            $files = scandir($dir, SCANDIR_SORT_NONE);
        } catch (\Exception $ex) {
            return false;
        }
        if ($files === false) return false;
        foreach ($files as $file) {
            if (($file <> '.') && ($file <> '..')) {
                $pn = pathinfo($file);
                if ($pn['filename'] == 'thumbnail') continue;
                return $file;
            }
        }
        return false;        
    }
    
    /**
     * Delete obsolete tokens from DB
     */
    public function deleteTokensFromDB() {
        $list = $this->_tokenList;
        $arr = [];
        foreach ($list as $rec) {
            $arr[] = "'" . $rec->token . "'";
        }
        if (count($arr) > 0) {
            $s = join(', ', $arr);
            DB::delete("delete from yy_tokens where token in ($s)");
        }
    }
    
    public function deleteFromDBToken(string $token) {
        DB::delete("delete from yy_tokens where token = '$token'");
    }
    
    public function generateToken() {
        return bin2hex(random_bytes(4));
    }
    
    /**
     * Try to create token dir (upload/temp/{token}), where token like '9b02c5ff'
     * @return mixed string | boolean - token string or false, if permission denied
     *  creating directory
     */
    public function createTokenDir() {
        do {
            $token = $this->generateToken();
            $s = public_path(). '/upload/temp/' . $token;
            try {
                $b = mkdir($s, 0777, true);
            } catch (\Exception $ex) {
                $b = false;
            }
            if (!$b && !file_exists($s)) return false;
        } while (!$b);
        $this->addTokenToDB($token); // добавляет токен к базе данных для удаления
          //  в дальнейшем устаревших токенов
        return $token;
    }
    
    /**
     * Добавляем токен к БД для удаления в дальнейшем устаревших токенов
     * @param string $token - токен
     */
    public function addTokenToDB(string $token) {
        $s = "replace into yy_tokens (token, type_id, time) values ('$token', 1, current_timestamp())";
        DB::statement($s);
    }
}  
