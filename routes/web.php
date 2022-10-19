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
    Route::get('/', CP9 . 'IndexController@index');
    //Route::get('/not-req/{id?}', 'NotReqParamsController');
        //->whereNumber('id');
    //Route::resource('edit', 'EditController');
});
