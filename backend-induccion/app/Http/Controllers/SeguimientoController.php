<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use App\Models\VideoUserProgress;
use Illuminate\Http\Request;
use App\Models\DeclaracionJurada;

class SeguimientoController extends Controller
{
    // GET /api/admin/seguimiento/curso
    public function curso(Request $request)
    {
        // 1) Total de videos activos
        $totalVideos = Video::where('activo', true)->count();

        // 2) Progreso agrupado por usuario (TODO en PHP, nada de SQL raro)
        $progresosPorUsuario = VideoUserProgress::all()->groupBy('user_id');

        // 3) Lista de trabajadores
        //    Por ahora, TODOS los usuarios (luego filtramos por rol si quieres)
        $trabajadores = User::all();

        $lista = [];
        $contadores = [
            'no_iniciado' => 0,
            'en_progreso' => 0,
            'completado'  => 0,
        ];

        foreach ($trabajadores as $user) {
            // Colección de progresos de ESTE usuario (puede venir vacía)
            $progresosUser = $progresosPorUsuario->get($user->id, collect());

            // Contar solo los que tienen completado = true
            $completados = $progresosUser->where('completado', true)->count();

            // Última actividad: updated_at más reciente
            $ultima = $progresosUser->max('updated_at');

            // Porcentaje de avance
            $porcentaje = $totalVideos > 0
                ? round(($completados / $totalVideos) * 100)
                : 0;

            // Estado según avance
            if ($totalVideos === 0 || $completados === 0) {
                $estado = 'no_iniciado';
            } elseif ($completados < $totalVideos) {
                $estado = 'en_progreso';
            } else {
                $estado = 'completado';
            }

            $contadores[$estado]++;

            // TODO: aquí luego conectamos con la tabla de declaración jurada
            $declaracionFirmada = false;

            $lista[] = [
                'id'                  => $user->id,
                'nombre'              => $user->name,   // AJUSTA si tus campos se llaman diferente
                'correo'              => $user->email,  // idem
                'videos_completados'  => $completados,
                'total_videos'        => $totalVideos,
                'porcentaje'          => $porcentaje,
                'estado'              => $estado,
                'ultima_actividad'    => $ultima,
                'declaracion_firmada' => $declaracionFirmada,
            ];
        }

        return response()->json([
            'resumen' => [
                'total_trabajadores' => $trabajadores->count(),
                'total_videos'       => $totalVideos,
                'no_iniciado'        => $contadores['no_iniciado'],
                'en_progreso'        => $contadores['en_progreso'],
                'completado'         => $contadores['completado'],
            ],
            'trabajadores' => $lista,
        ]);
    }

    public function usuario(User $user)
{
    // 1) Videos activos
    $videos = Video::where('activo', true)
        ->orderBy('orden')
        ->get();

    $totalVideos = $videos->count();

    // 2) Progresos de este usuario, indexados por video_id
    $progresos = VideoUserProgress::where('user_id', $user->id)
        ->get()
        ->keyBy('video_id');

    // 3) Declaración jurada firmada de este usuario (usa firmado_at)
    $declaracion = DeclaracionJurada::where('user_id', $user->id)
        ->whereNotNull('firmado_at')
        ->latest('firmado_at')
        ->first();

    $declaracionFirmada = $declaracion && $declaracion->firmado_at !== null;
    $declaracionFecha   = $declaracion?->firmado_at;
    $declaracionTexto   = $declaracion?->texto_declaracion;

    // 4) Detalle de cada video
    $videosDetalle = [];
    $videosCompletados = 0;
    $ultimaActividad = null;

    foreach ($videos as $video) {
        $prog = $progresos->get($video->id);

        $completado      = $prog?->completado ? true : false;
        $segundosVistos  = $prog?->segundos_vistos ?? 0;
        $completadoAt    = $prog?->completado_at;
        $actualizadoAt   = $prog?->updated_at;

        if ($completado) {
            $videosCompletados++;
        }

        if ($actualizadoAt && (!$ultimaActividad || $actualizadoAt > $ultimaActividad)) {
            $ultimaActividad = $actualizadoAt;
        }

        $videosDetalle[] = [
            'id'                    => $video->id,
            'orden'                 => $video->orden,
            'titulo'                => $video->titulo,
            'descripcion'           => $video->descripcion,
            'duracion_segundos'     => $video->duracion_segundos,
            'segundos_vistos'       => $segundosVistos,
            'completado'            => $completado,
            'completado_at'         => $completadoAt,
            'ultima_actualizacion'  => $actualizadoAt,
        ];
    }

    $porcentaje = $totalVideos > 0
        ? round(($videosCompletados / $totalVideos) * 100)
        : 0;

    // Estado general igual que en curso(), usando firma
    if ($totalVideos === 0 || $videosCompletados === 0) {
        $estado = 'no_iniciado';
    } elseif ($videosCompletados < $totalVideos) {
        $estado = 'en_progreso';
    } else {
        $estado = $declaracionFirmada ? 'completado' : 'en_progreso';
    }

    return response()->json([
        'usuario' => [
            'id'     => $user->id,
            'nombre' => $user->name,
            'correo' => $user->email,
        ],
        'resumen' => [
            'total_videos'        => $totalVideos,
            'videos_completados'  => $videosCompletados,
            'porcentaje'          => $porcentaje,
            'estado'              => $estado,
            'ultima_actividad'    => $ultimaActividad,
            'declaracion_firmada' => $declaracionFirmada,
            'declaracion_fecha'   => $declaracionFecha,
            'declaracion_texto'   => $declaracionTexto,
        ],
        'videos' => $videosDetalle,
    ]);
}

}
