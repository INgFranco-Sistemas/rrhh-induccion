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

    // 2) Progresos agrupados por user_id
    $progresosPorUsuario = VideoUserProgress::all()->groupBy('user_id');

    // 3) Declaraciones firmadas por user_id
    $declaracionesPorUsuario = DeclaracionJurada::whereNotNull('firmado_at')
        ->get()
        ->keyBy('user_id');

    // 4) Trabajadores ACTIVOS (tabla admin a través del modelo User)
    $trabajadores = User::query()
                        ->join('tram_dependencia as d', 'd.iddependencia', '=', 'admin.depe_id')
                        ->where('admin.adm_estado', 1)
                        ->where('d.depe_depende', 3)
                        ->get();
    $lista = [];
    $contadores = [
        'no_iniciado' => 0,
        'en_progreso' => 0,
        'completado'  => 0,
    ];

    foreach ($trabajadores as $user) {
        // Progresos de este usuario
        $progresosUser = $progresosPorUsuario->get($user->id, collect());

        $videosCompletados = $progresosUser->where('completado', true)->count();
        $ultimaActividad   = $progresosUser->max('updated_at');

        $porcentaje = $totalVideos > 0
            ? round(($videosCompletados / $totalVideos) * 100)
            : 0;

        // Declaración
        $declaracion = $declaracionesPorUsuario->get($user->id);
        $declaracionFirmada = $declaracion && $declaracion->firmado_at !== null;

        // Estado general
        if ($totalVideos === 0 || $videosCompletados === 0) {
            $estado = 'no_iniciado';
        } elseif ($videosCompletados < $totalVideos) {
            $estado = 'en_progreso';
        } else {
            $estado = $declaracionFirmada ? 'completado' : 'en_progreso';
        }

        $contadores[$estado]++;

        $lista[] = [
            'id'                  => $user->id,
            'nombre'              => trim($user->adm_name . ' ' . $user->adm_lastname),  // viene de adm_name + adm_lastname
            'correo'              => $user->adm_correo,  // viene de adm_correo/adm_email
            'videos_completados'  => $videosCompletados,
            'total_videos'        => $totalVideos,
            'porcentaje'          => $porcentaje,
            'estado'              => $estado,
            'ultima_actividad'    => $ultimaActividad,
            'declaracion_firmada' => $declaracionFirmada,
        ];
    }

    $search = trim($request->query('q', ''));

    if ($search !== '') {
        $lista = array_values(array_filter($lista, function ($item) use ($search) {
            $nombre = mb_strtolower($item['nombre'] ?? '', 'UTF-8');
            $correo = mb_strtolower($item['correo'] ?? '', 'UTF-8');
            $q      = mb_strtolower($search, 'UTF-8');

            return mb_stripos($nombre, $q, 0, 'UTF-8') !== false
                || mb_stripos($correo, $q, 0, 'UTF-8') !== false;
        }));
    }

    // -----------------------------
    //  PAGINACIÓN SOBRE $lista
    // -----------------------------
    $total   = count($lista);

    $page    = (int) $request->query('page', 1);
    $perPage = (int) $request->query('per_page', 25);

    if ($page < 1) {
        $page = 1;
    }
    if ($perPage < 1) {
        $perPage = 25;
    }

    $offset = ($page - 1) * $perPage;

    // Nos aseguramos de no salirnos del rango
    if ($offset > $total) {
        $offset = max(0, $total - $perPage);
        $page   = $offset > 0 ? (int) floor($offset / $perPage) + 1 : 1;
    }

    $trabajadoresPagina = array_slice($lista, $offset, $perPage);

    $pagination = [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $page,
        'last_page'    => $total > 0 ? (int) ceil($total / $perPage) : 1,
        'from'         => $total ? $offset + 1 : 0,
        'to'           => $total ? $offset + count($trabajadoresPagina) : 0,
    ];

    return response()->json([
        'resumen' => [
            'total_trabajadores' => $total,
            'total_videos'       => $totalVideos,
            'no_iniciado'        => $contadores['no_iniciado'],
            'en_progreso'        => $contadores['en_progreso'],
            'completado'         => $contadores['completado'],
        ],
        'trabajadores' => $trabajadoresPagina,
        'pagination'   => $pagination,
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
