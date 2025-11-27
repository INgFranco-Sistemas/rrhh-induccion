<?php

namespace App\Http\Controllers;

use App\Models\DeclaracionTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeclaracionTemplateController extends Controller
{
    // GET /api/admin/declaracion-plantilla
    public function show()
    {
        $template = DeclaracionTemplate::latest()->first();

        return response()->json($template);
    }

    // POST /api/admin/declaracion-plantilla
    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf|max:20480', // máx ~20MB
        ]);

        $file = $request->file('archivo');

        // Guardamos en storage/app/public/declaraciones
        $path = $file->store('declaraciones', 'public');

        // Borramos el archivo anterior si existiera
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
