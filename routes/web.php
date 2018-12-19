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

Route::get('/api-doc', function() {
    return view('website.api', [
        'openapi_json_file' => asset('openapi.json'),
        'api_logo'          => asset('img/static/logo_xsm.png')
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

// Admin panel dashboard
Route::get('/admin', 'AdminPanelController@dashboardPage')
    ->name('admin_panel.dashboard')
    ->middleware('kurozora.adminpanelauth');

// Admin panel login
Route::get('/admin/login', function () {
    return view('admin_panel.login');
})->name('admin_panel.login')->middleware('kurozora.adminpanelguestsonly');

Route::post('/admin/login', 'AdminPanelController@login');

// Admin panel logout
Route::get('/admin/logout', 'AdminPanelController@logout')
    ->middleware('kurozora.adminpanelauth');

// Admin panel user
Route::get('/admin/users', 'AdminPanelController@usersPage')
    ->middleware('kurozora.adminpanelauth');

// Confirm email
Route::get('/confirmation/{confirmation_id}', 'UserController@confirmEmail');

// Reset password
Route::get('/reset/{reset_token}', 'UserController@resetPasswordPage');