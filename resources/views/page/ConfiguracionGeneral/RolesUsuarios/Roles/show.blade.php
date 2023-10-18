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

                    <h2 class="text-black">Datos Generales del Rol</h2>
                    <div class="col-md-5">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('identificador', 'Identificador', 'negrita') !!}
                            {!! Form::text('identificador', $role->identifier, ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nombre', 'Nombre', 'negrita') !!}
                            {!! Form::text('nombre', $role->name, ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group mt10">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $role->status, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-12">
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelNOValidacion('nameDescripcion', 'Descripción', 'negrita') !!}
                            {!! Form::textarea('nameDescripcion', $role->descript, ['class' => 'form-control', 'rows' => 4, 'disabled']) !!}

                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::label('createdat', 'Fecha de creación', ['class' => 'negrita']) !!}
                            {!! Form::text('createat', $role->created_at->format('d-m-Y'), ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::label('updatedat', 'Fecha de actualización', ['class' => 'negrita']) !!}
                            {!! Form::text('updateat', $role->updated_at->format('d-m-Y'), ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>



                    <h3 class="text-black">Permisos</h3>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::checkbox('verPermisos', 'Ver permisos', [], ['id' => 'activePermisos']) !!}
                            {!! Form::labelNOValidacion('verPermiso', 'Ver permisos', '') !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>
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
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <script>
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
            const disableSetCheckbox = jQuery('.setCheckBox');
            const disabledModulosPermisos = jQuery('.modulosPermisos');


            activePermisos.on('change', function() {
                if (activePermisos.is(':checked')) {
                    viewPermisos.show();
                } else {
                    viewPermisos.hide();
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

            disableSetCheckbox.prop('disabled', true);
            disabledModulosPermisos.prop('disabled', true);
        });
    </script>
@endsection
