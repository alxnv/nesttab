<?php
const CP9 = 'Alxnv\\Nesttab\\Http\\Controllers\\';

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
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

Route::prefix('nesttab')->group(function () {
    /*Route::get('/', function () {
            return 'Hello';
    });*/
    //Route::get('/ex', ['as' => 'ex',
      //              'uses' => 'ExController@show']);
    //dd(7);
    Route::get('/struct-table-edit-field/step2/{id}', CP9 . 'StructTableEditFieldController@step2');
    Route::get('/struct-table-edit-field/index/{id}/{prev?}', CP9 . 'StructTableEditFieldController@index');
    Route::get('/struct-change-table/edit/{id}/{prev?}', CP9 . 'StructChangeTableController@edit');
    Route::get('/change-struct-list', CP9 . 'ChangeStructListController');
    Route::get('/error', CP9 . 'ErrorController');
    Route::get('/message', CP9 . 'MessageController');
    Route::get('/', CP9 . 'IndexController@index');
    //Route::get('/not-req/{id?}', 'NotReqParamsController');
        //->whereNumber('id');
    //Route::resource('edit', 'EditController');
});
