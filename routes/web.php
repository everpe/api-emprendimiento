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
// Rutas para probar Orm y demás
Route::get('/', function () {
});
Route::get('/prueba','PruebasController@probar');
//-ESTAS RUTAS LUEGO LAS DEBO PASAR A LAS DE API
Route::post('api/register','UserController@register');
Route::post('api/login','UserController@login');
Route::put('api/user/update','UserController@update');