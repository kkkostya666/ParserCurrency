<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
});

Route::group(['prefix' => 'currency', 'middleware' => 'auth:api'], function () {
    Route::get('/currencies', [CurrencyController::class, 'index']);
    Route::get('/currencies/{code}', [CurrencyController::class, 'show']);
    Route::post('/convert', [CurrencyController::class, 'convert']);
});

