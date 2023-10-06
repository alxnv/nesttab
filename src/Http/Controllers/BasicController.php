<?php
namespace Alxnv\Nesttab\Http\Controllers;

use App\Http\Controllers\Controller;

class BasicController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function __construct()
    {
        //dd(vendor_path());
        global $yy, $blocks;
        if (!isset($yy)) {
            require(__DIR__ . '/../../core/yy.php');
            require(__DIR__ . '/../../core/blocks.php');
        }
    }
    
    public function maintain() {
        // поддержка работоспособности сервера
        
        // удаление устаревших токенов
        if (rand(1,5) == 1) {
            $tm = new \Alxnv\Nesttab\Models\TokenUploadModel();
            $tm->deleteOldTokens();
        };
    }
}