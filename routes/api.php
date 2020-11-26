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
Route::post('/user/change/status/{id_user}','UserController@changeStatus');

Route::get('/refresh/token/','UserController@refreshToken');
Route::get('/all/users','UserController@index');

Route::delete('/delete/user/{id}','UserController@delete');





//Rutas de Test
Route::get('/tests','TestController@index');
Route::get('/tests/{id_test}','TestController@show');
// Herrmann
Route::post('/create/herrmann','TestController@createHerrmann');
Route::post('/interpret/herrmann/{id_test}','TestController@interpretHerrmann');
// Maslow
Route::post('/create/maslow', 'TestController@createMaslow');
Route::post('add/activity/maslow/{id_test}', 'ActivityController@addActivityMaslow');
Route::get('/bluesky/maslow/{id_test}', 'TestController@combinateBlueSky');
Route::post('/confirm/bluesky/maslow/{id_test}', 'TestController@confirmBlueSky');
Route::get('/five/bluesky/maslow/{id_test}', 'TestController@getBlueSky');
Route::post('/complete/maslow/{id_test}', 'TestController@completeMaslow');
Route::get('/results/maslow/{id_test}', 'TestController@getResultsMaslow');

// Lienzo Propuesta - Valor
Route::post('/create/lienzo', 'TestController@createLienzo');
Route::post('/add/activity/lienzo/{id_test}', 'ActivityController@addActivityLienzo');
Route::get('/lienzo/information/{id_test}', 'TestController@getInformationLienzo');
Route::post('/save/lienzo/{id_test}', 'TestController@saveLienzo');


//Saca todos los tests por user Logueado
Route::get('/tests/by/user','TestController@getTestsByUser');
Route::delete('/delete/test/{id_test}','TestController@deleteTest');

//RUTAS ACTIVITY
// Herrmann
Route::post('/add/activity/{id_test}', 'ActivityController@addActivityHerrmann');
Route::post('/add/hemisphere/{id_test}','ActivityController@addHemisphereHerrmann');

///RUTAS ROLE
Route::post('/add/role/{id_user}/{name_role}','RoleController@addRole');
Route::delete('/delete/role/{id_user}/{name_role}','RoleController@deleteRole');
Route::get('/get/roles/{id_user}','RoleController@getRolesByUser');
