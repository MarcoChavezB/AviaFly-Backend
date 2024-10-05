<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Capturar información relevante del request actual
            $request = request();

            // Inicializar un arreglo para la información del usuario
            $userInfo = null;

            // Verificar si el usuario está autenticado
            if ($request->user()) {
                $user = $request->user(); // Obtener el usuario autenticado
                $userInfo = [
                    'id' => $user->id,                     // ID del usuario
                    'name' => $user->name ?? 'N/A',        // Nombre del usuario
                    'email' => $user->email ?? 'N/A',      // Correo del usuario
                    'user_identification' => $user->user_identification ?? 'N/A', // ID de usuario personalizado
                    'user_type' => $user->user_type ?? 'N/A', // Tipo de usuario
                    // Puedes agregar más campos si es necesario
                ];
            }

            // Crear un mensaje de error detallado
            $errorMessage = sprintf(
                "Error capturado: %s\n" .
                "Detalles del Usuario:\n" .
                "ID: %s\n" .
                "Identificación: %s\n" .
                "Tipo de Usuario: %s\n" .
                "Email: %s\n" .
                "URL: %s\n" .
                "Método: %s\n" .
                "IP: %s\n" .
                "Entrada: %s\n" .
                "Archivo: %s\n" .
                "Línea: %d\n" .
                "Stack Trace: %s",
                $e->getMessage(),
                $userInfo['id'] ?? 'N/A',
                $userInfo['user_identification'],
                $userInfo['user_type'],
                $userInfo['email'],
                $request->fullUrl(),
                $request->method(),
                $request->ip(),
                json_encode($request->all()), // Para convertir el input en formato JSON
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );

            // Registrar el error en el log de Slack
            Log::channel('slack')->error($errorMessage);
        });
    }
}
