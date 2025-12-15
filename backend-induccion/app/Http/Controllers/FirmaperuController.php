<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        $file = Storage::disk('djfirmados')->path('demo.pdf');

        $headers = [
            'Content-Type' => 'application/pdf',
        ];

        return response()->file($file, $headers);
    }
     public function parametros($idFile)
    {

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
            "imageToStamp":"http://127.0.0.1:8080/img/firmadigital2.png",
            "stampTextSize": 14,
            "stampWordWrap": 37,
            "role": "Analista de servicios",
            "stampPage": 1,
            "positionx": 20,
            "positiony": 20,
            "uploadDocumentSigned":"'.route('firmaperu.upload').'",
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


            $fileName= 'djfirmados/'.time().'-'.$file->getClientOriginalName();
            // Storage::disk()->putFileAs('tramite', $file, $fileName); // SE GUARDA EN EL STORAGE

            $filesystem = Storage::disk('tramite');
            $filesystem->putFileAs($file, $fileName);



            // para guardar en BD
            // $ubiacionfile='firmados/'.$nombre_a;


            return 'correcto';
        }
    }
}
