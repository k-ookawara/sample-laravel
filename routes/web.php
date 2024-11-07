<?php

use App\Http\Controllers\BotController;
use App\Http\Controllers\HelloWorldController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/hello', [HelloWorldController::class, 'index']);
Route::post('/bot', [BotController::class, 'handleWebhook'])->withoutMiddleware(ValidateCsrfToken::class);;
