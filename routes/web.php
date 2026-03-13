<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\VideoCheckerController;

Route::get('/', [VideoCheckerController::class, 'index']);
Route::post('/video/process', [VideoCheckerController::class, 'process']);
