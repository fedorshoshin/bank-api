<?php

use App\Http\Controllers\OperationsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/balance/{id}', [OperationsController::class, 'balance']);
Route::post('/deposit', [OperationsController::class, 'transaction']);
Route::post('/withdraw', [OperationsController::class, 'transaction']);
Route::post('/transfer', [OperationsController::class, 'transfer']);
