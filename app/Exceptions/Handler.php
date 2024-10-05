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

            // Registrar un log detallado con más información del error
            Log::channel('slack')->error('Error capturado', [
                'message'       => $e->getMessage(),             // Mensaje de error
                'exception'     => get_class($e),                // Tipo de excepción
                'url'           => $request->fullUrl(),          // URL del endpoint
                'method'        => $request->method(),           // Método HTTP usado
                'input'         => $request->all(),              // Parámetros del request
                'ip'            => $request->ip(),               // IP del cliente
                'file'          => $e->getFile(),                // Archivo donde ocurrió el error
                'line'          => $e->getLine(),                // Línea donde ocurrió el error
                'trace'         => $e->getTraceAsString(),       // Stack trace completo
            ]);
        });
    }
}
