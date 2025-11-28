<?php

namespace App\Http\Controllers;

use App\Models\DeclaracionTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeclaracionTemplateController extends Controller
{
    // GET /api/declaracion-plantilla  (para trabajador y admin)
    public function show()
    {
        $template = DeclaracionTemplate::latest()->first();

        if (! $template) {
            return response()->json(null);
        }

        return response()->json([
            'id'        => $template->id,
            'nombre'    => $template->nombre,
            'file_path' => $template->file_path,
            'url'       => asset('storage/'.$template->file_path), // 👈 importante
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
}
