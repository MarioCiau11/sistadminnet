<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Factura</title>

    <link rel="stylesheet" href="{{ asset('css/factura/factura.css') }}">
</head>

<body>

    <div class="container">
        <div class="col-2">
            <img class="logo" src="{{ $logo }}" alt="Logo de la empresa">
        </div>

        <div class="col-6">
            <div class="info-empresa">
                <p><strong>CAPITAL CRUZ</strong></p>
                <p style="margin-top: -10px;">RFC: <strong>CCR220107A5A</strong></p>
                {{-- DIRRECCIÓN --}}
                <p style="margin-top: -10px;" class="info">CALLE 59 NO. 588, CP:97130,México-MEX,Yucatán-YUC-Diaz Ordaz-0059-97130</p>
                <p class="info">Régimen Fiscal <strong>601 - General de Ley Personas Morales</strong></p>
                <p class="info">Número de certificado <strong>00001000000512507257</strong></p>
            </div>
        </div>

        <div class="col-4">
            <table>
                <tr>
                    <td colspan="2" class="tabla-indentificador"><strong>CFDI de Ingreso</strong></td>
                </tr>
                <tr>
                    <td class="info-cfdi">Serie</td>
                    <td class="info-cfdi">Folio</td>
                </tr>
                <tr>
                    <td><strong>A</strong></td>
                    <td><strong>44</strong></td>
                </tr>
                <br style="line-height: 2px;">
                <tr>
                    <td class="info-cfdi">Lugar de emisión</td>
                    <td class="info-cfdi">Fecha y hora de emisión</td>
                </tr>
                <tr>
                    <td><strong>97130</strong></td>
                    <td><strong>08 Mar. 2023 - 16:53:45</strong></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- hacemos un salto de linea para que lo que pongamos se ponga abajo --}}
    <br style="line-height: 120px;">

    <div style="width: 49%; display: inline-block; text-align: center; vertical-align: top;">
        <table class="informacion-cliente" style="border-spacing: 100px; ">
            <tr>
                <td class="tabla-indentificador2"><strong>Información del cliente</strong></td>
            </tr>
            <tr>
                <td class="info-cliente">PÚBLICO GENERAL</td>
            </tr>
            <td class="info-rfc"><strong>RFC</strong> XAXX010101000</td>
            <tr>
                <td class="info-domicilio">CALLE 59 NO. 588 X 6 Y 6A Exterior: 403 Interior: Col.Mérida 97000, CP:97130,MEX,YUC</td>
            </tr>
            <tr>
                <td class="info-regimen"><strong>Régimen Fiscal</strong> 616 - Sin obligaciones fiscales</td>
            </tr>
            <tr>
                <td class="info-domicilioFiscal"><strong>Domicilio Fiscal</strong></td>
            </tr>
            <tr>
                <td class="info-CP">97130</td>
            </tr>
        </table>
    </div>
    <div style="width: 1%; display: inline-block;">
        <div class="order-info">
        </div>
    </div>

    <div style="width: 48%; display: inline-block; text-align: center; vertical-align: top;">
        <table>
            <tr>
                <td colspan="2" class="tabla-indentificador2" style="color: #dcdde2;"><strong>CFDI</strong></td>
            </tr>
            <tr>
                <td class="info-cfdi2">Uso de CFDI</td>
                <td class="info-cfdi2">Exportación</td>
            </tr>
            <tr>
                <td class="info-cfdi2">No se</td>
                <td class="info-cfdi2"><strong>01 - No aplica</strong></td>
            </tr>
        </table>
    </div>
    <div class="container">
        <div class="col-12">
            <table>
                <thead class="tabla-articulos">
                    <tr>
                        <th style="border-radius: 5px 0 0 0;" class="identificador">Código</th>
                        <th class="identificador">Clave unidad</th>
                        <th class="identificador">Descripción</th>
                        <th class="identificador">Valor unitario</th>
                        <th class="identificador">Cantidad</th>
                        <th class="identificador" style="width: 100px;">Importe</th>
                        <th class="identificador" style="border-radius: 0 5px 0 0;">Descuento</th>
                    </tr>
                </thead>
                {{-- hacemos un poco espacio para que no se pegue a la tabla de arriba --}}
                <tbody style="line-height: 20px;">
                    <tr>
                        <td class="articulo">20</td>
                        <td style="text-align: center;">E48</td>
                        <td class="articulo">PRESTACION DE SOPORTE, CONSULTORIA Y DESARROLLO DE SOFTWARE DEL 01 AL 31 DE AGOSTO DE 2023
                            <br>
                            <p style="font-size: 10px; margin-top: -5px;">Código SAT: 31211904 Objeto Impuesto: 02 - Sí objeto de impuesto</p>
                        </td>
                        <td class="money">$ {{ number_format(3500, 2) }}</td>
                        <td style="text-align: center;">4</td>
                        <td class="money">$ {{ number_format(27791.67, 2) }}</td>
                        <td class="money">$ 0.00</td>
                    </tr>
                </tbody>
            </table>
            <hr style="width: 62%; float: left; margin-top: 0px; border: 1px solid #c8c4c4;">
            <br style="line-height: 5px;">
            {{-- <table>
                @foreach ($retencionesChildren as $retencion)
                <tr>
                    <td class="impuestos" style="width: 20px">Traslado</td>
                    <td class="impuestos" style="width: 70px">Impuesto: {{ $retencion['Impuesto'] }}</td>
                    <td class="impuestos" style="width: 80px">Tipo factor: {{ $retencion['TipoFactor'] }}</td>
                    <td class="impuestos" style="width: 90px">Tasa o cuota: {{ $retencion['TasaOCuota'] }}</td>
                    <td class="impuestos" style="width: 80px">Base: $ {{ number_format($retencion['Base'], 2) }}</td>
                    <td class="impuestos">Importe: {{ number_format($retencion['Importe'], 2) }}</td>
                </tr>
                <?php
                    if ($retencion['Impuesto'] == '001') {
                        $retencionesISR = $retencion['Importe'];
                    } else {
                        $retencionesIVA = $retencion['Importe'];
                    }
                    ?>
                @endForeach
            </table> --}}
            {{-- @endif --}}
            <hr style="border: 1px solid #c8c4c4;">
            {{-- <table>
                <tr>
                    <td style="text-align: right;width: 80%;" class="totales">Subtotal $</td>
                    <td style="text-align: right;width: 10%;" class="totales">{{ number_format($XML['SubTotal'], 2) }}</td>
                </tr>
                <tr>
                    <td style="text-align: right;width: 80%;" class="totales">IVA Trasladado (16%) $</td>
                    <td style="text-align: right;width: 10%;" class="totales">{{ number_format($impuestos, 2) }}</td>
                </tr>
                <tr>
                    <?php
                    $retencionVistaISR = isset($retencionesISR) ? $retencionesISR : 0;
                    ?>
                    <td style="text-align: right;width: 80%;" class="totales">ISR Retenido $</td>
                    <td style="text-align: right;width: 10%;" class="totales">{{ number_format($retencionVistaISR, 2) }}</td>
                </tr>
                <tr>
                    <?php
                    $retencionVistaIVA = isset($retencionesIVA) ? $retencionesIVA : 0;
                    ?>
                    <td style="text-align: right;width: 80%;" class="totales">IVA Retenido $</td>
                    <td style="text-align: right;width: 10%;" class="totales">{{ number_format($retencionVistaIVA, 2) }}</td>
                </tr>
                <tr>
                    <td class="totales" style="text-align: center"><strong>IMPORTE CON LETRA</strong> 
                    <?php $formatter = new NumeroALetras();
                    echo $formatter->toInvoice($XML['Total'], 2, $moneda); ?><</td>
                </tr>
            </table> --}}
            <br style="line-height: 5px;">
            <table>
                <tr>
                    <td style="text-align: right;width: 80%;" class="totales2">Total</td>
                    <td style="text-align: right;width: 15%;" class="totales2">MXN <strong>{{ number_format(2000, 2) }}</strong></td>
                </tr>
            </table>
            <br style="line-height: 10px;">

            <div style="width: 64%; display: inline-block; text-align: center; vertical-align: top;">
                <table class="informacion-cliente" style="border-spacing: 100px; ">
                    <tr>
                        <td class="info-cfdi2"><strong>Método de pago</strong></td>
                    </tr>
                    <tr>
                        {{-- <td class="info-cfdi2">{{ $XML['MetodoPago'] . ' - ' . $metodoPago['descripcion'] }}</td> --}}
                    </tr>
                </table>
            </div>
            <div style="width: 1%; display: inline-block;">
                <div class="order-info">
                </div>
            </div>

            <div style="width: 33%; display: inline-block; text-align: center; vertical-align: top;">
                <table>
                    <tr>
                        <td class="info-cfdi2"><strong>Forma de pago</strong></td>
                    </tr>
                    <tr>
                        {{-- <td class="info-cfdi2">{{ $XML['FormaPago'] . '-' . $formaPago['descripcion'] }}</td> --}}
                    </tr>
                </table>
            </div>
            <br style="line-height: 180px;">
            <hr style="border: 1px solid #c8c4c4;">
            <br style="line-height: 5px;">
            <div class="timbrado">
                <div class="codigo">
                    {{-- <img src="{{ $qrEncode }}" alt="qrCode"> --}}
                </div>
                <div>
                    <div div style="width: 25%; display: inline-block; vertical-align: top;">
                        <div class="footer">
                            <b>Folio fiscal</b>
                        </div>
                        <div class="sellos3">
                            {{-- {{ $complemento['UUID'] }} --}}
                        </div>
                    </div>
                    <div style="width: 1%; display: inline-block; vertical-align: top;">
                        <div class="order-info">
                        </div>
                    </div>
                    <div div style="width: 25%; display: inline-block; vertical-align: top;">
                        <div class="footer">
                            <b>Número de certificado SAT</b>
                        </div>
                        <div class="sellos2">
                            {{-- {{ $complemento['NoCertificadoSAT'] }} --}}
                        </div>
                    </div>
                    <div style="width: 1%; display: inline-block; vertical-align: top;">
                        <div class="order-info">
                        </div>
                    </div>
                    <div div style="width: 25%; display: inline-block; vertical-align: top;">
                        <div class="footer">
                            <b>Fecha y hora de certificación</b>
                        </div>
                        <div class="sellos2">
                            {{-- {{ $complemento['FechaTimbrado'] }} --}}
                        </div>
                    </div>
                </div>
                    <br style="line-height: 15px;">
                    <div>
                        <div div style="width: 25%; display: inline-block; vertical-align: top;">
                            <div class="footer">
                                <b>RFC proveedor de certificación</b>
                            </div>
                            <div class="sellos2">
                                LSO1306189R5
                            </div>
                            <br style="line-height: 15px;">
                            <div class="footer">
                                <b>Cadena original del timbre</b>
                            </div>
                            <div class="sellos">
                                {{-- {{ $XML['Sello'] }} --}}
                            </div>
                        </div>
                        <div style="width: 1%; display: inline-block; vertical-align: top;">
                            <div class="order-info">
                            </div>
                        </div>
                        <div div style="width: 25%; display: inline-block; vertical-align: top;">
                            <div  class="footer">
                                <b>Sello digital del SAT</b>
                            </div>
                            <div class="sellos"></div>
                        </div>
                        <div style="width: 1%; display: inline-block;">
                            <div class="order-info">
                            </div>
                        </div>
                        <div div style="width: 25%; display: inline-block; vertical-align: top;">
                            <div class="footer">
                                <b>Sello digital del CFDI</b>
                            </div>
                            <div class="sellos">
                                {{-- {{ $complemento['SelloCFD'] }} --}}
                            </div>
                        </div>
                    </div>
            </div>
            {{-- ponemos ahora lo de este documento es una representación impresa de un cfdi --}}
            <br style="line-height: 5px;">
            <div class="representacion">
                <div class="representacion2">
                    <p>Este documento es una representación impresa de un CFDI versión 4.0</p>
                </div>

        </div>
    </div>
</body>

</html>
