<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::prefix('auth')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('register', 'register')->name('auth.register');
        Route::post('login', 'login')->name('auth.login');
        Route::post('logout', 'logout')->middleware('auth:api')->name('auth.logout');
    });
});

Route::middleware('auth:api')->group(function () {
    Route::controller(UserController::class)->group(function () {
        
        // Admin Routes
        Route::middleware(AdminMiddleware::class)->group(function () {
            Route::get('users', 'index')->name('users.index');
        });
    });
});
