@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="row body-form">
            <div class="container-formulario-login">
                <div class="card">
                    <div class="card-header">
                        <h2 class="title-login">Iniciar sesión</h2>
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

                    <img id="logo" alt="logo-empresa" class="logo-empresa" />

                    <div class="card-body">
                        {!! Form::open(['route' => 'login', 'method' => 'POST', 'autocomplete' => 'off', 'id' => 'formLogin']) !!}
                        {{ csrf_field() }}

                        <div class="form-group">
                            {!! Form::label('usuario', 'Usuario', ['class' => 'negrita']) !!}
                            <input id="username" type="text" class="form-control" name="username" required autofocus>
                            <span class="invalid-feedback" role="alert">
                                <strong class="error-login" id="usernameMessage"></strong>
                            </span>

                        </div>


                        <div class="form-group">
                            {!! Form::label('password', 'Contraseña', ['class' => 'negrita']) !!}
                            <input id="password" type="password" class="form-control" name="password" required autofocus />

                            <span class="invalid-feedback" role="alert">
                                <strong class="error-login" id="passwordMessage"></strong>
                            </span>

                        </div>

                        <div class="form-group">
                            {!! Form::label('empresa', 'Empresa', ['class' => 'negrita']) !!}
                            <select id="empresa" name="empresa" class="form-control" required>
                            </select>

                        </div>


                        <div class="form-group">
                            {!! Form::label('sucursal', 'Sucursal', ['class' => 'negrita']) !!}
                            <select id="sucursal" name="sucursal" class="form-control" required>
                            </select>
                        </div>

                          @if ($errors->has('license'))
                        <strong class="error-login">
                            {{ $errors->first('license') }}
                        </strong>
                          @endif

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }}>

                                <label class="form-check-label" for="remember">
                                    Recuerdame
                                </label>
                            </div>
                        </div>

                        {{-- <div class="row mb-0">
                        <div class="col-md-8 offset-md-4">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Login') }}
                            </button>

                            @if (Route::has('password.request'))
                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>
                    </div> --}}



                        {!! Form::submit('Iniciar sesión', ['class' => 'btn btn-primary enviar', 'id' => 'login']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function() {

            jQuery('#username').on('change', function() {
                $.ajax({
                    url: "/login/user/verificacion",
                    method: "GET",
                    data: {
                        username: jQuery('#username').val()
                    },
                    success: function({
                        status,
                        data
                    }) {
                        if (status === 404) {
                            jQuery('#usernameMessage').html(data);
                            jQuery('#empresa').html('');
                            jQuery('#sucursal').html('');
                            jQuery('#logo').css({
                                display: 'none'
                            })
                            jQuery('#logo').removeAttr('src');
                        } else {
                            jQuery('#usernameMessage').html('');
                            $.ajax({
                                url: "/empresasById",
                                method: "GET",

                                data: {
                                    id: data.user_id
                                },
                                success: function({
                                    status,
                                    data
                                }) {
                                    if (status === 200) {
                                        jQuery('#empresa').html('');
                                        jQuery('#empresa').append(
                                            '<option selected disabled value="">Escoge una empresa</option>'
                                        );
                                        data.forEach(function(empresa) {
                                            jQuery('#empresa').append(
                                                '<option value="' +
                                                empresa.companies_key +
                                                '">' + empresa
                                                .companies_key + ' - ' +
                                                empresa.companies_name +
                                                '</option>');
                                        });
                                    }
                                }
                            });
                        }
                    }
                });

            });

            jQuery('#password').on('change', function() {
                $.ajax({
                    url: "/login/user/verificacion/password",
                    method: "GET",
                    data: {
                        username: jQuery('#username').val(),
                        password: jQuery('#password').val()
                    },
                    success: function({
                        status,
                        data
                    }) {
                        if (status === 404) {
                            jQuery('#passwordMessage').html(data);
                        } else {
                            jQuery('#passwordMessage').html('');
                        }
                    }
                });
            });

            jQuery('#empresa').on('change', function(e) {
                e.preventDefault();
                let valueEmpresa = jQuery('#empresa').val();
                $.ajax({
                    url: "/empresaDatosById",
                    method: "GET",
                    data: {
                        clave: valueEmpresa
                    },
                    success: function({
                        status,
                        data
                    }) {
                        if (status === 200) {
                            if (data.companies_logo) {
                                jQuery('#logo').attr('src', '/archivo/' + data
                                    .companies_logo);
                            } else {
                                jQuery('#logo').attr('src', '/archivo/default.png');
                            }

                            jQuery('#logo').css({
                                display: 'block'
                            });
                            $.ajax({
                                url: "/sucursalByClaveEmpresa",
                                method: 'GET',
                                data: {
                                    username: jQuery('#username').val(),
                                    clave: valueEmpresa
                                },
                                success: function({
                                    status,
                                    data
                                }) {
                                    if (status === 200 && data !== null) {
                                        jQuery('#sucursal').html('');
                                        jQuery('#sucursal').append(
                                            '<option selected disabled value="">Escoge una sucursal</option>'
                                        );

                                        data.forEach(function(sucursal) {
                                            jQuery('#sucursal').append(
                                                '<option value="' +
                                                sucursal
                                                .branchOffices_key +
                                                '">' + sucursal
                                                .branchOffices_key +
                                                ' - ' + sucursal
                                                .branchOffices_name +
                                                '</option>');
                                        });
                                    }
                                }
                            });
                        }
                    },

                });
            });

        });
                //ahora hacemos que cuando le de clic en el botón de guardar aparezca el loader
                jQuery(".enviar").click(function() {
                //validamos que los select no esten vacios para mostrar el loader
                if (jQuery('#username').val() != null && jQuery('#password').val() != null && jQuery('#empresa').val() != null && jQuery('#sucursal').val() != null) {
                    jQuery("#loader").show();

                }

                
            });

        let sesionGuardada = localStorage.getItem("userSession");

        $.ajax({
          url: "/login/license/verificate",
          type: "POST",
          data: { id: sesionGuardada,
                    _token: "{{ csrf_token() }}"
        },
          success: function(data) {
            console.log(data);
          }
        });


    </script>
@endsection
