<?php
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
/**
 * Вызывается из filepond при загрузке изображений на сервер
 */

class UploadFileController extends BasicController {
    /**
     * filepond передает $_POST['image'] - пустое значение
     * @return string
     */
    public function __invoke(Request $request) {
        global $yy, $db;
//        $r = $_GET;
//        $r2 = $_POST;
        if (!isset($_GET['file928357'])) \App::abort(404);
        $index = $_GET['file928357'];
        if (!isset($_GET['tbl'])) \App::abort(404);
        $tbl_id = $_GET['tbl'];
        if (!isset($_GET['rec'])) \App::abort(404);
        $rec_id = $_GET['rec'];
        $columns = $db->q("select parameters from yy_columns "
                . "where table_id = $1 and name = $2", [$tbl_id, $index]);
        if (is_null($columns)) \App::abort(404);
        $params = json_decode($columns['parameters']);
        

        //Log::debug('111 ' . print_r($index, true));
        //Log::debug('222 ' . print_r($_POST, true));
        //Log::debug('333 ' . print_r($_FILES[$index], true));
        $obj = new \Alxnv\Nesttab\Models\TokenUploadModel();

        if (!($token = $obj->createTokenDir())) \App::abort(404); // если запрещена 
           // запись в директорию upload
        if ($_FILES[$index]['error'] <> UPLOAD_ERR_OK) \App::abort(404); // ошибка при
           // загрузке файла
        $from = \yy::pathDefend($_FILES[$index]['tmp_name']);
        if (!$obj->moveFile($token, $from, $_FILES[$index]['name'])) {
                \App::abort(404);
                };
                
        return $token;
    }
    
    public function revert(Request $request) {
        //Log::debug('444 ' . print_r($request->all(), true));
        $body  = file_get_contents('php://input'); // get id of download (token)
        //Log::debug('555 ' . print_r($body, true));
        if (!\Alxnv\Nesttab\Models\TokenUploadModel::isValidToken($body))  \App::abort(404);
        
        $obj = new \Alxnv\Nesttab\Models\TokenUploadModel();
        $obj->deleteTokenDir($body);
        
    }
    
}