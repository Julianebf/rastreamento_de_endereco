<?php

use App\Http\Controllers\EnderecoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EnderecoController::class, 'index']);
Route::post('/rastrear', [EnderecoController::class, 'store'])->name('endereco.store');