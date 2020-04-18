<?php

use Illuminate\Http\Request;



// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('prueba','PruebasController@probar');

///Rutas de Usuario
Route::post('/register','UserController@register');
Route::post('/login','UserController@login');
Route::put('/user/update','UserController@update');
Route::get('/me','UserController@getUser');
//Rutas de Test
Route::resource('/test','TestController');
Route::post('/create/herrmann','TestController@createHerrmann');
Route::post('/interpret/test/{id_testk}','TestController@interpretTest');

Route::post('/add/activity/{id_test}',
            'ActivityController@addActivityHerrmann');