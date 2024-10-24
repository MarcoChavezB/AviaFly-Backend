<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Receipt</title>
    <style>
        body, html {
            height: 100cm;
            margin: 0;
            padding: 0;
        }
        main {
            height: 100cm;
            border-right: 1px solid #000;
            width: 20%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .header {
            margin-bottom: 20px;
        }
        img {
            width: 100%;
            height: 70px;
        }
        .items {
            flex-grow: 1;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
        }
        .text {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        p, td {
            font-size: 0.6rem;
        }
        th {
            font-size: 0.7rem;
        }
        .products {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        table {
            width: 100%;
        }
        td {
            text-align: center;
        }
        .total { /* Cambié de bonificacion a total */
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .payment-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
<main>
    <div class="header">
        <img src="http://aviabackend.site/IMG/logo_avia.png" alt="logo">
    </div>
    <div class="text">
        <p>AVIATRAINING AND<br>
            TECHNOLOGY<br>
            ATA100618L60<br>
            {{ date('Y-m-d H:i:s') }}<br>
            {{ $baseData->location }}
        </p>
    </div>
    <div class="user">
        <p>Autoriza: <span>{{ $employeeName }} {{ $employeeLastNames }}</span><br>
            Fecha: {{ date('Y-m-d') }}<br>
            Matrícula: <span>{{ $studentData->user_identification }}</span><br>
            Nombre: <span>{{ $studentData->name }} {{ $studentData->last_names }}</span><br>
        </p>
    </div>
    <div class="products">
        <table>
            <thead>
                <tr>
                    <th>C</th>
                    <th>Concepto</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                    <tr>
                        <td>{{ $item['quantity'] >= 0 ? $item['quantity'] : '-' }}</td>
                        <td>{{ $item['concept'] }}</td>
                        <td>${{ number_format($item['total'], 2, '.', '') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="payment-info">
        <p><span>Forma de pago:</span> <span>{{ $incomeDetails->payment_method }}</span></p>
        <p><span>Comisión:</span> <span>${{ number_format($incomeDetails->commission, 2, '.', '') }}</span></p>
        <p class="total"><span>Total:</span> <span>${{ number_format($incomeDetails->total, 2, '.', '') }}</span></p> <!-- Clase modificada -->
    </div>
    <div class="footer">
        <p>
            Agradecemos tu confianza, es un placer atenderte. No hay devoluciones de pagos por ningún motivo.
            La hora de vuelo se cobrará al costo del día en que se realizará dicho vuelo. El precio de los trámites se tendrá que cubrir al precio del día en que se realizarán ante la institución competente; si pagaste antes y/o hubo un cambio, se tendrá que pagar la diferencia para cubrir el pago. Los precios no incluyen transporte hacia ningún recinto para visitas o prácticas.
            Los pagos por horas de vuelo, crédito de vuelo, colegiaturas, etc. no serán reembolsables.
            Pagos únicamente por transferencia o en efectivo directamente en caja. Al momento de pagar pide tu recibo, de lo contrario, no nos hacemos responsables. Si necesitas facturar, es por transferencia y antes de que cierre cada mes; pídela al momento de pagar. Los requisitos y trámites por realizar están sujetos a cambios sin previo aviso, dependiendo de la entidad educativa competente. Si no te entregan tu recibo al hacer tu pago, se solicita tu cortesía.
        </p>
    </div>
</main>
</body>
</html>
