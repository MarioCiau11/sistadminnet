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

                    <ul class="nav nav-justified nav-wizard">
                        <li><a href="#tab1-4" data-toggle="tab">Datos Generales del Cliente</a></li>
                        <li><a href="#tab2-4" data-toggle="tab">Datos fiscales</a></li>
                        <li><a href="#tab3-4" data-toggle="tab">Documentos digitales</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane" id="tab1-4">


                            <div class="col-md-12">
                                <h2 class="text-black">Datos Generales del Cliente</h2>
                            </div>
                            <div class="col-md-4">
                                {!! Form::label('clave', 'Clave', ['class' => 'negrita']) !!}
                                {!! Form::text('clave', $customer['customers_key'], ['id' => 'clave', 'class' => 'form-control', 'disabled']) !!}
                            </div>

                            <?php
                            
                            $typePersona = $customer['customers_type'] == 0 ? 'Fisica' : 'Moral';
                            if ($typePersona == 'Fisica') {
                                $PF = true;
                                $PM = false;
                            } else {
                                $PF = false;
                                $PM = true;
                            }
                            ?>

                            <div class="form-group">
                                <div class="col-md-8">
                                    <div class="col-md-4 mt10">
                                        {!! Form::labelValidacion('tipoPersona', 'Persona Física', 'negrita') !!}
                                        {!! Form::radio('tipoPersona', 1, $PF, ['id' => 'personaFisica', 'disabled']) !!}
                                    </div>

                                    <div class="col-md-4 mt10">
                                        {!! Form::labelValidacion('tipoPersona', 'Persona Moral', 'negrita') !!}
                                        {!! Form::radio('tipoPersona', 1, $PM, ['id' => 'personaMoral', 'disabled']) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-12"></div>

                            <div class="form-group">
                                <div class="col-md-12">
                                    {!! Form::labelValidacion('razonSocial', 'Razon social', 'negrita') !!}
                                    {!! Form::text('razonSocial', $customer['customers_businessName'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::labelValidacion('rfc', 'RFC', 'negrita') !!}
                                    {!! Form::text('rfc', $customer['customers_RFC'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="col-md-6">
                                    {!! Form::labelValidacion('curp', 'CURP', 'negrita') !!}
                                    {!! Form::text('curp', $customer['customers_CURP'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>


                            <div class="col-md-12">
                                <h2 class="text-black">Información de representante legal</h2>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::labelValidacion('nameRepresentante', 'Nombre(s)', 'negrita') !!}
                                    {!! Form::text('nameRepresentante', $customer['customers_name'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::labelValidacion('apellidoPaterno', 'Apellido Paterno', 'negrita') !!}
                                    {!! Form::text('apellidoPaterno', $customer['customers_lastName'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-4">
                                    {!! Form::labelValidacion('apellidoMaterno', 'Apellido Materno', 'negrita') !!}
                                    {!! Form::text('apellidoMaterno', $customer['customers_lastName2'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('telefono', 'Teléfono celular', ['class' => 'negrita']) !!}
                                    {!! Form::text('telefono', $customer['customers_cellphone'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-8">
                                    {!! Form::label('email', 'Correo electrónico', ['class' => 'negrita']) !!}
                                    {!! Form::text('email', $customer['customers_mail'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="col-md-12">
                                <h2 class="text-black">DOMICILIO DE LA RAZÓN SOCIAL</h2>
                            </div>


                            <div class="col-md-12">
                            <div class="form-group">
                                    {!! Form::labelValidacion('direccion', 'Dirección', 'negrita') !!}
                                    {!! Form::text('direccion', $customer['customers_addres'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::labelValidacion('numExt', 'Exterior', 'negrita') !!}
                                    {!! Form::text('numExt', $customer['customers_outdoorNumber'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::labelValidacion('numInt', 'Interior', 'negrita') !!}
                                    {!! Form::text('numInt', $customer['customers_interiorNumber'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12">
                                    {!! Form::labelValidacion('entreVialidades', 'Cruzamiento/Tablaje/Lote/Otro', 'negrita') !!}
                                    {!! Form::text('entreVialidades', $customer['customers_roads'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                    <div class="form-group">
                                    {!! Form::labelValidacion('pais', 'País', 'negrita') !!}
                                    {!! Form::select('pais', $show_pais_array, $customer['customers_country'], [
                                        'id' => 'select-basic-pais',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno...',
                                        'disabled',
                                    ]) !!}
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::labelValidacion('estado', 'Estado', 'negrita') !!}
                                    {!! Form::select('estado', $show_estado_array, $customer['customers_state'], [
                                        'id' => 'select-basic-estado',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno...',
                                        'disabled',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::labelValidacion('localidadMuni', 'Localidad/Municipio', 'negrita') !!}
                                    {!! Form::select('localidadMuni', $show_municipio_array, $customer['customers_townMunicipality'], [
                                        'id' => 'select-basic-localidadMuni',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno...',
                                        'disabled',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group ">
                                    {!! Form::labelValidacion('cpBusqueda', 'Código Postal', 'negrita') !!}
                                    {!! Form::number('cpBusqueda', $customer['customers_cp'], [
                                        'class' => 'form-control',
                                        'id' => 'cpBusqueda',
                                        'autocomplete' => 'on',
                                        'disabled',
                                    ]) !!}

                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group ">
                                    {!! Form::labelValidacion('coloniaBusqueda', 'Colonia', 'negrita') !!}
                                    {!! Form::text('coloniaBusqueda', explode('-', $customer['customers_colonyFractionation'])[0], [
                                        'class' => 'form-control',
                                        'id' => 'coloniaBusqueda',
                                        'autocomplete' => 'on',
                                        'disabled',
                                    ]) !!}

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('telefono1', 'Teléfono 1', ['class' => 'negrita']) !!}
                                    {!! Form::text('telefono1', $customer['customers_phone1'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('telefono2', 'Teléfono 2', ['class' => 'negrita']) !!}
                                    {!! Form::text('telefono2', $customer['customers_phone2'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('contacto1', 'Contacto 1 / Representante legal', ['class' => 'negrita']) !!}
                                    {!! Form::text('contacto1', $customer['customers_contac1'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6">
                                    {!! Form::label('contacto2', 'Contacto 2', ['class' => 'negrita']) !!}
                                    {!! Form::text('contacto2', $customer['customers_contac2'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('email1', 'Correo electrónico 1', ['class' => 'negrita']) !!}
                                    {!! Form::text('email1', $customer['customers_mail1'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6">
                                    {!! Form::label('email2', 'Correo electrónico 2', ['class' => 'negrita']) !!}
                                    {!! Form::text('email2', $customer['customers_mail2'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>

                            <div class="form-group">
                                <div class="col-md-12">
                                    {!! Form::label('observaciones', 'Observaciones', ['class' => 'negrita']) !!}
                                    {!! Form::textarea('observaciones', $customer['customers_observations'], [
                                        'class' => 'form-control',
                                        'rows' => 2,
                                        'disabled',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('grupo', 'Grupo', ['class' => 'negrita']) !!}
                                    {!! Form::select('grupo', $grupo_array, $customer['customers_group'], [
                                        'id' => 'select-search-hide-grupo',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno...',
                                        'disabled',
                                    ]) !!}
                                </div>
                            </div>



                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('categoria', 'Categoria', ['class' => 'negrita']) !!}
                                    {!! Form::select('categoria', $categoria_array, $customer['customers_category'], [
                                        'id' => 'select-search-hide-categoria',
                                        'class' => 'widthAll select-grupo',
                                        'placeholder' => 'Seleccione uno...',
                                        'disabled',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-4">
                                    {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                                    {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $customer['customers_status'], [
                                        'id' => 'select-search-hide-dg',
                                        'class' => 'widthAll select-status',
                                        'disabled',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mt10">
                                    {!! Form::label('createat', 'Fecha de Creación', ['class' => 'negrita']) !!}
                                    {!! Form::text('createat', $customer['created_at'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mt10">
                                    {!! Form::label('updateat', 'Fecha de Actualización', ['class' => 'negrita']) !!}
                                    {!! Form::text('updateat', $customer['updated_at'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="col-md-12">
                                <h2 class="text-black">Condiciones comerciales</h2>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('listaPrecios', 'Lista de precios', ['class' => 'negrita']) !!}
                                    {!! Form::select(
                                        'listaPrecios',
                                        [
                                            'listPrice1' => 'Precio 1',
                                            'listPrice2' => 'Precio 2',
                                            'listPrice3' => 'Precio 3',
                                            'listPrice4' => 'Precio 4',
                                            'listPrice5' => 'Precio 5',
                                        ],
                                        $customer['customers_priceList'],
                                        [
                                            'id' => 'select-basic-listaPrecios',
                                            'class' => 'widthAll select-grupo',
                                            'placeholder' => 'Seleccione uno...',
                                            'disabled',
                                        ],
                                    ) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6">
                                    {!! Form::label('condicionPago', 'Condición de pago', ['class' => 'negrita']) !!}
                                    {!! Form::select('condicionPago', $condicion_array, $customer['customers_creditCondition'], [
                                        'id' => 'select-search-hide-dg',
                                        'class' => 'widthAll select-status',
                                        'disabled',
                                        'placeholder' => 'Seleccione uno...',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6">
                                    {!! Form::label('limiteCredito', 'Limite de crédito', ['class' => 'negrita']) !!}
                                    {!! Form::text('limiteCredito', $customer['customers_creditLimit'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>


                        </div><!-- tab-pane -->

                        <div class="tab-pane" id="tab2-4">
                            <div class="col-md-12">
                                <h2 class="text-black">Datos fiscales</h2>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('identificadorCFDI', 'Régimen Fiscal', ['class' => 'negrita']) !!}
                                    {!! Form::select('regimenFiscal', $show_regimen_array, $customer['customers_taxRegime'], [
                                        'id' => 'select-basic-regimenFiscal',
                                        'class' => 'widthAll select-grupo',
                                        'placeholder' => 'Seleccione uno...',
                                        'disabled',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('identificadorCFDI', 'Identificador de CFDI', ['class' => 'negrita']) !!}
                                    {!! Form::select('identificadorCFDI', $show_usocfdi_array, $customer['customers_identificationCFDI'], [
                                        'id' => 'select-basic-identificadorCFDI',
                                        'class' => 'widthAll select-grupo',
                                        'placeholder' => 'Seleccione uno...',
                                        'disabled',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('identidadFiscal', 'Número registro identidad fiscal', ['class' => 'negrita']) !!}
                                    {!! Form::Text('identidadFiscal', $customer['customers_numRegIdTrib'], ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>


                            <div class="col-md-12"></div>


                        </div><!-- tab-pane -->

                        <div class="tab-pane" id="tab3-4">

                            <div class="col-md-12 mt10">
                                <h2 class="text-black">Documentos digitales</h2>
                            </div>

                            @if (count($customer_documents) > 0)
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="row media-manager">

                                            @foreach ($customer_documents as $document)
                                                <?php
                                                // nombre de los files
                                                $pathFileArray = explode('/', $document['customersFiles_path']);
                                                $path = explode('-', $document['customersFiles_path'])[0];
                                                $longitudPath = count($pathFileArray);
                                                $nameFileArray = explode('-', $pathFileArray[$longitudPath - 1]);
                                                $nameFile = $nameFileArray[count($nameFileArray) - 1];
                                                
                                                //nameFiles de los documentos digitales
                                                $FileArray = explode('/', $document['customersFiles_file']);
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
                                                                <li><a href="{{ url('archivo/' . $path . '/' . $file) }}"
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
                                                                <img src="{{ url('archivo/' . $path . '/' . $file) }}"
                                                                    class="img-responsive" alt=""
                                                                    style="width : 180px; margin: auto">
                                                            @endif
                                                        </div>
                                                        <h5 class="fm-title"><a
                                                                href="{{ url('archivo/' . $path . '/' . $file) }}">{{ $file }}</a>
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

            var maxField = 10; //Input fields increment limitation
            var addButton = $('.add_button'); //Add button selector
            var wrapper = $('.field_wrapper'); //Input field wrapper
            // var fieldHTML = '<div><input type="text" name="field_name[]" value=""/><a href="javascript:void(0);" class="remove_button" title="Remove field"><img src="remove-icon.png"/></a></div>'; //New input field html 

            var fieldHTML =
                "<div><div class='col-md-11 mt10'><label for='nombreDocumento' class='negrita'>Nombre del documento</label><input class='form-control' name='nombreDocumento[]' type='text'></div><div class='col-md-1'><a href='javascript:void(0);' class='remove_button btn btn-danger' title='Remove field'><i class='fa fa-times' aria-hidden='true'></i></a></div><div class='col-md-12 mt10'><input type='file' name='field_name[]'/></div></div>"; //New input field html 
            var x = 1; //Initial field counter is 1
            $(addButton).click(function() { //Once add button is clicked
                if (x < maxField) { //Check maximum number of input fields
                    x++; //Increment field counter
                    $(wrapper).append(fieldHTML); // Add field html
                }
            });
            $(wrapper).on('click', '.remove_button', function(e) { //Once remove button is clicked
                e.preventDefault();
                $(this).parent('div').parent('div').remove(); //Remove field html
                x--; //Decrement field counter
            });

            jQuery('#select-search-hide-grupo, #select-search-hide-categoria, #select-search-hide-dg').select2({
                minimumResultsForSearch: -1
            });

            jQuery(
                    "#select-basic-coloniaFraccionamiento, #select-basic-localidadMuni, #select-basic-estado, #select-basic-pais, #select-basic-codigoPostal, #select-basic-listaPrecios, #select-basic-condicionPago, #select-basic-identificadorCFDI, #select-basic-regimenFiscal"
                )
                .select2();

            // Wizard With Form Validation
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
                    var $valid = jQuery("#valWizard").valid();
                    if (!$valid) {
                        $validator.focusInvalid();
                        return false;
                    }
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


            // Wizard With Form Validation
            var $validator = jQuery("#valWizard").validate({
                rules: {
                    tipoPersona: {
                        required: true,
                    },
                    razonSocial: {
                        required: true,
                    },
                    rfc: {
                        required: true,
                        maxlength: 15,
                    },
                    curp: {
                        required: function() {
                            if (jQuery('#personaFisica')[0].checked) {
                                return true;
                            }
                            return false;
                        },
                        maxlength: 30,
                    },
                    nameRepresentante: {
                        required: function() {
                            if (jQuery('#personaFisica')[0].checked) {
                                return true;
                            }
                            return false;
                        },
                        maxlength: 100,
                    },
                    apellidoPaterno: {
                        required: function() {
                            if (jQuery('#personaFisica')[0].checked) {
                                return true;
                            }
                            return false;
                        },
                        maxlength: 100,
                    },
                    apellidoMaterno: {
                        required: function() {
                            if (jQuery('#personaFisica')[0].checked) {
                                return true;
                            }
                            return false;
                        },
                        maxlength: 100,
                    },
                    telefono: {
                        maxlength: 10,
                    },
                    email: {
                        email: true,
                        maxlength: 50,
                    },
                    direccion: {
                        required: true,
                        maxlength: 100,
                    },
                    entreVialidades: {
                        required: true,
                        maxlength: 100,
                    },
                    numExt: {
                        required: true,
                        maxlength: 50,
                    },
                    numInt: {
                        required: true,
                        maxlength: 50,
                    },
                    coloniaFracc: {
                        required: true,
                    },
                    localidadMuni: {
                        required: true,
                    },
                    estado: {
                        required: true,
                    },
                    pais: {
                        required: true,
                    },
                    codigoPostal: {
                        required: true,
                    },
                    contacto1: {
                        maxlength: 50,
                    },
                    contacto2: {
                        maxlength: 50,
                    },
                    email1: {
                        maxlength: 50,
                    },
                    email2: {
                        maxlength: 50,
                    },
                    observaciones: {
                        maxlength: 250,
                    },
                },
                messages: {
                    tipoPersona: {
                        required: "Este campo es requerido",
                    },
                    razonSocial: {
                        required: 'Este campo es requerido',
                    },
                    rfc: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    curp: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    nameRepresentante: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    apellidoPaterno: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    apellidoMaterno: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    telefono: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    email: {
                        email: 'Ingrese un correo valido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    direccion: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    entreVialidades: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    numExt: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    numInt: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    coloniaFracc: {
                        required: 'Este campo es requerido',
                    },
                    localidadMuni: {
                        required: 'Este campo es requerido',
                    },
                    estado: {
                        required: 'Este campo es requerido',
                    },
                    pais: {
                        required: 'Este campo es requerido',
                    },
                    codigoPostal: {
                        required: "Este campo es requerido",
                    },
                    contacto1: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    contacto2: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    email1: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    email2: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    observaciones: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    }
                },
                highlight: function(element) {
                    jQuery(element)
                        .closest(".form-group")
                        .removeClass("has-success")
                        .addClass("has-error");
                },
                success: function(element) {
                    jQuery(element).closest(".form-group").removeClass("has-error");
                },

            });
        });
    </script>
@endsection
