<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Cambio de Estado de Vuelo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .card {
            max-width: 800px;
            width: 100%;
            background: #fff;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            padding: 40px;
            box-sizing: border-box;
            margin: 0 auto;
        }

        .card-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .noti {
            width: 50%;
            border-top: 2px solid red;
            border-bottom: 2px solid red;
            padding: 20px;
            text-align: center;
            margin: 20px auto;
        }

        .noti span {
            font-size: 20px;
            font-weight: 600;
            color: red;
        }

        .separator {
            margin: 20px 0;
            height: 1px;
            background-color: #e5e7eb;
        }

        .info-item {
            margin-bottom: 8px;
            color: #4b5563;
        }

        .font-semibold {
            font-weight: 600;
        }

        img {
            width: 150px;
            height: auto;
            display: block;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <img src="https://sistema.aviafly.mx/IMG/logo_avia.png" alt="logo">
            <h1 style="margin: 0; font-size: 24px; font-weight: 200;">Cambio de estado de <span style="font-weight: 600;">Vuelo</span></h1>
            <div class="noti">
                <span>{{ $status }}</span>
            </div>
        </div>
        <div class="section">
            <h1 style="margin-bottom: 10px; font-size: 20px; font-weight: 400;">Hola <span style="font-weight: 600;">{{ $student->email }}</span></h1>
            <div class="info-item">
                <span>Se le notifica por el cambio de estatus en el vuelo en el que está relacionado, favor de prestar atención al informe.</span>
            </div>
        </div>
        <div class="separator"></div>
        <div class="section">
            <h2 style="font-size: 18px; margin: 0 0 10px;">Informacion del vuelo</h2>
            <div class="info-item">
                <span>Fecha: {{ $flight->flight_date }}</span>
            </div>
            <div class="info-item">
                <span>Hora: {{ $flight->flight_hour }}</span>
            </div>
            <div class="info-item">
                <span class="font-semibold">Curso del vuelo:</span> Piloto aviador ( {{ $flight->type_flight }} )
            </div>
        </div>

        <div class="separator"></div>

        <div class="section">
            <h2 style="font-size: 18px; margin: 0 0 10px;">Motivo del Cambio de Estado</h2>
            <div class="info-item">
                <span class="font-semibold">Motivo del cambio:</span> {{ $details->motive }}
            </div>
            <div class="info-item">
                <span class="font-semibold">Detalles:</span>
                <p>{{ $details->details }}</p>
            </div>
        </div>

        <div class="separator"></div>

        <div class="section">
            <h2 style="font-size: 18px; margin: 0 0 10px;">Información del Estudiante</h2>
            <div class="info-item">
                <span class="font-semibold">Nombre del Estudiante:</span> {{ $student->name }} {{ $student->last_names }}
            </div>
            <div class="info-item">
                <span class="font-semibold">Matricula del estudiante:</span> {{$student->user_identification}}
            </div>
            <div class="info-item">
                <span class="font-semibold">Correo del estudiante:</span> {{$student->email}}
            </div>
            <div class="info-item">
                <span class="font-semibold">Telefono:</span> {{$student->cellphone}}
            </div>
        </div>

        <div class="separator"></div>

        <div class="section">
            <h2 style="font-size: 18px; margin: 0 0 10px;">Información del Instructor</h2>
            <div class="info-item">
                <span class="font-semibold">Nombre del Instructor:</span> {{ $instructor->name }} {{ $instructor->last_names }}
            </div>
            <div class="info-item">
                <span class="font-semibold">Correo del Instructor:</span> {{ $instructor->email }}
            </div>
        </div>
    </div>
</body>
</html>
