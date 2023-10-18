<?php
use Luecano\NumeroALetras\NumeroALetras;
?>
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
                <p><strong>{{ $emisor['Nombre'] }}</strong></p>
                <p style="margin-top: -10px;">RFC: <strong>{{ $emisor['Rfc'] }}</strong></p>
                {{-- DIRRECCIÓN --}}
                <p style="margin-top: -10px;" class="info">{{ $direccion }}</p>
                <p class="info">Régimen Fiscal
                    <strong>{{ $emisor['RegimenFiscal'] . ' - ' . $regimenEmisor['descripcion'] }}</strong></p>
                <p class="info">Número de certificado <strong>{{ $XML['NoCertificado'] }}</strong></p>
            </div>
        </div>

        <div class="col-4">
            <table>
                <tr>
                    <td colspan="2" class="tabla-indentificador"><strong>{{ $nombreComprobante }}</strong></td>
                </tr>
                <tr>
                    <td class="info-cfdi">Serie</td>
                    <td class="info-cfdi">Folio</td>
                </tr>
                <tr>
                    <td><strong>A</strong></td>
                    <td><strong>{{ $folio }}</strong></td>
                </tr>
                <br style="line-height: 2px;">
                <tr>
                    <td class="info-cfdi">Lugar de emisión</td>
                    <td class="info-cfdi">Fecha y hora de emisión</td>
                </tr>
                <tr>
                    <td><strong>{{ $empresa->companies_cp }}</strong></td>
                    <td><strong>{{ $XML['Fecha'] }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- hacemos un salto de linea para que lo que pongamos se ponga abajo --}}
    <br style="line-height: 120px;">

    <div style="width: 48%; display: inline-block; text-align: center; vertical-align: top;">
        <table class="informacion-cliente" style="border-spacing: 100px; ">
            <tr>
                <td class="tabla-indentificador2"><strong>Información del cliente</strong></td>
            </tr>
            <tr>
                <td class="info-cliente">{{ $receptor['Nombre'] }}</td>
            </tr>
            <td class="info-rfc"><strong>RFC</strong> {{ $receptor['Rfc'] }}</td>
            <tr>
                <td class="info-domicilio">{{ $direccionCliente }}</td>
            </tr>
            <tr>
                <td class="info-regimen"><strong>Régimen Fiscal</strong>
                    {{ $receptor['RegimenFiscalReceptor'] . ' - ' . $regimenReceptor['descripcion'] }}</td>
            </tr>
            <tr>
                <td class="info-domicilioFiscal"><strong>Domicilio Fiscal</strong></td>
            </tr>
            <tr>
                <td class="info-CP">{{ $receptor['DomicilioFiscalReceptor'] }}</td>
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
                <td class="info-cfdi2">{{ $receptor['UsoCFDI'] . ' - ' . $usoCFDI['descripcion'] }}</td>
                <td class="info-cfdi2">
                    <strong>{{ $XML['Exportacion'] === '01' ? '01 - No Aplica' : '02 - Sí Aplica' }}</strong></td>
            </tr>
            <tr>
                <td class="info-cfdi2"><strong>Tipo Relación:</strong></td>
                <td class="info-cfdi2"><strong>UUID Relacionado:</strong></td>

            </tr>
            <tr>
                <td class="info-cfdi2">
                    {{ isset($CFDIRelacionado['TipoRelacion']) ? $CFDIRelacionado['TipoRelacion'] : 'No Aplica' }}
                </td>
                <td class="info-cfdi2">
                    {{ isset($CFDIRelacionado->CfdiRelacionado['UUID']) ? $CFDIRelacionado->CfdiRelacionado['UUID'] : 'Sin Relación' }}
                </td>


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
                    @foreach ($conceptos as $concepto)
                        <tr>
                            <td class="articulo">{{ $concepto['NoIdentificacion'] }}</td>
                            <td style="text-align: center;">{{ $concepto['ClaveUnidad'] }}</td>
                            <td class="articulo">{{ $concepto['Descripcion'] }}
                                <br>
                                @php
                                    $descripcion = $concepto['ObjetoImp'] === '01' ? 'No objeto de impuesto.' : ($concepto['ObjetoImp'] === '02' ? 'Sí objeto de impuesto.' : ($concepto['ObjetoImp'] === '03' ? 'Sí objeto del impuesto y no obligado al desglose.' : ''));
                                @endphp
                                @if (isset($ventaSeries[$concepto['NoIdentificacion']]))
                                        <p>Serie: {{ $ventaSeries[$concepto['NoIdentificacion']] }}</p>
                                    @endif

                                <p style="font-size: 10px; margin-top: -5px;">Código SAT:
                                    {{ $concepto['ClaveProdServ'] }} Objeto Impuesto:
                                    {{ $concepto['ObjetoImp'] . ' - ' . $descripcion }} </p>
                                </td>
                                <td class="money">$ {{ number_format($concepto['ValorUnitario'], 2) }}</td>
                                <td style="text-align: center;">{{ $concepto['Cantidad'] }}</td>
                                <td class="money">$ {{ number_format($concepto['Importe'], 2) }}</td>
                                <td class="money">$ {{ isset($concepto['Descuento']) ? $concepto['Descuento'] : '0.00' }}
                                </td>
                            </tr>
                            @if ($existePartes != false)
                            @foreach ($concepto() as $key => $hijo)
                            @if (isset($hijo['ClaveProdServ']))
                                    <tr>
                                        <td class="articulo" style="font-size: 11px;">{{ $hijo['NoIdentificacion'] }}
                                        </td>
                                        <td style="text-align: center; font-size: 11px;">{{ $hijo['ClaveUnidad'] }}
                                        </td>
                                        <td class="articulo" style="font-size: 11px;">{{ $hijo['Descripcion'] }}
                                            <br>
                                            @if (isset($hijo['ClaveProdServ']))
                                            <p style="font-size: 10px; margin-top: -5px;">Código SAT:
                                                {{ $hijo['ClaveProdServ'] }}</p>
                                            @endif
                                        </td>
                                        @if (isset($hijo['ClaveProdServ']))
                                            {{-- ponemos un isset para que no lo muestre si no existe  un $hijo --}}
                                            <td class="money" style="font-size: 11px;">$0.00</td>
                                            <td style="text-align: center; font-size: 11px;">{{ $hijo['Cantidad'] }}
                                            </td>
                                            <td class="money" style="font-size: 11px;">$0.00</td>
                                            <td class="money" style="font-size: 11px;">$0.00</td>
                                            @endif
                                        </tr>
                                @endif
                            @endforeach
                            @endif
                    @endforeach
                </tbody>
            </table>
            <hr style="width: 62%; float: left; margin-top: 0px; border: 1px solid #c8c4c4;">
            <br style="line-height: 5px;">
                <?php
                $retencionesISR = 0;
                $retencionesIVA = 0;
                ?>
                <table>
                    @foreach ($conceptosImpuestosArray as $retencion)
                        <tr>
                            {{-- para la primera fila vamos a hacer una condicional según TasaOCuota, si es 0.160000 debe decir "Traslado", si es 0.100000 o 0.106667 debe decir "Retención" --}}
                            <td class="impuestos" style="width: 55px">
                                {{ $retencion['TasaOCuota'] == 0.160000 ? 'Traslado' : ($retencion['TasaOCuota'] == 0.100000 || $retencion['TasaOCuota'] == 0.106667 ? 'Retención' : '') }}
                            </td>
                            <td class="impuestos" style="width: 75px">Impuesto: {{ $retencion['Impuesto'] == '001' ? 'ISR' : 'IVA' }}</td>
                            <td class="impuestos" style="width: 85px">Tipo factor: {{ $retencion['TipoFactor'] }}</td>
                            <td class="impuestos" style="width: 120px">Tasa o cuota: {{ $retencion['TasaOCuota'] }}</td>
                            <td class="impuestos" style="width: 70px">Base: ${{ number_format($retencion['Base'], 2) }}</td>
                            <td class="impuestos">Importe: {{ number_format($retencion['Importe'], 2) }}</td>
                        </tr>
                        <?php
                        if ($retencion['Impuesto'] == '001' && $retencion['TasaOCuota'] == 0.100000) {
                            $retencionesISR = $retencion['Importe'];
                        } elseif ($retencion['Impuesto'] == '002' && $retencion['TasaOCuota'] == 0.106667) {
                            $retencionesIVA = $retencion['Importe'];
                        }
                        ?>
                    @endForeach
                </table>
            <hr style="border: 1px solid #c8c4c4;">
            <table>
                <tr>
                    <td style="text-align: right;width: 80%;" class="totales">Subtotal $</td>
                    <td style="text-align: right;width: 10%;" class="totales">{{ number_format($XML['SubTotal'], 2) }}
                    </td>
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
                    <td style="text-align: right;width: 10%;" class="totales">
                        {{ number_format($retencionVistaISR, 2) }}</td>
                </tr>
                <tr>
                    <?php
                    $retencionVistaIVA = isset($retencionesIVA) ? $retencionesIVA : 0;
                    ?>
                    <td style="text-align: right;width: 80%;" class="totales">IVA Retenido $</td>
                    <td style="text-align: right;width: 10%;" class="totales">
                        {{ number_format($retencionVistaIVA, 2) }}</td>
                </tr>
                <tr>
                    <td class="totales" style="text-align: center"><strong>IMPORTE CON LETRA</strong>
                        <?php $formatter = new NumeroALetras();
                        echo $formatter->toInvoice($XML['Total'], 2, $moneda); ?></td>
                </tr>
            </table>
            <br style="line-height: 5px;">
            <table>
                <tr>
                    <td style="text-align: right;width: 80%;" class="totales2">Total</td>
                    <td style="text-align: right;width: 15%;" class="totales2">MXN
                        <strong>{{ number_format($XML['Total'], 2) }}</strong></td>
                </tr>
            </table>
            <br style="line-height: 10px;">

            <div style="width: 64%; display: inline-block; text-align: center; vertical-align: top;">
                <table class="informacion-cliente" style="border-spacing: 100px; ">
                    <tr>
                        <td class="info-cfdi2"><strong>Método de pago</strong></td>
                    </tr>
                    <tr>
                        <td class="info-cfdi2">{{ $XML['MetodoPago'] . ' - ' . $metodoPago['descripcion'] }}</td>
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
                        <td class="info-cfdi2">{{ $XML['FormaPago'] . '-' . $formaPago['descripcion'] }}</td>
                    </tr>
                </table>
            </div>
            {{-- <br style="line-height: 180px;"> --}}
            <hr style="border: 1px solid #c8c4c4;">
            <br style="line-height: 5px;">
            <div class="timbrado">
                <div class="codigo">
                    <img src="{{ $qrEncode }}" alt="qrCode">
                </div>
                <div>
                    <div div style="width: 25%; display: inline-block; vertical-align: top;">
                        <div class="footer">
                            <b>Folio fiscal</b>
                        </div>
                        <div class="sellos3">
                            {{ $complemento['UUID'] }}
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
                            {{ $complemento['NoCertificadoSAT'] }}
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
                            {{ $complemento['FechaTimbrado'] }}
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
                            {{ $complemento['RfcProvCertif'] }}
                        </div>
                    </div>
                    <div style="width: 1%; display: inline-block; vertical-align: top;">
                        <div class="order-info">
                        </div>
                    </div>
                    <div div style="width: 25%; display: inline-block; vertical-align: top;">
                        <div class="footer">
                            <b>Sello digital del SAT</b>
                        </div>
                        <div class="sellos">{{ $complemento['SelloSAT'] }}</div>
                    </div>
                    <div style="width: 1%; display: inline-block; vertical-align: top;">
                        <div class="order-info">
                        </div>
                    </div>
                    <div div style="width: 25%; display: inline-block; vertical-align: top;">
                        <div class="footer">
                            <b>Sello digital del CFDI</b>
                        </div>
                        <div class="sellos">
                            {{ $complemento['SelloCFD'] }}
                        </div>
                    </div>
                </div>
                <div>
                        <br style="line-height: 15px;">
                        <div class="footer">
                            <b>Cadena original del timbre</b>
                        </div>
                        <div class="sellos">
                            {{ $XML['Sello'] }}
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
    </div>
</body>

</html>
