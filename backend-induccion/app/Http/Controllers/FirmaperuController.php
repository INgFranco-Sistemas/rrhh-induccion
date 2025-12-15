<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeclaracionTemplate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\Djfirmados;

class FirmaperuController extends Controller
{
    public $token_firma_peru;
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        //
    }

  public function printPdfR($idFile = 0)
    {
        // Obtiene la ruta absoluta del archivo en el disk 'tramite'
        // $file = Storage::disk('djfirmados')->path('demo.pdf');
        $declaracion = DeclaracionTemplate::find($idFile);
        // asset('storage/'.$template->file_path)
        $file = storage_path('app/public/' . $declaracion->file_path);

        $headers = [
            'Content-Type' => 'application/pdf',
        ];

        return response()->file($file, $headers);
    }

  public function parametros($idFile,$cargo,$iduser)
    {
        $cargo = str_replace("_", " ", $cargo);
        
        $paramfirma = '{
            "signatureFormat": "PAdES",
            "signatureLevel": "B",
            "signaturePackaging": "enveloped",
            "documentToSign":"'.route('firmaperu.printPdfFirma', [$idFile]).'",
            "certificateFilter": ".*",
            "webTsa": "",
            "userTsa": "",
            "passwordTsa": "",
            "theme": "oscuro",
            "visiblePosition": true,
            "contactInfo": "",
            "signatureReason": "Soy el autor de este documento",
            "bachtOperation": false,
            "oneByOne": true,
            "signatureStyle": 1,
            "imageToStamp":"'.asset('/img/firmadigital2.png').'",
            "stampTextSize": 14,
            "stampWordWrap": 37,
            "role": "' . $cargo . '",
            "stampPage": 1,
            "positionx": 20,
            "positiony": 20,
            "uploadDocumentSigned":"'.route('firmaperu.upload',[$iduser]).'",
            "certificationSignature": false,
            "token":"' . $this->getTokenFirmaPeruCached() . '"
         }';

        return response(
            base64_encode(
                $paramfirma
            ),
            200
        )
            ->header("Access-Control-Allow-Origin",  "*")
            ->header('Content-Type', 'text/html; charset=UTF-8');

        // return $this->getTokenFirmaPeruCached();
    }
    public function getTokenFirmaPeru()
    {
        $curl = curl_init();
        $fields = array('client_id' => 'p99VWXJ1tzIwNDg5MjUwNzMxJ-hafwePDg', 'client_secret' => 'sD-b136fUs7OXA2-omAS6e-yvvR1t1_ZB6Y');
        $fields_string = http_build_query($fields);
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://apps.firmaperu.gob.pe/admin/api/security/generate-token',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_POSTFIELDS => $fields_string,
                CURLOPT_POST => 1
            )
        );
        return curl_exec($curl);
    }
    
    public function getTokenFirmaPeruCached()
    {
        //86400 minutos comprenden 24 horas Laravel 5.7 hacia atras
        //5184000 segundos comprenden 24 horas Laravel 5.8 en adelante
        return $this->token_firma_peru = Cache::remember('token_firma_peru', 86400, function () {
            return $this->getTokenFirmaPeru();
        });
    }
    public function upload(Request $request)
    {
        if ($request->hasFile('signed_file'))
        {

            //get filename with extension
            $file = $request->file('signed_file');//->getClientOriginalName();


            $fileName= time().'-'.$file->getClientOriginalName();
            // Storage::disk()->putFileAs('tramite', $file, $fileName); // SE GUARDA EN EL STORAGE

            $filesystem = Storage::disk('djfirmados');
            $filesystem->putFileAs($file, $fileName);

            $firmados = new Djfirmados();
            $firmados->iduser = $request->iduser; // 👈 viene en el body
            $firmados->file_name = $file->getClientOriginalName();
            $firmados->file_url = $fileName;
            $firmados->save();


            return 'correcto';   
        }
    }
}
