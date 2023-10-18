@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => 'catalogo.clientes.store',
                        'method' => 'POST',
                        'id' => 'valWizard',
                        'files' => true,
                        'class' => 'panel-wizard',
                    ]) !!}

                    {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                        return "<label for='" .
                            $name .
                            "' class='" .
                            $classes .
                            "'>" .
                            $labelName .
                            "<span class='asterisk'> *</span></label>";
                    }) !!}
                    {!! Form::macro('labelNOValidacion', function ($name, $labelName, $classes) {
                        return "<label for='" .
                            $name .
                            "' class='" .
                            $classes .
                            "'>" .
                            $labelName .
                            "<span class='asterisk'> *</span></label>";
                    }) !!}
                    <ul class="nav nav-justified nav-wizard">
                        <li><a href="#tab1-4" data-toggle="tab">Datos Generales del Proveedor</a></li>
                        <li><a href="#tab3-4" data-toggle="tab">Documentos digitales</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane" id="tab1-4">

                            <div class="form-group">
                                <div class="col-md-1">
                                    <div class="form-group">
                                        {!! Form::label('keyProveedor', 'Clave', ['class' => 'negrita']) !!}
                                        {!! Form::text('keyProveedor', $provider['providers_key'], [
                                            'class' => 'form-control',
                                            'disabled',
                                            'id' => 'keyProveedor',
                                        ]) !!}
                                    </div>
                                </div>


                                <div class="form-group">
                                    <div class="col-md-7 ">
                                        <div class="form-group">
                                            {!! Form::labelValidacion('nameProveedor', 'Nombre/Razón Social', 'negrita') !!}
                                            {!! Form::text('nameProveedor', $provider['providers_name'], ['class' => 'form-control', 'disabled']) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        {!! Form::label('nameShortProveedor', 'Nombre Comercial', ['class' => 'negrita']) !!}
                                        {!! Form::text('nameShortProveedor', $provider['providers_nameShort'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-6 mt10">
                                        {!! Form::label('rfcProveedor', 'RFC', ['class' => 'negrita']) !!}
                                        {!! Form::text('rfcProveedor', $provider['providers_RFC'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>
                                    <div class="col-md-6 mt10">
                                        {!! Form::label('curpProveedor', 'CURP', ['class' => 'negrita']) !!}
                                        {!! Form::text('curpProveedor', $provider['providers_CURP'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('type', 'Es:', ['class' => 'negrita']) !!}
                                        {!! Form::select('type', ['Acreedor' => 'Acreedor', 'Proveedor' => 'Proveedor'], $provider['providers_type'], [
                                            'id' => 'select-search-hide-type',
                                            'class' => 'widthAll select-status',
                                            'disabled',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                                        {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $provider['providers_status'], [
                                            'id' => 'select-search-hide-dg',
                                            'class' => 'widthAll select-status',
                                            'disabled',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('grupo', 'Grupo', ['class' => 'negrita']) !!}
                                        {!! Form::select('grupo', $grupo_array, $provider['providers_group'], [
                                            'id' => 'select-search-hide-type',
                                            'class' => 'widthAll select-status',
                                            'disabled',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('categoria', 'Categoría', ['class' => 'negrita']) !!}
                                        {!! Form::select('categoria', $categoria_array, $provider['providers_category'], [
                                            'id' => 'select-search-hide-dg',
                                            'class' => 'widthAll select-status',
                                            'disabled',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-12">
                                        <h3 class="">DOMICILIO</h3>
                                    </div>

                                    <div class="col-md-8">
                                        {!! Form::label('direccionProveedor', 'Dirección', ['class' => 'negrita']) !!}
                                        {!! Form::text('direccionProveedor', $provider['providers_address'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!! Form::label('numberProveedor', 'Interior', ['class' => 'negrita']) !!}
                                        {!! Form::text('numberProveedor', $provider['providers_outdoorNumber'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!! Form::label('numberProveedor2', 'Exterior', ['class' => 'negrita']) !!}
                                        {!! Form::text('numberProveedor2', $provider['providers_interiorNumber'], [
                                            'class' => 'form-control',
                                            'disabled',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-12 mt10">
                                        {!! Form::label('vialidades', 'Cruzamientos/Tablaje/Lote/Otro', ['class' => 'negrita']) !!}
                                        {!! Form::text('vialidades', $provider['providers_roads'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-6 mt10">
                                        {!! Form::label('colonia', 'Colonia', ['class' => 'negrita']) !!}
                                        {!! Form::text('coloniaBusqueda', $provider['providers_colonyFractionation'], [
                                            'class' => 'form-control',
                                            'disabled',
                                            'id' => 'coloniaBusqueda',
                                            'autocomplete' => 'on',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-6 mt10">
                                        {!! Form::label('codigoPostal', 'Código postal', ['class' => 'negrita']) !!}
                                        {!! Form::number('cpBusqueda', $provider['providers_cp'], [
                                            'class' => 'form-control',
                                            'disabled',
                                            'id' => 'cpBusqueda',
                                            'autocomplete' => 'on',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-12"></div>
                                    <div class="col-md-4 mt10">
                                        {!! Form::label('municipio', 'Localidad/ Municipio', ['class' => 'negrita']) !!}
                                        {!! Form::select('municipio', $show_municipio_array, $provider['providers_townMunicipality'], [
                                            'id' => 'select-basic-city',
                                            'class' => 'widthAll select-status',
                                            'disabled',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-5 mt10">
                                        {!! Form::label('estado', 'Estado', ['class' => 'negrita']) !!}
                                        {!! Form::select('estado', $show_estado_array, $provider['providers_state'], [
                                            'id' => 'select-basic-state',
                                            'class' => 'widthAll select-status',
                                            'disabled',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('pais', 'Pais', ['class' => 'negrita']) !!}
                                        {!! Form::select('pais', $show_pais_array, $provider['providers_country'], [
                                            'id' => 'select-basic-country',
                                            'class' => 'widthAll select-status',
                                            'disabled',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-12 mt10">
                                        {!! Form::label('observaciones', 'Observaciones', ['class' => 'negrita']) !!}
                                        {!! Form::textarea('observaciones', $provider['providers_observations'], [
                                            'class' => 'form-control',
                                            'disabled',
                                            'rows' => 2,
                                        ]) !!}
                                    </div>

                                    <div class="col-md-4 mt10">
                                        {!! Form::label('phone1', 'Teléfono 1', ['class' => 'negrita']) !!}
                                        {!! Form::text('phone1', $provider['providers_phone1'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-4 mt10">
                                        {!! Form::label('phone2', 'Teléfono 2', ['class' => 'negrita']) !!}
                                        {!! Form::text('phone2', $provider['providers_phone2'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-4 mt10">
                                        {!! Form::label('cellphone', 'Teléfono celular', ['class' => 'negrita']) !!}
                                        {!! Form::text('cellphone', $provider['providers_cellphone'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-6 mt10">
                                        {!! Form::label('contacto1', 'Contacto 1', ['class' => 'negrita']) !!}
                                        {!! Form::text('contacto1', $provider['providers_contact1'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-6 mt10">
                                        {!! Form::label('correo1', 'Correo electrónico 1', ['class' => 'negrita']) !!}
                                        {!! Form::text('correo1', $provider['providers_mail1'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-6 mt10">
                                        {!! Form::label('contacto2', 'Contacto 2', ['class' => 'negrita']) !!}
                                        {!! Form::text('contacto2', $provider['providers_contact2'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-6 mt10">
                                        {!! Form::label('correo2', 'Correo electrónico 2', ['class' => 'negrita']) !!}
                                        {!! Form::text('correo2', $provider['providers_mail2'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-12">
                                        <h3 class="">INFORMACIÓN COMERCIAL Y FISCAL</h3>
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('condicion', 'Término de Crédito', ['class' => 'negrita']) !!}
                                        {!! Form::select('condicion', $condicion_array, $provider['providers_creditCondition'], [
                                            'id' => 'select-search-hide-dg',
                                            'class' => 'widthAll select-status',
                                            'disabled',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('formaPago', 'Forma de pago', ['class' => 'negrita']) !!}
                                        {!! Form::select('formaPago', $forma_pago_array, $provider['providers_formPayment'], [
                                            'id' => 'select-search-hide-dg',
                                            'class' => 'widthAll select-status',
                                            'disabled',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('moneda', 'Moneda', ['class' => 'negrita']) !!}
                                        {!! Form::select('moneda', $monedas, $provider['providers_money'], [
                                            'id' => 'select-search-hide-dg',
                                            'class' => 'widthAll select-status',
                                            'disabled',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('regimenFiscal', 'Régimen fiscal', ['class' => 'negrita']) !!}
                                        {!! Form::select('regimenFiscal', $show_regimen_array, $provider['providers_taxRegime'], [
                                            'id' => 'select-search-hide-dg',
                                            'class' => 'widthAll select-status',
                                            'disabled',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('listaPrecios', 'Lista de Precios', ['class' => 'negrita']) !!}
                                        {!! Form::select('listaPrecios', $listaProveedor,  $provider['providers_priceList'], [
                                            'id' => 'select-search-hide-type',
                                            'class' => 'widthAll select-status',
                                            'disabled',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-12 mt10">
                                        {!! Form::label('cuentaBancaria', 'Cuenta bancaria', ['class' => 'negrita']) !!}
                                        {!! Form::text('cuentaBancaria', $provider['providers_bankAccount'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mt10">
                                        {!! Form::labelNOValidacion('createat', 'Fecha de Creación', 'negrita') !!}
                                        {!! Form::text('createat', $provider['created_at'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mt10">
                                        {!! Form::labelNOValidacion('updateat', 'Fecha de Actualización', 'negrita') !!}
                                        {!! Form::text('updateat', $provider['updated_at'], ['class' => 'form-control', 'disabled']) !!}
                                    </div>
                                </div>
                            </div>
                        </div><!-- tab-pane -->

                        <div class="tab-pane" id="tab3-4">

                            <div class="col-md-12 mt10">
                                <h2 class="text-black">Documentos digitales</h2>
                            </div>

                            @if (count($documentosProveedor) > 0)
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="row media-manager">
                                            @foreach ($documentosProveedor as $document)
                                                <?php
                                                // nombre de los files
                                                $pathFileArray = explode('/', $document['providersFiles_path']);
                                                $patch = explode('-', $document['providersFiles_path'])[0];
                                                $longitudPath = count($pathFileArray);
                                                $nameFileArray = explode('-', $pathFileArray[$longitudPath - 1]);
                                                $nameFile = $nameFileArray[count($nameFileArray) - 1];
                                                
                                                //nameFiles de los documentos digitales
                                                $FileArray = explode('/', $document['providersFiles_file']);
                                                $longitudFile = count($FileArray);
                                                $file = $FileArray[$longitudFile - 1];
                                                
                                                ?>


                                                <div class="col-xs-3 col-sm-3 col-md-3 document ajuste">
                                                    <div class="thmb checked">
                                                        <div class="btn-group fm-group open" style="display: block;">
                                                            <button type="button"
                                                                class="btn btn-default dropdown-toggle fm-toggle"
                                                                data-toggle="dropdown">
                                                                <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu fm-menu pull-right" role="menu">
                                                                <li><a href="{{ url('archivo/' . $patch . '/' . $file) }}"
                                                                        id="descargar" download="{{ $file }}"><i
                                                                            class="fa fa-download"></i> Descargar</a></li>
                                                            </ul>
                                                        </div><!-- btn-group -->
                                                        <div class="thmb-prev">
                                                            <?php
                                                            $tipo = strpos($file, '.pdf');
                                                            $tipo2 = strpos($file, '.txt');
                                                            $tipo3 = strpos($file, '.doc');
                                                            $tipo4 = strpos($file, '.docx');
                                                            $tipo5 = strpos($file, '.xls');
                                                            $tipo6 = strpos($file, '.xlsx');
                                                            ?>

                                                            @if ($tipo !== false ||
                                                                $tipo2 !== false ||
                                                                $tipo3 !== false ||
                                                                $tipo4 !== false ||
                                                                $tipo5 !== false ||
                                                                $tipo6 !== false)
                                                                <img src="{{ url('archivo/media-doc.png') }}"
                                                                    class="img-responsive" alt="">
                                                            @else
                                                                <img src="{{ url('archivo/' . $patch . '/' . $file) }}"
                                                                    class="img-responsive" alt=""
                                                                    style="width : 180px; margin: auto">
                                                            @endif
                                                        </div>
                                                        <h5 class="fm-title"><a
                                                                href="{{ url('archivo/' . $patch . '/' . $file) }}">{{ $file }}</a>
                                                        </h5>
                                                        <small class="text-muted">{{ $nameFile }}</small>
                                                    </div><!-- thmb -->
                                                </div>
                                            @endforeach
                                        @else
                                            <h5 class="text-black">Sin documentos guardados</h5>
                            @endif
                        </div>
                    </div>
                </div>
            </div><!-- tab-pane -->
        </div><!-- tab-content -->

        {!! Form::close() !!}
    </div>
    </div>
    </div>
    </div>

    <script>
        jQuery(document).ready(function() {
            jQuery('#select-search-hide-dg, #select-search-hide-type, #select-search-hide-sucursal').select2({
                minimumResultsForSearch: -1
            });

            jQuery(
                    "#select-basic-country, #select-basic-state, #select-basic-city, #select-basic-suburb, #select-basic-cp, #select-basic-taxRegime, #select-basic-condition, #select-basic-payment, #select-basic-money"
                )
                .select2();

            jQuery("#valWizard").bootstrapWizard({
                onTabShow: function(tab, navigation, index) {
                    tab.prevAll().addClass("done");
                    tab.nextAll().removeClass("done");
                    tab.removeClass("done");

                    var $total = navigation.find("li").length;
                    var $current = index + 1;

                    if ($current >= $total) {
                        $("#valWizard").find(".wizard .next").addClass("hide");
                        $("#valWizard").find(".wizard .finish").removeClass("hide");
                    } else {
                        $("#valWizard").find(".wizard .next").removeClass("hide");
                        $("#valWizard").find(".wizard .finish").addClass("hide");
                    }
                },
                onTabClick: function(tab, navigation, index) {
                    return true;
                },
                onNext: function(tab, navigation, index) {
                    var $valid = jQuery("#valWizard").valid();
                    if (!$valid) {
                        $validator.focusInvalid();
                        return false;
                    }
                },
            });

            var validator = jQuery('#basicForm').validate({
                rules: {
                    keyAlmacen: {
                        required: true,
                        maxlength: 10,
                    },
                    nameAlmacen: {
                        required: true,
                        maxlength: 100,
                    },
                    type: {
                        required: true,
                    },
                    sucursal: {
                        required: true,
                    }
                },
                messages: {
                    keyAlmacen: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nameAlmacen: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    type: {
                        required: "Este campo es requerido",
                    },
                    sucursal: {
                        required: "Este campo es requerido",
                    }
                },
                highlight: function(element) {
                    jQuery(element).closest('.form-group').addClass('has-error');
                },
                unhighlight: function(element) {
                    jQuery(element).closest('.form-group').removeClass('has-error');
                },
                success: function(element) {
                    jQuery(element).closest('.form-group').removeClass('has-error');
                }

            })
        });
    </script>
@endsection
