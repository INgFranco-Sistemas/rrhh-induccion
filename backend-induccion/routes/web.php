<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirmaperuController as Firmaperu;

Route::get('/', function () {
    return view('welcome');
});

// firmaperu
Route::prefix('firmaperu')->group(function () {
    Route::post('parametros/{idFile}', [Firmaperu::class, 'parametros'])->name('firmaperu.parametros');
    Route::get('archivpdf/{idFile?}', [Firmaperu::class, 'printPdfR'])->name('firmaperu.printPdfFirma');
    Route::post('upload', [Firmaperu::class, 'upload'])->name('firmaperu.upload');
});
