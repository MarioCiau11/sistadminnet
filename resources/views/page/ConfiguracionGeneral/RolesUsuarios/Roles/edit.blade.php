@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::model($role, [
                        'method' => 'PUT',
                        'route' => ['configuracion.roles.update', Crypt::encrypt($role->id)],
                    ]) !!}

                    {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" .
                            $name .
                            "' class= '" .
                            $classes .
                            "'>" .
                            $labelName .
                            "<span class='asterisk'> *</span> </label>";
                    }) !!}

                    {!! Form::macro('labelNOValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" . $name . "' class= '" . $classes . "'>" . $labelName . '</label>';
                    }) !!}

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <h2 class="text-black">Datos Generales del Rol</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('identificador', 'Identificador', 'negrita') !!}
                            {!! Form::text('identificador', $role->identifier, ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nombre', 'Nombre', 'negrita') !!}
                            {!! Form::text('nombre', $role->name, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group mt10">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $role->status, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-12">
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelNOValidacion('nameDescripcion', 'Descripción', 'negrita') !!}
                            {!! Form::textarea('nameDescripcion', $role->descript, ['class' => 'form-control', 'rows' => 4]) !!}

                        </div>
                    </div>



                    <h3 class="text-black">Permisos</h3>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::checkbox('verPermisos', 'Ver permisos', [], ['id' => 'activePermisos']) !!}
                            {!! Form::labelNOValidacion('verPermiso', 'Ver permisos', '') !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::checkbox(
                                'selectPermisos',
                                'Seleccionar todos los permisos con un solo checkbox',
                                [],
                                ['id' => 'selecPermisos'],
                            ) !!}
                            {!! Form::labelNOValidacion('selectPermisos', 'Seleccionar todos los permisos con un solo checkbox', '') !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>
                    {{-- <div class="view-permiso">
                        @foreach ($categorias as $categoria)
                            <div class='col-md-4 checkbox-permisos'>
                                <div class='form-group mt10'>
                                    <h4 class='text-black'>{{ $categoria }}</h4>
                                    <div class='checkbox-container'>

                                        <!-- Aqui se genera el checkbox de cada permiso un solo checkbox -->
                                        <?php
                                        $cat = [
                                            'Ventas' => false,
                                            'Compras' => false,
                                            'Inventarios' => false,
                                            'Gastos' => false,
                                            'Cuentas por cobrar' => false,
                                            'Cuentas por pagar' => false,
                                            'Tesorería' => false,
                                            'Movimientos' => true,
                                            'Permisos de Configuración' => true,
                                            'Permisos de Catálogos' => true,
                                            'Reporte Ventas' => true,
                                            'Reporte Compras' => true,
                                            'Reporte Inventarios' => true,
                                            'Reporte Gastos' => true,
                                            'Reporte CxC' => true,
                                            'Reporte CxP' => true,
                                            'Reporte Tesorería' => true,
                                            'Reporte Gerencial' => true,
                                        ];
                                        ?>
                                        @if ($cat[$categoria])
                                            @foreach ($categoriasPermisos[$categoria] as $permisosPorCategoria)
                                                <div class="checkbox">
                                                    {!! Form::checkbox(
                                                        'permisos[]',
                                                        $permisosPorCategoria['id'],
                                                        in_array($permisosPorCategoria['id'], $rolePermissions) ? true : false,
                                                        ['class' => 'setCheckBox'],
                                                    ) !!}
                                                    {!! Form::label($permisosPorCategoria['name']) !!}
                                                </div>
                                            @endforeach
                                        @else
                                            @foreach ($categoriasPermisos[$categoria] as $permisosPorCategoria)
                                                <div class="checkbox">
                                                    {!! Form::checkbox(
                                                        'permisos[]',
                                                        $permisosPorCategoria['id'],
                                                        in_array($permisosPorCategoria['id'], $rolePermissions) ? true : false,
                                                        [
                                                            'class' => 'setCheckBox modulosPermisos',
                                                            'id' => str_replace(' ', '', $categoria) . '-' . str_replace(' ', '', $permisosPorCategoria['name']),
                                                        ],
                                                    ) !!}
                                                    {!! Form::label($permisosPorCategoria['name']) !!}
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div> --}}

                    <div class="view-permiso">
                        <div class="col-sm-8 col-md-12">
                              
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs nav-line">
                                @if (isset($categorias))
                                    @foreach ($categorias as $categoria)
                                        @if ($categoria != 'Dashboard')
                                            <li class="{{ $loop->first ? 'active' : '' }}">
                                                <a href="#{{ str_replace(' ', '', $categoria) }}" data-toggle="tab">
                                                    {{ $categoria }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                @endif
                            </ul>
                        
                            <!-- Tab panes -->
                            <div class="tab-content nopadding noborder">

                            @if (isset($categorias))
                                @foreach ($categorias as $categoria)
                                    @if ($categoria != 'Dashboard')
                                        <div class="tab-pane {{ $loop->first ? 'active' : '' }}"
                                            id="{{ str_replace(' ', '', $categoria) }}">
                                            <div class="checkbox-container">
                                                @foreach ($categoriasPermisos[$categoria] as $permisosPorCategoria)
                                                    <div class="checkbox">
                                                        {!! Form::checkbox(
                                                            'permisos[]',
                                                            $permisosPorCategoria['id'],
                                                            in_array($permisosPorCategoria['id'], $rolePermissions) ? true : false,
                                                            ['class' => 'setCheckBox'],
                                                        ) !!}
                                                        {!! Form::label($permisosPorCategoria['name']) !!}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif                         
                        </div><!-- tab-content -->
                          
                        </div>
                    </div>



                    <div class="col-md-12 mt50 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar rol', ['class' => 'btn btn-warning enviar']) !!}

                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <script>
        
        //ahora hacemos que cuando le de clic en el botón de guardar aparezca el loader
        jQuery(".enviar").click(function() {
                //solo mostrar el loader si los campos están validados
                if (jQuery("#basicForm").valid()) {
                    jQuery("#loader").show();
                }
            });

            
        jQuery(document).ready(function() {
            jQuery(
                    '#select-search-hide-dg, #select-search-hide-nameUnidad, #select-search-hide-numDecimalValida, #select-search-hide-nameclaveSAT, #select-search-hide-status'
                )
                .select2({
                    minimumResultsForSearch: -1
                });

            jQuery("#select-basic-empresa").select2();

            jQuery('#basicForm').validate({
                rules: {
                    identificador: {
                        required: true,
                        maxlength: 10,
                    },
                    nombre: {
                        required: true,
                        maxlength: 100,
                    }
                },
                messages: {
                    identificador: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nombre: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
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

            });


            jQuery('#regreso').click(function() {
                window.location.href = "{{ route('configuracion.roles.index') }}";
            });

            const viewPermisos = jQuery('.view-permiso');
            const activePermisos = jQuery('#activePermisos');
            const selecPermisos = jQuery('#selecPermisos');
            const checkBoxes = jQuery('.setCheckBox');

            activePermisos.on('change', function() {
                if (activePermisos.is(':checked')) {
                    viewPermisos.show();
                } else {
                    viewPermisos.hide();
                }
            });

            selecPermisos.on('change', function() {
                if (selecPermisos.is(':checked')) {
                    checkBoxes.prop('checked', true);
                } else {
                    checkBoxes.prop('checked', false);
                }
            });

            // $(".modulosPermisos").change((
            //     e) => {
            //     //Obtenemos el id del checkbox presionado
            //     let id = $(e.target).attr("id");
            //     let ultimoCaracter = id.slice(-1);
            //     let permiso = id.substring(0, id.length - 1);

            //     if (ultimoCaracter === "E") {
            //         $("#" + permiso + "C").prop("checked", false);
            //     }

            //     if (ultimoCaracter === "C") {
            //         $("#" + permiso + "E").prop("checked", false);
            //     }

            // });
        });
    </script>
@endsection
