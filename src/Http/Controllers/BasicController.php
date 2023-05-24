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
        require(__DIR__ . '/../../core/yy.php');
        require(__DIR__ . '/../../core/blocks.php');
    }
}