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
        $user = $request->iduser;

        $request->validate([
            'texto_declaracion' => 'required|string',
        ]);

        // 1. Verificar que el usuario completó todos los videos activos
        $totalVideos = Video::where('activo', true)->count();

        $videosCompletados = VideoUserProgress::where('user_id', $user)
            ->where('completado', true)
            ->count();

        if ($totalVideos === 0 || $videosCompletados !== $totalVideos) {
            return response()->json([
                'message' => 'No puede firmar la declaración. Aún no ha completado todos los videos.',
            ], 400);
        }

        // 2. (Opcional) impedir múltiples declaraciones
        /*
        $yaTiene = DeclaracionJurada::where('user_id', $user->id)->first();
        if ($yaTiene) {
            return response()->json([
                'message' => 'Ya tiene una declaración jurada registrada.',
                'declaracion' => $yaTiene,
            ], 200);
        }
        */

        // 3. Registrar la declaración
        $declaracion = DeclaracionJurada::create([
            'user_id'           => $user,
            'texto_declaracion' => $request->texto_declaracion,
            'ip_address'        => $request->ip(),
            'user_agent'        => $request->userAgent(),
            'firmado_at'        => now(),
        ]);

        return response()->json([
            'message'     => 'Declaración jurada firmada correctamente.',
            'declaracion' => $declaracion,
        ], 201);
    }

    // GET /api/declaracion/mia  (por si luego quieres ver la última declaración)
    public function miDeclaracion(Request $request)
    {
        $user = $request->user();

        $ultima = DeclaracionJurada::where('user_id', $user->id)
            ->orderBy('firmado_at', 'desc')
            ->first();

        if (! $ultima) {
            return response()->json([
                'message' => 'No tiene declaración jurada registrada.',
            ], 404);
        }

        return response()->json($ultima);
    }
}
