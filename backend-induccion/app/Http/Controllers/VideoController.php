<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    // Admin: crear video y subir archivo mp4
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string',
            'orden'             => 'required|integer|min:1',
            'duracion_segundos' => 'nullable|integer|min:1',
            'video'             => 'required|file|mimetypes:video/mp4|max:204800', // ~200MB
        ], [
            'video.mimetypes'   => 'El archivo debe ser un video MP4',
            'video.max'         => 'El tamaño máximo permitido es de 200MB',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Guardar archivo en storage/app/public/videos
        $filePath = $request->file('video')->store('videos', 'public');

        $video = Video::create([
            'titulo'            => $request->titulo,
            'descripcion'       => $request->descripcion,
            'orden'             => $request->orden,
            'duracion_segundos' => $request->duracion_segundos,
            'file_path'         => $filePath,
            'activo'            => true,
        ]);

        return response()->json([
            'message' => 'Video creado correctamente',
            'video'   => $video,
        ], 201);
    }

    // Admin: actualizar datos del video (no necesariamente el archivo)
    public function update(Request $request, Video $video)
    {
        $validator = Validator::make($request->all(), [
            'titulo'            => 'sometimes|required|string|max:255',
            'descripcion'       => 'nullable|string',
            'orden'             => 'sometimes|required|integer|min:1',
            'duracion_segundos' => 'nullable|integer|min:1',
            'activo'            => 'nullable|boolean',
            'video'             => 'nullable|file|mimetypes:video/mp4|max:204800',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Actualizar archivo de video si viene uno nuevo
        if ($request->hasFile('video')) {
            // borrar anterior
            if ($video->file_path && Storage::disk('public')->exists($video->file_path)) {
                Storage::disk('public')->delete($video->file_path);
            }
            $video->file_path = $request->file('video')->store('videos', 'public');
        }

        $video->fill($validator->validated());
        $video->save();

        return response()->json([
            'message' => 'Video actualizado correctamente',
            'video'   => $video,
        ]);
    }

    // Admin: eliminar video
    public function destroy(Video $video)
    {
        if ($video->file_path && Storage::disk('public')->exists($video->file_path)) {
            Storage::disk('public')->delete($video->file_path);
        }

        $video->delete();

        return response()->json(['message' => 'Video eliminado correctamente']);
    }

    // Trabajador / Admin: listar videos en orden
    public function index()
    {
        $videos = Video::where('activo', true)
            ->orderBy('orden')
            ->get();

        return response()->json($videos);
    }

    // Ver detalle de un video (incluye ruta del archivo)
    public function show(Video $video)
    {
        return response()->json($video);
    }
}
