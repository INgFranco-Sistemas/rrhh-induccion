<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeclaracionTemplateController;


Route::get('/', function () {
    return view('welcome');
});


Route::get('storage/djfirmados/{filename}', [DeclaracionTemplateController::class, 'muestrafile']);
