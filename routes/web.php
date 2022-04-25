<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\SubmitController;

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

// Route::get('/', function () {
//     return view('page');
// });
// Route::get('/{section}', [PagesController::class, 'showPage'])->defaults('page', false)->defaults('subpage', false)->where([
//     'section' => '[a-z0-9_-]+',
// ]);
// Route::get('/', function () {
//     return view('construction');
// });
Route::get('/', [PagesController::class, 'showOnePager'])->name('home');
Route::get('/#tickets', [PagesController::class, 'showOnePager'])->name('aanmelden');
Route::post('/submit-bestellen-form', [SubmitController::class, 'submitOrderForm']);
Route::get('/bestelling/{id}', [PagesController::class, 'showOnePagerCheckout'])->where(['id' => '[0-9]+']);