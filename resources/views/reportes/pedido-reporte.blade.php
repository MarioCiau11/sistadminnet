<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTA DE VENTA</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportesNew.css') }}">

    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>
        </tr>
        <tr>
            <td class="info-empresa">
                <h4>{{ $venta->companies_descript }}</h4>
                <h2>{{ $venta->companies_nameShort }}</h2>
                <p>{{ $venta->companies_representative }}</p>
                <p>R.F.C. {{ $venta->companies_rfc }}</p>
                <p> {{ $direccion }}</p>
                <p> {{ $otrosDatos }}</p>
                <p>{{ $venta->companies_website }}</p>

            </td>

            <td class="info-compra">
                <h1 style="color: #0171c0">NOTA DE VENTA</h1>
                <p style="border: 1px solid black; padding: 5px;"><strong>FECHA: </strong>
                    {{ \Carbon\Carbon::parse($venta->sales_issuedate)->format('d/m/Y') }}
                </p>
                <p style="border: 1px solid black; padding: 5px;"><strong>NOTA #</strong> <span
                        class="folio-bold">{{ $venta->sales_movementID }}</span>
                </p>
                <p style="border: 1px solid black; padding: 5px;"><strong>AGENTE: </strong> {{ $venta->sales_seller }}
                </p>
            </td>
        </tr>

    </table>


</head>

