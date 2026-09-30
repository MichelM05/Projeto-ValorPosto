<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/calculadora/etanol-gasolina', [HomeController::class, 'ethanolGasoline'])->name('calc.ethanol_gasoline');
Route::get('/calculadora/vale-a-pena-desvio', [HomeController::class, 'detour'])->name('calc.detour');
Route::get('/historico', [HomeController::class, 'history'])->name('history');
