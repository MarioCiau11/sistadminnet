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
            width: 40%;
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

        .timbrado {
            margin-top: 35px;

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

        .hijos-table {
            font: outline;
            color: gray;
            font-size: 9px;
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
                <div class="titulo"><b>Factura</b></div>
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
                <div class="titulo"><b>Lugar de expedición:</b> {{$empresa->companies_cp}} </div>
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
                <div class="titulo"><b>Método de pago:</b> {{ $XML['MetodoPago'] . ' - ' . $metodoPago['descripcion'] }}
                </div>
            </div>
            <div class="col-xs-2">
                <div class="titulo"><b>Uso CFDI:</b> {{ $receptor['UsoCFDI'] . ' - ' . $usoCFDI['descripcion'] }}
                </div>
            </div>

            @if (isset($receptor['NumRegIdTrib']))
                <div class="col-xs-2">
                    <div class="titulo"><b>Número registro identidad fiscal:</b> {{ $receptor['NumRegIdTrib'] }}
                    </div>
                </div>
            @endif
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
                <div class="">Forma de pago: {{ $XML['FormaPago'] . '-' . $formaPago['descripcion'] }} </div>
            </div>
            <div class="col-xs-2">
                <div class="">Fecha comprobante: {{ $complemento['FechaTimbrado'] }} </div>
            </div>
            <div class="col-xs-2">
                <div class="">Fecha de certificacion del CFDI: {{ $complemento['FechaTimbrado'] }} </div>
            </div>
            <div class="col-xs-2">
                <div class="">Moneda: {{ $XML['Moneda'] }} </div>
            </div>
            <div class="col-xs-2">
                <div class="">Tipo de cambio: {{ $XML['TipoCambio'] }} </div>
            </div>
        </div>


        <table class="" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Clave de Producto o Servicio</th>
                    <th style="width: 200px">Descripción</th>
                    <th>Descuento</th>
                    <th>Precio unitario.</th>
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($conceptos as $concepto)
                    <tr>
                        <td>{{ $concepto['Cantidad'] }}</td>
                        <td>{{ $concepto['Unidad'] }}</td>
                        <td>{{ $concepto['ClaveProdServ'] }}</td>
                        <td>{{ $concepto['Descripcion'] }}</td>
                        <td>{{ isset($concepto['Descuento']) ? $concepto['Descuento'] : '' }}</td>
                        <td>$ {{ number_format($concepto['ValorUnitario'], 2) }}</td>
                        <td>$ {{ number_format($concepto['Importe'], 2) }}</td>
                    </tr>
                    @if ($existePartes != false)
                        @foreach ($concepto() as $key => $hijo)
                            <tr class="hijos-table">
                                @if ($key != 0)
                                    <td>{{ $hijo['Cantidad'] }}</td>
                                    <td>{{ $hijo['Unidad'] }}</td>
                                    <td>{{ $hijo['ClaveProdServ'] }}</td>
                                    <td>{{ $hijo['Descripcion'] }}</td>
                                    <td></td>
                                    <td>$0.00</td>
                                    <td>$0.00</td>
                                @endif
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
        @if (isset($retencionesTotales) && $retencionesTotales != null)
            <?php
            $retencionesISR = 0;
            $retencionesIVA = 0;
            ?>
            <H4>Total Retenciones:</H4>
            <table>
                <thead>

                    <tr>
                        <th>Tipo</th>
                        <th>Impuesto</th>
                        <th>Tipo Factor</th>
                        <th>Tasa o Couta DR</th>
                        <th>Base</th>
                        <th>Importe</th>
                    </tr>
                    @foreach ($retencionesChildren as $retencion)
                        <tr>
                            <td>Retención</td>
                            <td>{{ $retencion['Impuesto'] }}</td>
                            <td>{{ $retencion['TipoFactor'] }}</td>
                            <td>{{ $retencion['TasaOCuota'] }}</td>
                            <td>$ {{ number_format($retencion['Base'], 2) }}</td>
                            <td>$ {{ number_format($retencion['Importe'], 2) }}</td>
                        </tr>
                        <?php
                        if ($retencion['Impuesto'] == '001') {
                            $retencionesISR = $retencion['Importe'];
                        } else {
                            $retencionesIVA = $retencion['Importe'];
                        }
                        ?>
                    @endForeach
                    <tr>
                        <td colspan="5" style="text-align: right"><strong>Total Retenciones:</strong></td>
                        <td>$ {{ number_format($retencionesTotales, 2) }}</td>
                    </tr>
                </thead>
            </table>
        @endif
        <div class="totales">
            <div class="divisores">
                <div class="col-xs-3">
                    <div>$ {{ number_format($XML['SubTotal'], 2) }} </div>
                    <div>Subtotal: </div>
                </div>
            </div>
            <div class="divisores">
                <div class="col-xs-3">
                    <div>$ {{ number_format($impuestos, 2) }} </div>
                    <div>IVA Trasladado(16%): </div>
                </div>
            </div>
            @if ($movimientoFactura['movimiento'] == 'Factura')
                <div class="divisores">
                    <div class="col-xs-3">
                        <?php
                        $descuento = isset($XML['Descuento']) ? $XML['Descuento'] : 0;
                        ?>
                        <div>$ {{ number_format($descuento, 2) }} </div>
                        <div>Descuento:</div>
                    </div>

                    <div class="col-xs-3">
                        <?php
                        $retencionVistaISR = isset($retencionesISR) ? $retencionesISR : 0;
                        ?>
                        <div>$ {{ number_format($retencionVistaISR, 2) }} </div>
                        <div>ISR Retenido:</div>
                    </div>

                    <div class="col-xs-3">
                        <?php
                        $retencionVistaIVA = isset($retencionesIVA) ? $retencionesIVA : 0;
                        ?>
                        <div>$ {{ number_format($retencionVistaIVA, 2) }} </div>
                        <div>IVA Retenido:</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="totales">
            <div class="divisores" style="padding: 0px;">
                <div class="col-xs-3">
                    <div><b>$ {{ number_format($XML['Total'], 2) }}</b></div>
                    <div><b>Total:</b> </div>
                </div>
            </div>
        </div>
        <div>
            <div class="divDerecha">
                <div> <?php $formatter = new NumeroALetras();
                echo $formatter->toInvoice($XML['Total'], 2, $moneda); ?></div>
            </div>
        </div>
    </div>
    <br>
    <br>
    <br>

    @if (isset($atributosComercioExterior) && $atributosComercioExterior !== null)
        <H4>Complemento de comercio exterior:</H4>
        <table>
            <thead>
                <tr>

                    <td style="text-align: left">

                        <div class="titulo"><b>Motivo
                                traslado:</b>
                            {{ isset($atributosComercioExterior['MotivoTraslado']) ? $motivosTraslados->c_MotivoTraslado . ' - ' . $motivosTraslados->descripcion : '' }}
                        </div>

                        <div class="titulo"><b>Tipo de operación:</b>
                            {{ isset($atributosComercioExterior['TipoOperacion']) ? $tipoOperacion->c_TipoOperacion . ' - ' . $tipoOperacion->descripcion : '' }}
                        </div>

                        <div class="titulo"><b>Clave de pedimento:</b>
                            {{ isset($atributosComercioExterior['claveDePedimento']) ? $claveDePedimento->c_ClavePedimento . ' - ' . $claveDePedimento->descripcion : '' }}
                        </div>

                        <div class="titulo"><b>Certificado origen:</b>
                            {{ isset($atributosComercioExterior['CertificadoOrigen']) ? ($atributosComercioExterior['CertificadoOrigen'] === '0' ? 'No funge como certificado de origen' : 'Si funge como certificado de origen') : '' }}
                        </div>

                        <div class="titulo"><b>Tipo de cambio USD:</b>
                            {{ isset($atributosComercioExterior['TipoCambioUSD']) ? $atributosComercioExterior['TipoCambioUSD'] : '' }}
                        </div>

                    </td>

                    <td style="text-align: left">
                        <div class="titulo"><b>Número de certificado origen:</b>
                            {{ isset($atributosComercioExterior['NumCertificadoOrigen']) ? $atributosComercioExterior['NumCertificadoOrigen'] : '' }}
                        </div>

                        <div class="titulo"><b>Número de exportador confiable:</b>
                            {{ isset($atributosComercioExterior['NumeroExportadorConfiable']) ? $atributosComercioExterior['NumeroExportadorConfiable'] : '' }}
                        </div>

                        <div class="titulo"><b>INCOTERM:</b>
                            {{ isset($atributosComercioExterior['Incoterm']) ? $atributosComercioExterior['Incoterm'] : '' }}
                        </div>

                        <div class="titulo"><b>Subdivisión:</b>
                            {{ isset($atributosComercioExterior['Subdivision']) ? ($atributosComercioExterior['Subdivision'] === '0' ? 'No tiene subdivición' : 'Si tiene subdivición') : '' }}
                        </div>

                        <div class="titulo"><b>Total USD:</b>
                            {{ isset($atributosComercioExterior['TotalUSD']) ? number_format($atributosComercioExterior['TotalUSD'], 2) : '' }}
                        </div>

                    </td>
                </tr>
            </thead>
        </table>

        <table class="" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>Datos complementarios del emisor</th>
                </tr>
                <tr>
                    <th>Curp:</th>
                    <th>Calle</th>
                    <th>Colonia</th>
                    <th>Municipio</th>
                    <th>Estado</th>
                    <th>Pais</th>
                    <th>Código Postal</th>
                </tr>
            </thead>
            <tbody>
                <tr></tr>
                <tr>
                    <td>{{ isset($comercioExteriorEmisor['Curp']) ? $comercioExteriorEmisor['Curp'] : '' }}</td>
                    <td>{{ isset($comercioExteriorEmisor->Domicilio['Calle']) ? $comercioExteriorEmisor->Domicilio['Calle'] : '' }}
                    </td>
                    <td>{{ isset($comercioExteriorEmisor->Domicilio['Colonia']) ? $comercioExteriorEmisor->Domicilio['Colonia'] : '' }}
                    </td>
                    <td>{{ isset($comercioExteriorEmisor->Domicilio['Municipio']) ? $comercioExteriorEmisor->Domicilio['Municipio'] : '' }}
                    </td>
                    <td>{{ isset($comercioExteriorEmisor->Domicilio['Estado']) ? $comercioExteriorEmisor->Domicilio['Estado'] : '' }}
                    </td>
                    <td>{{ isset($comercioExteriorEmisor->Domicilio['Pais']) ? $comercioExteriorEmisor->Domicilio['Pais'] : '' }}
                    </td>
                    <td>{{ isset($comercioExteriorEmisor->Domicilio['CodigoPostal']) ? $comercioExteriorEmisor->Domicilio['CodigoPostal'] : '' }}
                    </td>
                </tr>

            </tbody>
        </table>

        <br>


        <table class="" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>Datos complementarios del receptor</th>
                </tr>
                <tr>
                    <th>Calle</th>
                    <th>Colonia</th>
                    <th>Municipio</th>
                    <th>Estado</th>
                    <th>Pais</th>
                    <th>Código Postal</th>
                </tr>
            </thead>
            <tbody>
                <tr></tr>
                <tr>
                    <td>{{ isset($comercioExteriorReceptor->Domicilio['Calle']) ? $comercioExteriorReceptor->Domicilio['Calle'] : '' }}
                    </td>
                    <td>{{ isset($comercioExteriorReceptor->Domicilio['Colonia']) ? $comercioExteriorReceptor->Domicilio['Colonia'] : '' }}
                    </td>
                    <td>{{ isset($comercioExteriorReceptor->Domicilio['Municipio']) ? $comercioExteriorReceptor->Domicilio['Municipio'] : '' }}
                    </td>
                    <td>{{ isset($comercioExteriorReceptor->Domicilio['Estado']) ? $comercioExteriorReceptor->Domicilio['Estado'] : '' }}
                    </td>
                    <td>{{ isset($comercioExteriorReceptor->Domicilio['Pais']) ? $comercioExteriorReceptor->Domicilio['Pais'] : '' }}
                    </td>
                    <td>{{ isset($comercioExteriorReceptor->Domicilio['CodigoPostal']) ? $comercioExteriorReceptor->Domicilio['CodigoPostal'] : '' }}
                    </td>
                </tr>

            </tbody>
        </table>

        <br>
        <H4>Mercancías</H4>
        <table class="" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>Numero identificación</th>
                    <th>Fracción arancelaria</th>
                    <th>Cantidad aduana</th>
                    <th>Unidad aduana</th>
                    <th>Valor unitario aduana</th>
                    <th>Valor dolares</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($comercioExteriorMercancias as $mercancia)
                    <tr>
                        <td>{{ $articulosInfor[$mercancia['NoIdentificacion']]['articles_descript2'] . ' ' . $articulosInfor[$mercancia['NoIdentificacion']]['articles_key'] }}
                        </td>
                        <td>{{ $articulosInfor[$mercancia['NoIdentificacion']]['articles_tariffFraction'] }}
                        </td>
                        <td>{{ number_format($mercancia['CantidadAduana'], 2) }}</td>
                        <td>{{ $unidArray[$mercancia['UnidadAduana']] }}</td>
                        <td>{{ number_format($mercancia['ValorUnitarioAduana'], 2) }}</td>
                        <td>{{ number_format($mercancia['ValorDolares'], 2) }}</td>
                    </tr>

                    <thead>
                        <tr>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Submodelo</th>
                            <th>Serie</th>
                        </tr>
                    </thead>
            <tbody>
                <tr>
                    <td>{{ $articulosInfor[$mercancia['NoIdentificacion']]['articles_descript2'] . ' ' . $articulosInfor[$mercancia['NoIdentificacion']]['articles_key'] }}
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
    @endforeach
    </tbody>
    </table>
    @endif



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
