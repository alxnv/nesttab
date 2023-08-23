<?php
const CP9 = 'Alxnv\\Nesttab\\Http\\Controllers\\';

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are floaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

*/

/*    Route::get('/nesttab', function () {
            return 'Hello';
    });
*/

// Route for not secret pages
//  todo: go to not secret place to enter login password if not logged in
//  todo: make not secret message and error page (if needed) - all links on that pages
//    must be not secret
Route::prefix(config('nesttab.uurl'))->group(function () {
});

// Route for admin system. Maybe secret
Route::prefix(config('nesttab.nurl'))->group(function () {
    /*Route::get('/', function () {
            return 'Hello';
    });*/
    //Route::get('/ex', ['as' => 'ex',
      //              'uses' => 'ExController@show']);
    //dd(7);
    
    /**
     * руты для уровня пользователя "модератор"
     */
    Route::get('/error', CP9 . 'ErrorController');
    Route::get('/message', CP9 . 'MessageController');
    Route::delete('/upload_image/revert', CP9 . 'UploadImageController@revert');
    Route::get('/upload_image/load', CP9 . 'UploadImageController@load');
    Route::get('/upload_image/restore', CP9 . 'UploadImageController@restore');
    Route::post('/upload_image', CP9 . 'UploadImageController');
    Route::delete('/upload_file/revert', CP9 . 'UploadFileController@revert');
    Route::get('/upload_file/load', CP9 . 'UploadFileController@load');
    Route::get('/upload_file/restore', CP9 . 'UploadFileController@restore');
    Route::post('/upload_file', CP9 . 'UploadFileController');
    Route::post('/edit/save_one/{id}', CP9 . 'EditController@saveOne');
    Route::get('/edit/{id}', CP9 . 'EditController@index');
    Route::get('/', CP9 . 'IndexController@index');
    
    

    /**
     * руты для уровня пользователя "администратор"
     */
    //Route::get('/upl', CP9 . 'UploadImageController@loadEx');
    Route::get('/tests', CP9 . 'TestsController@index');
    Route::get('/tests/ajax_infinite_sql', CP9 . 'TestsAjaxController@infiniteSql');
    Route::get('/tests/ajax_infinite_run_sql', CP9 . 'TestsAjaxController@infiniteSqlRun');
    Route::get('/tests/ajax_infinite_sql_make_table', CP9 . 'TestsAjaxController@infiniteSqlMakeTable');
    Route::get('/tests/ajax_infinite', CP9 . 'TestsAjaxController@infinite');
    Route::get('/tests/ajax_infinite_run', CP9 . 'TestsAjaxController@infiniteRun');
    Route::get('/struct-add-table', CP9 . 'StructAddTableController@index');
    Route::get('/struct-add-table/step22', CP9 . 'StructAddTableController@step22');
    Route::post('/struct-table-edit-field/save/{id}', CP9 . 'StructTableEditFieldController@save');
    //Route::get('/struct-table-edit-field/step2/{id}', CP9 . 'StructTableEditFieldController@step2');
    Route::get('/struct-table-edit-field/step2/{id}/{parm}', CP9 . 'StructTableEditFieldController@step2');
    Route::get('/struct-table-edit-field/index/{id}/{prev?}', CP9 . 'StructTableEditFieldController@index');
    Route::get('/struct-change-table/edit/{id}/{prev?}', CP9 . 'StructChangeTableController@edit');
    Route::get('/struct-change-table/delete/{id}', CP9 . 'StructChangeTableController@delete');
    Route::get('/struct-change-table/move/{tbl_id}/{id}/moveto/{pos}', CP9 . 'StructChangeTableController@move');
    Route::get('/change-struct-list', CP9 . 'ChangeStructListController');
    //Route::get('/not-req/{id?}', 'NotReqParamsController');
        //->whereNumber('id');
    //Route::resource('edit', 'EditController');
});
