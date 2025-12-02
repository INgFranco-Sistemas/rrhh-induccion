<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use App\Models\VideoUserProgress;
use Illuminate\Http\Request;

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
}
