<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WhaController extends Controller
{
    public function envia()
    {
        // Token generado desde la API de Facebook
        $token = 'EAALakzmgN60BOZBEHffxpMZCHiwOd2uZCsClRHcVWA1UvyVKhEhgL05ZAtqrW5vtRZC742Ls9K3gvbYDynZCHQ6awpZCuciZBb7y14TxFZBzHQKRRAcHZCArpZCV4LZBLimL3XGr4EmwKpIrMY9EARSGIIJkAExhOGpfZC6rnEbzrEsBh1f1I6bh3fgR1Lt6ZA5eQTVcgYJDj8lekDpg3OBQwlb1ZC7Yp0otIAjMyVtAVwDgRzvUiIE9tymkdRx';

        // Número de teléfono del receptor (incluyendo código de país, pero sin el "+")
        $telefono = '526242647089';

        // URL del endpoint de la API
        $url = 'https://graph.facebook.com/v21.0/453142977893192/messages';

        // Mensaje personalizado
        $mensaje = '
        {
            "messaging_product": "whatsapp",
            "to": "' . $telefono . '",
            "type": "text",
            "text": {
                "body": "Hola, este es un mensaje personalizado de notificación de tu aplicación. ¡Gracias por usar nuestro servicio!"
            }
        }';

        // Cabeceras
        $header = array(
            "Authorization: Bearer " . $token,
            "Content-Type: application/json"
        );

        // Inicializamos CURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);

        // Ejecutamos la solicitud y capturamos la respuesta
        $response = json_decode(curl_exec($curl), true);

        // Obtenemos el código de estado HTTP
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Cerramos la conexión CURL
        curl_close($curl);

        // Imprimimos la respuesta y el código de estado (puedes manejarlo según tus necesidades)
        echo "HTTP Status Code: " . $status_code . "\n";
        print_r($response);

        // Puedes retornar la respuesta si es necesario
        return response()->json(['status_code' => $status_code, 'response' => $response]);
    }
}