<body>

    <table class="articulos-table">
        <tr>
            <th width="30%" style="background-color: #0171c0; border-color: #0171c0; color: white">
                <p>CLIENTE:</p>
            </th>
            <th width="30%" style="border: 0ch">
            </th>
            <th width="30%" style="background-color: #0171c0; border-color: #0171c0; color: white">
                <p>DIRIGIDO A:</p>
            </th>

        </tr>

        <tr>
            <td> {{ $venta->sales_customer . ' ' . $venta->customers_businessName }} </td>
            <td> </td>
            <td> {{ $venta->customers_name . ' ' . $venta->customers_lastName . ' ' . $venta->customers_lastName2 }}
            </td>



        </tr>

    </table>

    <table class="articulos-table">
        <tr>
            <th style="background-color: #0171c0; border-color: #0171c0; color: white;">
                <p>DESCRIPCIÓN</p>
            </th>
            <th width="10%" style="background-color: #0171c0; border-color: #0171c0; color: white">
                <p>CANT.</p>
            </th>
            <th width="15%" style="background-color: #0171c0; border-color: #0171c0; color: white">
                <p>PRECIO UNIT.</p>
            </th>
            <th width="10%" style="background-color: #0171c0; border-color: #0171c0; color: white">
                <p>TOTAL</p>
            </th>
        </tr>

        <?php 
        // dd($articulos_venta);
        $descuento = 0;
        $totales = 0;
        
        ?>
        @foreach ($articulos_venta as $key => $articulo)
            <tr>
                @if ($articulo->salesDetails_type == 'Kit')
                <p><strong>{{ $articulo->salesDetails_descript }}</strong>
                <br>
                 <strong  style="font-size: 11px">{{ $articulo->salesDetails_observations }}</strong></p>
                @foreach ($articulos_kit as $key => $kit)
                {{-- @if ($kit->salesDetails_type == 'Kit') --}}
                @if(($articulo->salesDetails_id == $kit->procKit_articleIDReference))
                    <p style="margin-left: 20px;">{{ number_format($kit->procKit_cantidad, 0).'-'.$kit->procKit_articleDesp}}</p>
                @endif
            @endforeach
            @else
                <strong>{{ $articulo->salesDetails_descript }}</strong>
            <br>
             <p  style="font-size: 11px">{{ $articulo->salesDetails_observations }}</p>
            @endif
         
                <td>{{ number_format($articulo->salesDetails_quantity, $articulo->units_decimalVal) }}</td>
                <td style="text-align: right">${{ number_format($articulo->salesDetails_unitCost, 2) }}</td>
                <?php
                $total = $articulo->salesDetails_quantity * $articulo->salesDetails_unitCost;
                $totales += $total;
                $descuento += $articulo->salesDetails_discount;
                ?>
                <td style="text-align: right">${{ number_format($total, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <table class="costos-desglosados">
        <tr>
            <td class="anchoCompleto">

            </td>
            <td>
                <p style="text-align: right">Subtotal</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format($totales, 2) }}
                </p>
            </td>
        </tr>

        <tr>
            <td class="anchoCompleto">

            </td>
            <td>
                <p style="text-align: right">Descuento</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format($descuento, 2) }}
                </p>
            </td>
        </tr>
        <tr>
            <td class="anchoCompleto"></td>
            <td>
                <p style="text-align: right">IVA %</p>
            </td>
            
            <?php $hayIva = false;
            //usamos una variable para saber si hay al menos un articulo con iva
            foreach ($articulos_venta as $key => $articulo) {
                if ($articulo->articles_porcentIva > 0 && session('company')->companies_calculateTaxes == 0) {
                    $hayIva = true;
                }
            }
            
            ?>
            @if ($hayIva)
                <td>
                    <p class="numeros-reportes" style="text-align: right">16%</p>
                </td>
                <?php
                $iva = (($totales- $descuento)* 0.16);
                ?>
            @else
                <td>
                    <p class="numeros-reportes" style="text-align: right">0%</p>
                </td>
                <?php
                $iva = 0;
                ?>
            @endif

        </tr>
        <tr>
            <td class="anchoCompleto"></td>
            <td>
                <p style="text-align: right">Importe IVA</p>
            </td>
            <td>
              
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format($iva, 2) }}</p>
            </td>
        </tr>
        <tr>
            <td class="anchoCompleto"></td>
            <td>
                <p style="text-align: right">Total</p>
            </td>
            <td style="border-top: 1px solid black">
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format(($totales-$descuento) + $iva, 2) }}</p>
            </td>
        </tr>

    </table>

    <table class="articulos-table">
        <tr>
            <th style="background-color: #0171c0; border-color: #0171c0; color: white">
                <p>TÉRMINOS Y CONDICIONES</p>
            </th>
            <th width="15%" style="border: 0ch">
                <p></p>
            </th>
            <th width="10%" style="border: 0ch">
                <p></p>
            </th>
        </tr>
        <tr>
            <td>
               @php
                   //tenemos que convertir generalParameters_termsConditionsReportSalesNote a html para que no nos aparezcan las etiquetas
                     echo html_entity_decode($parametro->generalParameters_termsConditionsReportSalesNote);           
               @endphp
            </td>

        </tr>
    </table>
    <table class="articulos-table">
        <tr>
            <th style="background-color: #0171c0; border-color: #0171c0; color: white">
                <p>FIRMA DE CONFORMIDAD</p>
            </th>
            <th width="10%" style="border: 0ch">
                <p></p>
            </th>
            <th width="15%" style="border: 0ch">
                <p></p>
            </th>
            <th width="10%" style="border: 0ch">
                <p></p>
            </th>
        </tr>
        <tr>
            <td style=" color: white">
                <p>CLIENTE</p>
            </td>
        </tr>
        <tr>
            <td style=" color: white">
                <p>CLIENTE</p>
            </td>
        </tr>
        <tr>
            <td>x_________________________________________________</td>
        </tr>
        <tr>
            <td>Nombre del cliente (Firma de aceptación)</td>
        </tr>
    </table>
</body>

<header>
    <br>
    <br>
    <br>
    <br>

    Si usted tiene alguna pregunta sobre esta cotización, por favor, póngase en contacto con nosotros <br>
    Tel. Oficina: {{ $venta->companies_phone1 }} Cel: {{ $venta->companies_phone2 }} Correo:
    {{ $venta->companies_mail }} <br>
       @php
           //tenemos que convertir generalParameters_termsConditionsReportSalesNote a html para que no nos aparezcan las etiquetas
             echo html_entity_decode($parametro->generalParameters_defaultText);           
       @endphp
</header>

</html>
