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
                - self::oldIfCurrentTimeMinus * 60, self::timeSpan);
        $list = DB::select("select * from yy_tokens where time < ? order by time"
                . " limit 0,?", [date('Y-m-d H:i:s', $time), self::batchSize]);
        //echo \yy::ds($time);
        //dd($list);
        $this->_tokenList = $list;
        foreach ($list as $rec) {
            $this->deleteTokenDir($rec->token);
        }
        $this->deleteTokensFromDB();
    }
 
    /**
     * Удаляет директорию с токеном
     * @param string $token
     */   
    public function deleteTokenDir(string $token) {
        \Alxnv\Nesttab\core\FileHelper::deleteDir(public_path() . '/upload/temp/' . $token);
    }
    
    public function deleteTokensFromDB() {
        
    }
}  
