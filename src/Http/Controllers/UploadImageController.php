<?php
namespace Alxnv\Nesttab\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
/**
 * Вызывается из filepond при загрузке изображений на сервер
 */

class UploadImageController extends BasicController {
    /**
     * filepond передает $_POST['image'] - пустое значение
     * @return string
     */
    public function __invoke(Request $request) {
        
        if (!$request->has('file')) \App::abort(404);
        $index = $request->input('file');
        Log::debug('111 ' . print_r($index, true));
        Log::debug('222 ' . print_r($_POST, true));
        Log::debug('333 ' . print_r($_FILES[$index], true));
        $obj = new \Alxnv\Nesttab\Models\TokenUploadModel();

        if (!($token = $obj->createTokenDir())) \App::abort(404); // если запрещена 
           // запись в директорию upload
        if ($_FILES[$index]['error'] <> UPLOAD_ERR_OK) \App::abort(404); // ошибка при
           // загрузке файла
        $from = $_FILES[$index]['tmp_name'];
        $to = public_path() . '/upload/temp/' . $token . '/' . $_FILES[$index]['name'];
        move_uploaded_file($from, $to);
        return $token;
    }
    
    public function revert(Request $request) {
        Log::debug('444 ' . print_r($request->all(), true));
        $body  = file_get_contents('php://input'); // get id of download (token)
        Log::debug('555 ' . print_r($body, true));
        $obj = new \Alxnv\Nesttab\Models\TokenUploadModel();
        $obj->deleteTokenDir($body);
        
    }
    
}