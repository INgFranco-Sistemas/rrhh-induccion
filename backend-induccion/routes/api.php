<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ProgresoController;
use App\Http\Controllers\DeclaracionTemplateController;
use App\Http\Controllers\DeclaracionJuradaController;
use App\Http\Controllers\SeguimientoController;

// Rutas públicas (sin autenticación)
Route::post('/auth/login', [AuthController::class, 'login']);

// (Opcional) permitir registro
// Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware('auth:api')->group(function () {

    // === AUTH ===
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);    
    
    Route::get('/declaracion-plantilla', [DeclaracionTemplateController::class, 'show']);
    // Todos los usuarios autenticados (admin + trabajador)
    Route::get('/videos', [VideoController::class, 'index']);
    Route::get('/videos/{video}', [VideoController::class, 'show']);
    
    // === PROGRESO ===
    Route::post('/videos/{video}/progreso', [ProgresoController::class, 'updateProgress']);
    Route::get('/curso/estado', [ProgresoController::class, 'estadoCurso']);
    
    // === DECLARACIÓN JURADA ===
    Route::post('/declaracion/firmar', [DeclaracionJuradaController::class, 'firmar']);
    Route::get('/declaracion/mia', [DeclaracionJuradaController::class, 'miDeclaracion']);
    Route::get('/admin/seguimiento/curso', [SeguimientoController::class, 'curso']);

    // === VIDEOS ===
    // Admin
    Route::middleware('role:admin')->group(function () {
        Route::post('/videos', [VideoController::class, 'store']);
        Route::post('/videos/{video}', [VideoController::class, 'update']);
        Route::delete('/videos/{video}', [VideoController::class, 'destroy']);

        Route::get('/admin/declaracion-plantilla', [DeclaracionTemplateController::class, 'show']);
        Route::post('/admin/declaracion-plantilla', [DeclaracionTemplateController::class, 'store']);
    });
});
