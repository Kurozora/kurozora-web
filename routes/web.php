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
    return view('website.home', [
        'title' => 'Home',
        'stylesheets' => [
            asset('css/frontpage.css')
        ],
        'scripts' => [
            asset('js/frontpage.js')
        ]
    ]);
});

Route::get('/privacy', function () {
    return view('website.privacy', [
        'title' => 'Privacy policy',
        'scripts' => [
            asset('js/privacy_policy_page.js')
        ]
    ]);
});

Route::get('/login', function () {
    return view('website.login', [
        'title' => 'Login to your account',
        'scripts' => [
            asset('js/login.js')
        ],
        'hide_footer' => false
    ]);
});

Route::get('/confirmation/{confirmation_id}', 'UserController@confirmEmail');
Route::get('/reset/{reset_token}', 'UserController@resetPasswordPage');