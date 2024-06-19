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
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        img{
            width: 100px;
            height: 100px;
        }
        .items {
            flex-grow: 1;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <main>
        <div class="header">
            <img src="https://sistema.aviafly.mx/IMG/logo_avia.png" alt="logo">
            <p>Date: June 18, 2024</p>
        </div>
        <div class="items">
            <!-- Aquí irían los elementos del ticket como productos, precios, etc. -->
            <p>Product A - $10.00</p>
            <p>Product B - $15.00</p>
            <p>Product C - $5.00</p>
        </div>
        <div class="footer">
            <p>Total: $30.00</p>
        </div>
    </main>
</body>
</html>
