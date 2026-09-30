<?php

use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\StationController;
use Illuminate\Support\Facades\Route;

Route::get('/stations', [StationController::class, 'index']);
Route::get('/stations/{station}', [StationController::class, 'show']);
Route::get('/history', [HistoryController::class, 'index']);
