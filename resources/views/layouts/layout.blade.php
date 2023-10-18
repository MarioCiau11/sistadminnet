<!DOCTYPE html>
{{-- <html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> --}}
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script> --}}

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <link rel="shortcut icon" href="{{ asset('public/images/default.png') }}" />

    <!-- Styles -->
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}

    <link href="{{ asset('css/style.default.css') }}" rel="stylesheet">
    <link href="{{ asset('css/morris.css') }} " rel="stylesheet">
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/bootstrap-timepicker.min.css') }} " rel="stylesheet" />
    {{-- <link href="{{ asset("css/style.datatables.css") }}" rel="stylesheet"> --}}
    <link href="{{ asset('css/dataTables.bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('css/responsive.bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select.dataTables.min.css') }}" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css"
        integrity="sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls" crossorigin="anonymous">
    {{-- FancyBox --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css" />
    <script src="https://kit.fontawesome.com/bbaab6fc75.js" crossorigin="anonymous"></script>

    {{-- Jquery --}}
    <script src="{{ asset('js/jquery-1.11.1.min.js') }}"></script>

    {{-- Sweetalert --}}
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>

    {{-- Dropzone --}}
    <link href="{{ asset('css/dropzone.css') }}" rel="stylesheet" />
    {{-- <script defer src="https://app.embed.im/snow.js"></script> --}}

    {{-- Currency --}}
    {{-- Currency --}}
    <script src="{{ asset('js/currency.min.js') }}"></script>
    {{-- Table Edit --}}
    <script src="{{ asset('js/editableTable.js') }}"></script>
    {{-- Numeric Table Validation --}}
    <script src="{{ asset('js/numeric-input-example.js') }}"></script>
    {{-- FancyBox --}}
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>

    <script src="https://cdn.ckeditor.com/ckeditor5/37.1.0/classic/ckeditor.js"></script>
    {{-- chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
    @if (!auth()->user())
        <div class="login-container">
            <main class="py-4 ">
                @yield('content')
            </main>
        </div>
    @else
        @include('layouts.header.header')
        <section>
            <div class="mainwrapper collapsed">
                <div class="leftpanel">
                    <div class="media profile-left">
                        <div class="media-body">
                            <a href="{{ route('dashboard.index') }}" class="logo">
                                <?php
                                if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
                                    $logoFile = null;
                                } else {
                                    $logoFile = Storage::disk('empresas')->get(session('company')->companies_logo);
                                }
                
                                if ($logoFile == null) {
                                    $logoFile = Storage::disk('empresas')->get('default.png');
                
                                    if ($logoFile == null) {
                                        $logoBase64 = '';
                                    } else {
                                        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
                                    }
                                } else {
                                    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
                                }
                                ?>
                                {{-- ahora ponemos la imagen para que sea responsive --}}
                                <img src="{{ $logoBase64 }}" alt="" class="img-responsive editImage2" />


                            </a>
                        </div>
                    </div><!-- media -->

                    {{-- <h5 class="leftpanel-title">Navegación</h5> --}}
                    <ul class="nav nav-pills nav-stacked">
                        @include('layouts.menuItems.itemsParent')
                    </ul>

                </div><!-- leftpanel -->

                @yield('content') {{-- renderizamos la vista seleccionada por el usuario --}}
            </div>
        </section>

        <!-- loading a mostrar con las peticiones ajax -->


        <div class="lds-spinner2" id="loading" style="display: none">
            <div class="lds-spinner">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>

        <div class="lds-roller" id="loader" style="display: none">
            <div class="lds-roller2">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>


        <script src="{{ asset('js/jquery-migrate-1.2.1.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/modernizr.min.js') }}"></script>
        <script src="{{ asset('js/pace.min.js') }}"></script>
        <script src="{{ asset('js/retina.min.js') }}"></script>
        <script src="{{ asset('js/jquery.cookies.js') }}"></script>
        {{-- <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script> --}}
        <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/select/1.4.0/js/dataTables.select.min.js"></script>


        <script src="https://momentjs.com/downloads/moment-with-locales.js"></script>


        <script src="{{ asset('js/jquery.sparkline.min.js') }}"></script>
        <script src="{{ asset('js/morris.min.js') }}"></script>
        <script src="{{ asset('js/raphael-2.1.0.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap-wizard.min.js') }}"></script>
        <script src="{{ asset('js/select2.min.js') }}"></script>
        {{-- Archivo de idiomas select2 --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.4/select2_locale_es.min.js"
            integrity="sha512-1XiQ0dfEwnKYWk5qNiUwagZcG+s2r82v5lMbL1i7xqy8/kOlwAZgEKkTxAdAw8EADrXVEyU7okVkMWKnxmrROQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>


        <script src="{{ asset('js/language/Table/tablaIdioma.js') }}"></script>
        <script src="{{ asset('js/custom.js') }}"></script>
        <script src="{{ asset('js/jquery-ui-1.10.3.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap-timepicker.min.js') }}"></script>
        <script src="{{ asset('js/dropzone.min.js') }}"></script>
        <script src="{{ asset('js/app.js') }}"></script>


        {{-- <script language="JavaScript" type="text/javascript">
            var bPreguntar = true;
     
     window.onbeforeunload = preguntarAntesDeSalir;
      
     function preguntarAntesDeSalir()
     {
       if (bPreguntar)
         return "¿Seguro que quieres salir?";
     }
        </script> --}}
    @endif
</body>

<style>
    /* Tooltip container */
    .tooltip {
        position: relative;
        display: inline-block;
    }

    /* Tooltip text */
    .tooltip .tooltiptext {
        visibility: hidden;
        width: 120px;
        bottom: 100%;
        left: 50%;
        background-color: black;
        color: #fff;
        text-align: center;
        padding: 5px 0;
        border-radius: 6px;

        /* Position the tooltip text - see examples below! */
        position: absolute;
        z-index: 1;
    }

    /* Show the tooltip text when you mouse over the tooltip container */
    .tooltip:hover .tooltiptext {
        visibility: visible;
    }
</style>

</html>
