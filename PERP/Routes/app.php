<?php

namespace PERP\Routes;

use Illuminate\Support\Facades\Route;
use PERP\Auth\Controllers\AuthController;
use PERP\Task\Controllers\TaskController;

$apiDomain = (in_array(env('APP_ENV'), ['production', 'uat']) ? env('APP_API_URL') : '');
$apiURL = (in_array(env('APP_ENV'), ['production', 'uat']) ? env('APP_API_VERSION')  : env('APP_LOCAL_API_VERSION')) . '/';

/**
 * Unauthenticated
 */
Route::group(
    [
        'domain' => $apiDomain,
        'prefix' => $apiURL,
        'middleware' => [
            'cors',
            'json.response'
        ]
    ],
    function () {

        Route::post('login', [AuthController::class, 'login']);

        Route::group(['prefix' => 'auth',], function () {
            Route::post('login', [AuthController::class, 'login']);
            Route::post('register', [AuthController::class, 'register']);

        });
    }
);

/**
 * Authenticated Group
 */
Route::group(
    [
        'domain' => $apiDomain,
        'prefix' => $apiURL,
        'middleware' => [
            'auth:sanctum',
            'throttle:500|2200,1',
            'cors',
            'json.response',
        ],
    ],
    function () {

        Route::group(['prefix' => 'auth',], function () {
            Route::get('logout', [AuthController::class, 'logout']);
            Route::get('user', [AuthController::class, 'user']);
        });

        /**
         * Tasks Page
         */
        Route::get('tasks', [TaskController::class, 'index']);
        Route::post('tasks', [TaskController::class, 'store']);
        Route::get('tasks/{taskId}', [TaskController::class, 'show']);
        Route::put('tasks/{taskId}', [TaskController::class, 'update']);
        Route::delete('tasks/{taskId}', [TaskController::class, 'destroy']);
    }
);
