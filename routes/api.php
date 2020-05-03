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
Route::post('/user/upload/avatar','UserController@uploadImage');
Route::get('/user/download/avatar/{filename}','UserController@getImage');



//Rutas de Test
Route::resource('/test','TestController');
Route::post('/create/herrmann','TestController@createHerrmann');
Route::post('/interpret/herrmann/{id_test}','TestController@interpretHerrmann');
//Saca todos los tests por user Logueado
Route::get('/tests/by/user','TestController@getTestsByUser');

//RUTAS ACTIVITY
Route::post('/add/activity/{id_test}',
            'ActivityController@addActivityHerrmann');
///RUTAS ROLE
Route::post('/add/role/{id_user}/{name_role}','RoleController@addRole');
Route::delete('/delete/role/{id_user}/{name_role}','RoleController@deleteRole');
Route::get('/get/roles/{id_user}','RoleController@getRolesByUser');
