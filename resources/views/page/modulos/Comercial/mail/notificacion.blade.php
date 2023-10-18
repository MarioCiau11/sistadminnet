<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f5f5f5;
        }

        .container {
            margin-top: 50px;
        }

        .invoice {
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            padding: 20px;
        }

        .invoice-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 15px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .invoice-title {
            margin: 0;
            font-size: 28px;
            color: #333;
        }

        .invoice-content {
            padding: 20px;
        }

        .address {
            margin-bottom: 20px;
        }

        .address strong {
            font-weight: bold;
        }

        .contact-info a {
            color: #007bff;
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }
    </style>
</head>
@php
$empresa = session('company');
$direccion = $empresa->companies_addres . ', CP:' . $empresa->companies_cp;
$colonia = explode("-", $empresa->companies_suburb)[0];
$ciudad = explode("-", $empresa->companies_city)[0];
$estado = explode("-", $empresa->companies_state)[0];
$pais = explode("-", $empresa->companies_country)[0];
$todo = $ciudad . ', ' . $estado . ', ' . $pais;

@endphp
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="invoice">
                    <div class="invoice-header">
                        <h2 class="invoice-title">{{ $movimiento }}</h2>
                    </div>
                    <div class="invoice-content">
                        <p>Estimado cliente,</p>
                        <p>Anexo al presente encontrará su factura de {{$movimiento = ($movimiento == 'Factura') ? 'Venta' : (($movimiento == 'Anticipo Clientes') ? 'Anticipo' : (($movimiento == 'Cobro Facturas') ? 'Complemento de Pago' : $movimiento))}}.</p>
                        <hr>
                        <div class="address">
                            <div class="row">
                                <div class="col-md-6">
                                    <img 
                                    @if ($logo != null)
                                        src="{{ isset($message) ? $message->embed($logo) : '' }}"
                                        {{-- src="{{ $base64ImageE }}" --}}
                                    @endif
                                    alt="Logo de la empresa" width="100">
                                </div>
                                <div class="col-md-6">
                                    <strong>{{ session('company')->companies_nameShort }}</strong><br>
                                    {{ session('company')->companies_name }}<br>
                                    {{ session('company')->companies_website }}<br>
                                    <a href="mailto:{{ session('company')->companies_mail }}">{{ session('company')->companies_mail }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="address">
                            <strong>{{ $direccion }}</strong><br>
                            <strong>{{ $colonia }}</strong><br>
                            <strong>{{ $todo }}</strong><br>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
