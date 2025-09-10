<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WalletController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

Route::prefix('v1/wallet')->group(function () {

    Route::post('/account', [WalletController::class, 'createAccount']);
    Route::post('/top-up', [WalletController::class, 'topUp']);
    Route::post('/charge', [WalletController::class, 'charge']);
    Route::get('/balance/{userId}', [WalletController::class, 'getBalance']);
    Route::get('/transactions/{userId}', [WalletController::class, 'getTransactions']);
});
