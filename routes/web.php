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

Route::get('/', function () {
    return view('welcome');
})->name('main');

//fusion auth
Route::get('/oauth2/{provider}/login', 'Auth\LoginController@redirectToProvider')->name('login');
Route::get('/oauth2/{provider}/login/callback', 'Auth\LoginController@handleProviderCallback');
Route::post('/oauth2/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/dashboard', 'HomeController@dashboard')->name('dashboard');
