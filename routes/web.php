<?php

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

use \App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return view('welcome');
});

//INICIO RUTAS API
Route::post('/registro', 'GeneralController@registro');
Route::post('/login', 'GeneralController@login');
Route::post('/upload', 'GeneralController@upload');
Route::post('/guardar-pokemon', 'GeneralController@guardarPokemon');
Route::get('/traer-pokemon/{filename}', 'GeneralController@getImage');
Route::get('/nombre-pokemon/{filename}', 'GeneralController@nombreImg');
Route::post('/historial', 'GeneralController@historial');
Route::get('/historial-busqueda', 'GeneralController@baseHistorial');

//FIN RUTAS API
