<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoUserProgress;
use Illuminate\Http\Request;

class ProgresoController extends Controller
{
    // Actualizar progreso de un video específico
    // Ruta: POST /api/videos/{video}/progreso
    public function updateProgress(Request $request, Video $video)
    {
        $user = $request->user();

        $request->validate([
            'segundos_vistos' => 'required|integer|min:0',
            'completado'      => 'required|boolean',
        ]);

        // Si quiere marcar como completado, validar que el anterior esté completo
        if ($request->boolean('completado')) {
            $videoAnterior = Video::where('orden', '<', $video->orden)
                ->where('activo', true)
                ->orderBy('orden', 'desc')
                ->first();

            if ($videoAnterior) {
                $progresoAnterior = VideoUserProgress::where('user_id', $user->id)
                    ->where('video_id', $videoAnterior->id)
                    ->where('completado', true)
                    ->first();

                if (!$progresoAnterior) {
                    return response()->json([
                        'message' => 'Debe completar el video anterior antes de marcar este como completado',
                    ], 403);
                }
            }
        }

        $progreso = VideoUserProgress::firstOrNew([
            'user_id'  => $user->id,
            'video_id' => $video->id,
        ]);

        $progreso->segundos_vistos = max($progreso->segundos_vistos, $request->segundos_vistos);

        if ($request->boolean('completado')) {
            $progreso->completado = true;
            $progreso->completado_at = now();
        }

        $progreso->save();

        // Ver si ya completó todos los videos
        $totalVideos = Video::where('activo', true)->count();
        $videosCompletados = VideoUserProgress::where('user_id', $user->id)
            ->where('completado', true)
            ->count();

        $puedeFirmar = ($totalVideos > 0) && ($videosCompletados === $totalVideos);

        return response()->json([
            'message'        => 'Progreso actualizado',
            'progreso'       => $progreso,
            'puede_firmar'   => $puedeFirmar,
            'total_videos'   => $totalVideos,
            'completados'    => $videosCompletados,
        ]);
    }

    // Estado general del curso para el usuario logueado
    // GET /api/curso/estado
    // Estado general del curso para el usuario logueado
// GET /api/curso/estado
public function estadoCurso(Request $request)
{
    $user = $request->user();

    // Traemos todos los videos del curso ordenados
    $videos = Video::where('activo', true)
        ->orderBy('orden')
        ->get();

    // Traemos el progreso del usuario
    $progresos = VideoUserProgress::where('user_id', $user->id)
        ->get()
        ->keyBy('video_id'); // para acceder rápido por ID

    $videosEstado = [];
    $anteriorCompletado = true; // el primer video siempre va desbloqueado

    foreach ($videos as $video) {
        $prog = $progresos->get($video->id);

        $completado = $prog?->completado ?? false;

        $videosEstado[] = [
            'id'                => $video->id,
            'titulo'            => $video->titulo,
            'descripcion'       => $video->descripcion,
            'orden'             => $video->orden,
            'duracion_segundos' => $video->duracion_segundos,
            'file_path'         => $video->file_path,
            'url'               => $video->url,      // por si lo usas en el front
            'completado'        => $completado,
            'bloqueado'         => !$anteriorCompletado, // 👈 AQUÍ EL BLOQUEO
        ];

        // Para el siguiente video: solo estará desbloqueado si ESTE está completado
        $anteriorCompletado = $completado;
    }

    // Revisamos si ya terminó todo
    $todosCompletos = collect($videosEstado)->every(fn ($v) => $v['completado']);

    return response()->json([
        'videos'              => array_values($videosEstado),
        'puede_firmar'        => $todosCompletos,
        'declaracion_firmada' => false,
        'declaracion'         => null,
    ]);
}

}
