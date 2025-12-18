<?php

namespace App\Http\Controllers;

use App\Models\DeclaracionTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Djfirmados;
use App\Http\Controllers\Response;
use setasign\Fpdi\Fpdi;


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

        $pdfconvertido = asset('storage/pdf_generados/' . $user->adm_dni . '.pdf');
            // Construir URL según exista firma o no
        $url = $verificarDeclaracion
        ? asset('djfirmados/'.$verificarDeclaracion->file_url)
        : $pdfconvertido;//asset('storage/'.$template->file_path);
         

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

    public function actualizapdf(request $request)
    {

        $dni = $request->dni; // Asegúrate de que el campo se llame 'dni' en tu DB

        // 2. Ruta del PDF original (plantilla)
        $template = DeclaracionTemplate::latest()->first();
        $pathOriginal = storage_path('app/public/' . $template->file_path);
        
        // 3. Configurar FPDI
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($pathOriginal);

        // Importar la primera página
        $templateId = $pdf->importPage(1);
        $pdf->addPage();
        $pdf->useTemplate($templateId);

        // 4. Escribir contenido sobre el PDF
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(0, 0, 0); // Color negro

        // Ejemplo: Escribir el nombre del usuario en coordenadas X, Y (en mm)
        // Debes ajustar 50, 50 según tu formato
        $pdf->SetXY(50, 47); 
        $pdf->Write(0, utf8_decode($request->nombre));

        $pdf->SetXY(80, 56); 
        $pdf->Write(0, utf8_decode($request->dni));

        $fecha= date('d/m/Y');
        $pdf->SetXY(50, 125); 
        $pdf->Write(0, utf8_decode($fecha));

        // 5. Definir el nombre del archivo y la ruta de guardado
        $nombreArchivo = "{$dni}.pdf";
        $rutaDestino = "pdf_generados/{$nombreArchivo}";

        // 6. Generar el contenido del PDF y guardarlo en Storage
        $pdfOutput = $pdf->Output('S'); // 'S' devuelve el documento como string
        Storage::disk('public')->put($rutaDestino, $pdfOutput);

        return response()->json([
            'mensaje' => 'Archivo guardado con éxito',
            'archivo' => $nombreArchivo
        ]);    
    }
}
