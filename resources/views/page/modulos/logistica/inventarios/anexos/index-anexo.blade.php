@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="pageheader">
            <div class="media display-space-between">
                <div>
                    <div class="pageicon pull-left mr10">
                        <span class="glyphicon glyphicon-shopping-cart"></span>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Inventarios / Anexos</li>
                        </ul>
                        <h4>Anexos</h4>
                    </div>

                </div>
                <h3 class="text-black text-center">{{ $movimiento->inventories_movement }} :
                    <strong>#{{ $movimiento->inventories_movementID }} </strong>
                </h3>


            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">

                <div class="col-md 12">
                    <div>
                        <h2 class="text-black">Documentos digitales</h2>
                    </div>

                    @if (count($anexosFiles) > 0)
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row media-manager">
                                    @foreach ($anexosFiles as $anexosFile)
                                        <?php
                                        //nameFiles de los documentos digitales
                                        $FileArray = explode('/', $anexosFile['inventoriesFiles_file']);
                                        $longitudFile = count($FileArray);
                                        $file = $FileArray[$longitudFile - 1];
                                        $quitamosDoblesDiagonales = str_replace(['//', '///', '////'], '/', 'archivo/' . $anexosFile->inventoriesFiles_path);
                                        ?>
                                        <div class="col-xs-3 col-sm-3 col-md-3 document ajuste">
                                            <div class="thmb checked">
                                                <div class="btn-group fm-group" style="display: block;">
                                                    <button type="button" class="btn btn-default dropdown-toggle fm-toggle"
                                                        data-toggle="dropdown">
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu fm-menu pull-right" role="menu">
                                                        <li><a href="{{ url($quitamosDoblesDiagonales) }}" id="descargar"
                                                                download="{{ $file }}"><i
                                                                    class="fa fa-download"></i> Descargar</a></li>
                                                        <li><a class="eliminar-file" style="cursor: pointer">
                                                                {!! Form::open([
                                                                    'route' => ['modulo.inventarios.anexos.delete', 'id' => $anexosFile->inventoriesFiles_id],
                                                                    'method' => 'DELETE',
                                                                ]) !!}
                                                                <i class="fa-regular fa-circle-down"></i>
                                                                Eliminar
                                                                {!! Form::close() !!}
                                                            </a>
                                                        </li>
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
                                                    $tipo7 = strpos($file, '.jpg');
                                                    ?>

                                                    @if (
                                                        $tipo !== false ||
                                                            $tipo2 !== false ||
                                                            $tipo3 !== false ||
                                                            $tipo4 !== false ||
                                                            $tipo5 !== false ||
                                                            $tipo6 !== false ||
                                                            $tipo7 !== false)
                                                        <img src="{{ url('archivo/media-doc.png') }}" class="img-responsive"
                                                            alt="">
                                                    @else
                                                        <img src="{{ url($quitamosDoblesDiagonales) }}"
                                                            class="img-responsive" alt=""
                                                            style="width : 180px; margin: auto">
                                                    @endif
                                                </div>
                                                <h5 class="fm-title"><a
                                                        href="{{ url($quitamosDoblesDiagonales) }}">{{ $file }}</a>
                                                </h5>
                                                <small class="text-muted">{{ $file }}</small>
                                            </div><!-- thmb -->
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <h5 class="text-black">Sin documentos guardados</h5>
                    @endif



                </div>
                {!! Form::open([
                    'route' => ['modulo.inventarios.anexos.store', 'id' => $id],
                    'method' => 'POST',
                    'class' => 'dropzone',
                    'id' => 'myDropzone',
                    'encType' => 'multipart/form-data',
                ]) !!}

                <div class="col-md-12">
                    <div class="fallback">
                        <input name="file" type="file" multiple />
                    </div>
                </div>


                {!! Form::close() !!}
            </div>
            <div>
            </div>


            <script>
                jQuery(document).ready(function() {
                    const btnEliminar = $('.eliminar-file');

                    btnEliminar.on('click', function(e) {
                        e.preventDefault();
                        this.children[0].submit();
                    });

                    const mostrarMensaje = (title, message, tipo) => {
                        swal({
                            icon: tipo,
                            title: title,
                            text: message,
                            confirm: true,
                            closeOnClickOutside: false,
                            closeOnEsc: false,

                        }).then((isConfirm) => {
                            if (isConfirm) {
                                location.reload();
                            }
                        });
                    }

                    const btnEnviar = jQuery('#btn-Enviar');
                    const form = jQuery('#myDropzone');

                    Dropzone.options.myDropzone = {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        paramName: 'file',
                        init: function() {
                            this.on("complete", function(file) {
                                if (this.getUploadingFiles().length === 0 && this.getQueuedFiles()
                                    .length === 0) {
                                    mostrarMensaje('!Listo!', 'Se ha guardado los archivos correctamente',
                                        'success');
                                }
                            });

                            this.on("error", function(file) {
                                mostrarMensaje('Error', 'No se pudo guardar el archivo', 'error');
                            });


                        },
                    };

                    btnEnviar.click(function(e) {
                        form.submit();
                    });





                });
            </script>

            @include('include.mensaje')
        @endsection
