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

Route::post("/orderCreated", 'WebhookController@orderCreated');

Route::get('/', 'Auth\LoginController@showLoginForm');

Route::group(['middleware' => 'auth'], function () {
    Route::get("/products", 'BigCommerceController@index');
    Route::get("/orders", 'WebhookController@index');
    Route::get("/order/{order_id}", "DPDController@view_order");
    Route::get("/create/{order_id}", 'DPDController@create');
    Route::get("/create", 'DPDController@test_create');
    Route::get("/label/{order_id}/{pl_number}", 'DPDController@label_from_order');
    Route::get("/close", 'DPDController@test_close');
    Route::get("/labels", 'DPDController@labels');
    Route::get("/delete/{pl_number}", 'DPDController@delete_label');
    //    Route::get('/link1', function ()    {
//        // Uses Auth Middleware
//    });

    //Please do not remove this if you want adminlte:route and adminlte:link commands to works correctly.
    #adminlte_routes
});
