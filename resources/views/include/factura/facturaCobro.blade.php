<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    <style>
        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: "Arial Narrow", Arial, sans-serif;
            font-size: 12px;
        }

        table {
            /* border: 1px solid red; */
            border-collapse: collapse;
            border: none;
            width: 100%;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            font-weight: normal;
            margin-top: 15px;

        }

        td,
        th {
            padding: 7px 0px;
            text-align: center;
        }

        th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;

        }

        tr:last-child {
            border-bottom: 1px solid black;
        }

        .image {
            width: 100px;
            height: 100px;
            margin-top: 0px;
            float: right;
        }

        .image img {
            width: 100%;
            height: 100%;
        }

        .derecha {
            text-align: left;
            width: 45%;
            margin-left: 50%;
            word-break: break-all;
        }

        .derecha .titulo {
            word-break: break-all;
        }

        .totales {
            text-align: left;
            width: 30%;
            margin-left: 70%;
            word-break: break-all;
        }

        .divisores {
            border: 0px solid green;

        }

        .divisores .col-xs-3 div {
            display: inline-block !important;
            width: 31%;
            margin: 5px 0px;
        }

        .divisores .col-xs-3 div:nth-child(1) {
            float: right !important;

        }

        .sinEspacio {
            padding: 0px !important;
        }


        .divisores:last-child {
            padding-bottom: 50px;
            border-bottom: 1px solid black
        }

        .divDerecha {
            float: right;
            width: 50%;
            text-align: right;
            margin: 5px 0px;
        }

        .timbrado {
            margin-top: 25px;
        }

        .certificados div {
            display: inline-block;
        }

        .certificados div:last-child {
            float: right;
        }

        .certificados div:first-child {
            float: left;
        }

        .timbrado {
            margin-top: 35px;

        }

        .timbrado .col-xs-5 {
            display: inline-block;
            width: 100%;


        }

        .timbrado .col-xs-5 .sellos {
            font-size: 9px;
            word-wrap: break-word;
            margin-bottom: 5px;
            width: 80%;



        }

        .codigo {
            float: right;
            display: block;
            margin-top: 20px;
        }


        @page {
            size: 8.5in 11in;
            margin: 1cm
        }
    </style>



