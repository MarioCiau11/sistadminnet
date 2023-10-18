<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORMATO DE ENTREGA</title>

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
                <h1 style="color: #0171c0">FORMATO DE ENTREGA</h1>
                <p style="border: 1px solid black; padding: 5px;"><strong>FECHA: </strong>
                    {{ \Carbon\Carbon::parse($venta->sales_issuedate)->format('d/m/Y') }}</p>
                <p style="border: 1px solid black; padding: 5px;"><strong>PEDIDO #</strong> <span
                        class="folio-bold">{{ $venta->sales_movementID }}</span>
                </p>
                <p style="border: 1px solid black; padding: 5px;"><strong>CLIENTE: </strong>
                    {{ $venta->sales_customer . ' ' . $venta->customers_businessName }}</p>
            </td>
        </tr>

    </table>


</head>

<body>
    <table class="articulos-table">
        <tr>
            <th width="90%" style="background-color: #0171c0; border-color: #0171c0; color: white;">
                <p>DESCRIPCIÓN ENTREGA EQUIPO</p>
            </th>
            <th width="10%" style="background-color: #0171c0; border-color: #0171c0; color: white">
                <p>CANT.</p>
            </th>
        </tr>

        @foreach ($articulos_venta as $key => $articulo)
            <tr>
                @if ($articulo->salesDetails_type == 'Kit')
                    <p><strong>{{ $articulo->salesDetails_descript }}</strong>
                        <br>
                         <p  style="font-size: 11px">{{ $articulo->salesDetails_observations }}</p></p>
                    @foreach ($articulos_kit as $key => $kit)
                        {{-- @if ($kit->salesDetails_type == 'Kit') --}}
                        @if(($articulo->salesDetails_id == $kit->procKit_articleIDReference))
                            <p style="margin-left: 20px;">
                                {{ number_format($kit->procKit_cantidad, 0) . '-' . $kit->procKit_articleDesp }} {{ $kit->procKit_observation }}</p>
                            @if ($kit->procKit_tipo == 'Serie')
                                @foreach ($series[$kit->procKit_articleID . '-' . $kit->procKit_article] as $serie)
                                    <p><strong>Serie:</strong> {{ $serie['delSeriesMov2_lotSerie'] }}</p>
                                @endforeach
                            @endif
                        @endif
                    @endforeach
                @else
                    <p><strong>{{ $articulo->salesDetails_descript }}</strong> 
                        <br>
                        <p style="font-size: 11px">{{ $articulo->salesDetails_observations }}</p></p>
                    @if ($articulo->salesDetails_type == 'Serie')
                        @foreach ($series[$articulo->salesDetails_article . '-' . $articulo->salesDetails_id] as $serie)
                            <p><strong>Serie:</strong> {{ $serie['delSeriesMov2_lotSerie'] }}</p>
                        @endforeach
                    @endif
                @endif

                <td>{{ number_format($articulo->salesDetails_quantity, $articulo->units_decimalVal) }}</td>

            </tr>
        @endforeach
    </table>


    <table class="articulos-table">
        <tr>
            <th style="background-color: #0171c0; border-color: #0171c0; color: white">
                TÉRMINOS Y CONDICIONES
            </th>
            <th style="background-color: #0171c0; border-color: #0171c0; color: white">
                <p>Fecha de entrega equipos:</p>
            </th>
        </tr>
        <tr>
            <td>
                   @php
                       //tenemos que convertir generalParameters_termsConditionsReportSalesNote a html para que no nos aparezcan las etiquetas
                         echo html_entity_decode($parametro->generalParameters_termsConditionsReportDeliveryFormat);           
                   @endphp
            </td>

            {{-- <td style=" color: white">
                <div style="border-top: 1px solid black; border-left: 1px solid black; border-right: 1px solid black;">
                    <p></p>
                </div>
            </td> --}}
        </tr>

        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>

        <tr>
            <td>x_________________________________________________</td>
            <td>x_________________________________________________</td>
        </tr>
        <tr>
            <td>Confirmo de recibido de equipo completo.
                <br>(Nombre y Firma Cliente)
            </td>
            <td>Nombre y firma de distribuidor</td>
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
