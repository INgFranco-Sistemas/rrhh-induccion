<?php

namespace App\Http\Controllers;

use App\Models\DeclaracionTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Djfirmados;
use App\Http\Controllers\Response;


class DeclaracionTemplateController extends Controller
{
    // GET /api/declaracion-plantilla  (para trabajador y admin)
    public function show()
    {
        $template = DeclaracionTemplate::latest()->first();
        $user = auth()->user();

        if (! $template) {
            return response()->json(null);
        }

        // verifica si existe el file firmado digitalmente
        $verificarDeclaracion = Djfirmados::where('iduser', $user->id)->orderBy('id', 'desc')->first();
            // Construir URL según exista firma o no
        $url = $verificarDeclaracion
        ? asset('djfirmados/'.$verificarDeclaracion->file_url)//Storage::disk('djfirmados')->url($verificarDeclaracion->file_url)
        : asset('storage/'.$template->file_path);
         

        //         $user = auth()->user();

        // $verificarDeclaracion = Djfirmados::where('iduser', $user->id)->orderBy('id', 'desc')->first();

        // $path = Storage::disk('djfirmados')->path($verificarDeclaracion->file_url);
        return response()->json([
            'id'        => $template->id,
            'nombre'    => $template->nombre,
            'file_path' => $template->file_path,
            'url'       => $url,
        ]);
    }

    // POST /api/admin/declaracion-plantilla (solo admin)
    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf|max:20480',
        ]);

        $file = $request->file('archivo');

        // Guardar en storage/app/public/declaraciones
        $path = $file->store('declaraciones', 'public');

        // Eliminar anterior si existe
        $anterior = DeclaracionTemplate::latest()->first();
        if ($anterior) {
            Storage::disk('public')->delete($anterior->file_path);
            $anterior->delete();
        }

        $template = DeclaracionTemplate::create([
            'nombre'    => $file->getClientOriginalName(),
            'file_path' => $path,
        ]);

        return response()->json([
            'message'  => 'Declaración jurada actualizada correctamente.',
            'template' => $template,
        ]);
    }

    public function muestrafile($filename)
{
    // Verificar si el archivo existe antes de obtener el path
    if (!Storage::disk('djfirmados')->exists($filename)) {
        abort(404, 'Archivo no encontrado');
    }

    $path = Storage::disk('djfirmados')->path($filename);

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . $filename . '"',
        // Evita problemas de caché si el archivo se llega a actualizar
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ]);
}
}
