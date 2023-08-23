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
     * upload temporary file image
     * @param Request $request
     */
    public function restore(Request $request) {
        $token = $request->input('token');
        if (!\Alxnv\Nesttab\Models\TokenUploadModel::isValidToken($token))  \App::abort(404);
        $obj = new \Alxnv\Nesttab\Models\TokenUploadModel();
        $fn = $obj->getFileName($token);
        if ($fn === false)  \App::abort(404);
        if ($fn[1] == '')  \App::abort(404);
        $dir = public_path() . '/upload/temp/' . $token;
        $s2 = $dir . '/' . $fn[1]; // thumbnail
        if (!is_file($s2)) \App::abort(404);
        $contentType = mime_content_type($s2);
        if (!in_array($contentType, ['image/jpeg', 'image/png', 'image/gif'])) \App::abort(404);

        $img = file_get_contents($s2);
        return response($img)->header('Content-type',$contentType)->
                header('Content-Disposition', 'inline; filename="' . $fn[0] . '"');
        /*$c1 = file_get_contents($s2);
        header("Content-Type: " . $contentType);
        header('Content-Disposition: inline; filename="' . $fn[0] . '"');
        echo $c1;
        /*$r = $request->all();
        Log::debug('restore ' . print_r($r, true));*/
        
    }
    public function loadEx(Request $request) {
        //echo 'rrrrr';exit;
        /*$s2 = public_path() . '/abrak.jpg';
        $img = file_get_contents($s2);
return response($img)->header('Content-type','image/jpeg');
        //$c1 = file_get_contents($s2);
        header("Content-Type: image/jpeg");
        //header('Content-Length: ' . strlen($c1));
        header('Content-Disposition: inline; filename="abrak.jpg"');
        $tg = new \Alxnv\Nesttab\Models\ThumbnailGenerator;
        $tg->generate($s2, 100, 100);
//header('Content-Length: ' . filesize($s2));
//readfile($s2);
//exit;*/
        //echo $c1;
    }
    public function load(Request $request) {
        global $db, $yy;
        if (!$request->has('file928357')) \App::abort(404);
        $r = \Alxnv\Nesttab\core\StringHelper::splitByFirst('|', $request->input('file928357'));
        /*if (!$request->has('tbl')) \App::abort(404);
http://localhost/nesttab/public/nesttab/upload_image/load?tbl=73&rec=1&file=image1s|1/abrak.jpg
         *         $tbl_id = intval($request->input('tbl'));
        if (!$request->has('rec')) \App::abort(404);
        $rec_id = intval($request->input('rec'));
        $tbl = \Alxnv\Nesttab\Models\TablesModel::getOneAjax($tbl_id);
        $rec = $db->q("select $1 from $2 where id = $3", [$r[0], $tbl['name'], $rec_id]);
        if (is_null($rec)) \App::abort(404);*/
        $s = \yy::pathDefend2($r[1]);
        $f = \Alxnv\Nesttab\core\StringHelper::splitByFirst('/', $s);
        if ($f[0] == '') \App::abort(404);
        $s2 = public_path() . '/upload/' . $f[0] . '/1/' . $f[1]; // thumbnail
        if (!is_file($s2)) \App::abort(404);
        $contentType = mime_content_type($s2);
        if (!in_array($contentType, ['image/jpeg', 'image/png', 'image/gif'])) \App::abort(404);
        $img = file_get_contents($s2);
        return response($img)->header('Content-type',$contentType);

        /*$c1 = file_get_contents($s2);
        header("Content-Type: " . $contentType);
        //header('Content-Length: ' . strlen($c1));
        header('Content-Disposition: inline; filename="' . $f[1] . '"');
        echo $c1;
        /*$r2 = $request->all();
        Log::debug('load, file= ' . print_r($r, true));
        Log::debug('load, req= ' . print_r($r2, true));
         */
        
    }
    /**
     * filepond передает $_POST['image'] - пустое значение
     * @return string
     */
    public function __invoke(Request $request) {
        global $yy, $db;
        if (!$request->has('file928357')) \App::abort(404);
        $index = $request->input('file928357');
        if (!$request->has('tbl')) \App::abort(404);
        $tbl_id = $request->input('tbl');
        if (!$request->has('rec')) \App::abort(404);
        $rec_id = $request->input('rec');
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
        if (!$obj->moveAndCreateThumbnail($token, $from, $_FILES[$index]['name'],
                $params->iprm[1])) {
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