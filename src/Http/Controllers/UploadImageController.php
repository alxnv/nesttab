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
    public function __invoke() {
        
        Log::debug('111 An informational message.' . print_r($_FILES, true));

        return '111';
    }
}