<?php

namespace App\Http\Controllers;

use App\Models\DeclaracionJurada;
use App\Models\Video;
use App\Models\VideoUserProgress;
use Illuminate\Http\Request;

class DeclaracionJuradaController extends Controller
{
    // POST /api/declaracion/firmar
    public function firmar(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'texto_declaracion' => 'required|string',
        ]);

        // Verificar que completó todos los videos
        $totalVideos = Video::where('activo', true)->count();

        $videosCompletados = VideoUserProgress::where('user_id', $user->id)
            ->where('completado', true)
            ->count();

        if ($totalVideos === 0 || $videosCompletados !== $totalVideos) {
            return response()->json([
                'message' => 'No puede firmar la declaración. Aún no ha completado todos los videos.',
            ], 400);
        }

        $declaracion = DeclaracionJurada::create([
            'user_id'           => $user->id,
            'texto_declaracion' => $request->texto_declaracion,
            'ip_address'        => $request->ip(),
            'user_agent'        => $request->userAgent(),
            'firmado_at'        => now(),
        ]);

        return response()->json([
            'message'     => 'Declaración jurada firmada correctamente',
            'declaracion' => $declaracion,
        ], 201);
    }

    // GET /api/declaracion/mia
    public function miDeclaracion(Request $request)
    {
        $user = $request->user();

        $ultima = DeclaracionJurada::where('user_id', $user->id)
            ->orderBy('firmado_at', 'desc')
            ->first();

        if (!$ultima) {
            return response()->json([
                'message' => 'No tiene declaración jurada registrada',
            ], 404);
        }

        return response()->json($ultima);
    }
}