</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-xs-4" style="text-align: center"><b>{{ $emisor['Nombre'] }}</b></div>
            <div class="col-xs-4" style="text-align: center"><b>RFC: {{ $emisor['Rfc'] }}</b></div>
        </div>
        <div class="image">
            <img src="{{ $logo }}" alt="Logo de la empresa">
        </div>
        <div class="row">
            <div class="col-xs-2">
                <div class="titulo"><b>{{ $nombreComprobante }}</b></div>
            </div>
            <div class="col-xs-2">
                <div class="titulo"><b>Domicilio y Expedido en:</b></div>
                <div>{{ $direccion }}</div>
            </div>
        </div>
        <br>
        {{-- datos emisor --}}
        <div class="row">
            <div class="col-xs-2">
                <div class="titulo"><b>Lugar de expedición:</b> {{ $empresa->companies_cp }} </div>
            </div>
            <div class="col-xs-2">
                <div class="titulo"><b>Régimen fiscal:</b>
                    {{ $emisor['RegimenFiscal'] . ' - ' . $regimenEmisor['descripcion'] }} </div>
            </div>
        </div>
        <br>
        {{-- datos receptor --}}
        <div class="row">
            <div class="col-xs-2">
                <div class="titulo"><b>Datos del receptor</b></div>
            </div>
            <div class="col-xs-2">
                <div class="titulo"><b>Cliente:</b> {{ $receptor['Nombre'] }} </div>
            </div>
            <div class="col-xs-2">
                <div class="titulo"><b>RFC:</b> {{ $receptor['Rfc'] }} </div>
            </div>
            <div class="col-xs-2">
                <div class="titulo"><b>Régimen fiscal:</b>
                    {{ $receptor['RegimenFiscalReceptor'] . ' - ' . $regimenReceptor['descripcion'] }} </div>
            </div>
            <div class="col-xs-2">
                <div class="titulo"><b>Domicilio:</b> {{ $direccionCliente }} </div>
            </div>

            <div class="col-xs-2">
                <div class="titulo"><b>Uso CFDI:</b> {{ $receptor['UsoCFDI'] . ' - ' . $usoCFDI['descripcion'] }}
                </div>
            </div>
        </div>
        {{-- datos factura --}}
        <div class="row derecha">
            <div class="col-xs-2">
                <div class=""><b>Tipo Comprobante: {{ $nombreComprobante }}</b></div>
            </div>
            <div class="col-xs-2">
                <div class="">Folio fiscal: {{ $complemento['UUID'] }} </div>
            </div>
            <div class="col-xs-2">
                <div class="">Número de comprobante: {{ $folio }} </div>
            </div>
            <div class="col-xs-2">
                <div class="">Forma de pago:
                    {{ $XML->Complemento->Pagos->Pago['FormaDePagoP'] . '-' . $formaPago['descripcion'] }} </div>
            </div>
            <div class="col-xs-2">
                <div class="">Fecha comprobante: {{ $complemento['FechaTimbrado'] }} </div>
            </div>
            <div class="col-xs-2">
                <div class="">Fecha de certificacion del CFDI: {{ $complemento['FechaTimbrado'] }} </div>
            </div>
            <div class="col-xs-2">
                <div class=""><strong>Monto Total Pagos:</strong>
                    {{ number_format($XML->Complemento->Pagos->Totales['MontoTotalPagos'], 2) }}
                </div>
            </div>
            <div class="col-xs-2">
                <div class=""><strong>Moneda:</strong> {{ $XML->Complemento->Pagos->Pago['MonedaP'] }}
                </div>
            </div>
            <div class="col-xs-2">
                <div class=""><strong>Tipo de cambio:</strong>
                    {{ $XML->Complemento->Pagos->Pago['TipoCambioP'] }}
                </div>
            </div>

        </div>


        <table class="" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Descripción</th>
                    <th>Precio unitario.</th>
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($conceptos as $concepto)
                    <tr>
                        <td>{{ $concepto['Cantidad'] }}</td>
                        <td>{{ $concepto['ClaveUnidad'] }}</td>
                        <td>{{ $concepto['Descripcion'] }}</td>
                        <td>$ {{ number_format($concepto['ValorUnitario'], 2) }}</td>
                        <td>$ {{ number_format($concepto['Importe'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h1>Documentos Relacionados</h1>
        <table class="" cellspacing="0" cellpadding="0">


            @foreach ($documentosRelacionados as $documentosRelacionado)
                <thead>
                    <tr>
                        <th>ID del Documento</th>
                        <th>Moneda D</th>
                        <th>Parcialidad</th>
                        <th>Folio</th>
                        <th>Imp. Saldo A</th>
                        <th>Importe Paga</th>
                        <th>Saldo Insoluto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $documentosRelacionado['idDocumento'] }}</td>
                        <td>{{ $documentosRelacionado['monedaD'] }}</td>
                        <td>{{ $documentosRelacionado['NumParcialidad'] }}</td>
                        <td>{{ $documentosRelacionado['Folio'] }}</td>
                        <td>$ {{ number_format($documentosRelacionado['ImpSaldoAnt'], 2) }}</td>
                        <td>$ {{ number_format($documentosRelacionado['ImpPagado'], 2) }}</td>
                        <td>$ {{ number_format($documentosRelacionado['ImpSaldoInsoluto'], 2) }}</td>
                    </tr>
                    <tr>
                        <th>Objeto Impuesto </th>
                        <th>Equivalencia DR</th>
                    <tr>
                        <td>{{ $documentosRelacionado['ObjImp'] }}</td>
                        <td>{{ $documentosRelacionado['EquivalenciaDR'] }}</td>
                    </tr>
                    </tr>
                    <tr>
                        <th>Tipo</th>
                        <th>Base DR</th>
                        <th>Impuesto DR</th>
                        <th>Tipo Factor DR</th>
                        <th>Tasa o Cuota DR</th>
                        <th>Importe DR</th>

                        @if (isset($documentosRelacionado['TipoRetencion1']))
                    <tr>
                        <td>{{ isset($documentosRelacionado['TipoRetencion1']) ? $documentosRelacionado['TipoRetencion1'] : 'RetencionDR' }}
                        </td>
                        <td>$
                            {{ isset($documentosRelacionado['BaseDR1']) ? number_format($documentosRelacionado['BaseDR1'], 2) : '' }}
                        </td>
                        <td>{{ isset($documentosRelacionado['ImpuestoDR1']) ? $documentosRelacionado['ImpuestoDR1'] . ' - ISR' : '' }}
                        </td>
                        <td>{{ isset($documentosRelacionado['TipoFactorDR1']) ? $documentosRelacionado['TipoFactorDR1'] : '' }}
                        </td>
                        <td>{{ isset($documentosRelacionado['TasaOCuotaDR1']) ? $documentosRelacionado['TasaOCuotaDR1'] : '' }}
                        </td>
                        <td>$
                            {{ isset($documentosRelacionado['ImporteDR1']) ? number_format($documentosRelacionado['ImporteDR1'], 2) : '' }}
                        </td>
                    </tr>
            @endif

            @if (isset($documentosRelacionado['TipoRetencion0']))
                <tr>
                    <td>{{ isset($documentosRelacionado['TipoRetencion0']) ? $documentosRelacionado['TipoRetencion0'] : 'RetencionDR' }}
                    </td>
                    <td>$
                        {{ isset($documentosRelacionado['BaseDR0']) ? number_format($documentosRelacionado['BaseDR0'], 2) : '' }}
                    </td>
                    <td>{{ isset($documentosRelacionado['ImpuestoDR0']) ? $documentosRelacionado['ImpuestoDR0'] . ' - IVA' : '' }}
                    </td>
                    <td>{{ isset($documentosRelacionado['TipoFactorDR0']) ? $documentosRelacionado['TipoFactorDR0'] : '' }}
                    </td>
                    <td>{{ isset($documentosRelacionado['TasaOCuotaDR0']) ? $documentosRelacionado['TasaOCuotaDR0'] : '' }}
                    </td>
                    <td>$
                        {{ isset($documentosRelacionado['ImporteDR0']) ? number_format($documentosRelacionado['ImporteDR0'], 2) : '' }}
                    </td>
                </tr>
            @endif

            @if (isset($documentosRelacionado['Tipo']))
                <tr>
                    <td>{{ $documentosRelacionado['Tipo'] }}</td>
                    <td>$ {{ number_format($documentosRelacionado['BaseDR'], 2) }}</td>
                    <td>{{ $documentosRelacionado['ImpuestoDR'] . ' - IVA' }}</td>
                    <td>{{ $documentosRelacionado['TipoFactorDR'] }}</td>
                    <td>{{ $documentosRelacionado['TasaOCuotaDR'] }}</td>
                    <td>$ {{ number_format($documentosRelacionado['ImporteDR'], 2) }}</td>
                </tr>
            @endif
            </tr>
            </tbody>
            @endforeach

        </table>

    </div>

    <div class="timbrado">
        <div>
            "Este documento es una representación impresa de un CFDI"
        </div>
        <br>
        <div class="certificados">
            <div class="col-xs-3">
                <div><b>Número de serie del certificado de sello digital:</b> <br>
                    {{ $XML['NoCertificado'] }}</div>
            </div>
            <div class="col-xs-3">
                <div><b>Número de serie del certificado de sello digital del SAT:</b> <br>
                    {{ $complemento['NoCertificadoSAT'] }}</div>
            </div>
        </div>
    </div>

    <div class="timbrado">
        <div class="codigo">
            <img src="{{ $qrEncode }}" alt="qrCode">
        </div>
        <div class="col-xs-5">
            <div>
                <b>Cadena original del complemento de certificación digital del SAT:</b>
            </div>
            <div class="sellos">
                {{ $XML['Sello'] }}
            </div>
        </div>

        <div class="col-xs-5">
            <div>
                <b>Sello Digital del Emisor:</b>
            </div>
            <div class="sellos">
                {{ $complemento['SelloCFD'] }}
            </div>
        </div>

        <div class="col-xs-5">
            <div><b>Sello digital del SAT:</b></div>
            <div class="sellos">{{ $complemento['SelloSAT'] }}</div>
        </div>


    </div>


</body>

</html>
