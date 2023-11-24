<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

$apiDomain = (in_array(env('APP_ENV'), ['production', 'uat']) ? env('APP_API_URL') : '');
$apiURL = (in_array(env('APP_ENV'), ['production', 'uat']) ? env('APP_API_VERSION')  : env('APP_LOCAL_API_VERSION')) . '/';

Route::get('login', function(Request $request) use ($apiDomain, $apiURL) {
    return response('You don\'t have access to the following resources.')
        ->header('Location', $apiDomain . $apiURL, false);
})->name('login');
