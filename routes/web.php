<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BahanController;

Route::get('/', function () {
    return view('welcome');
});


// Route bahan
Route::resource('bahan', BahanController::class);