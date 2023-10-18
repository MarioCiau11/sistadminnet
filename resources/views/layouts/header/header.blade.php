<header>
    <div class="headerwrapper collapsed">
        <div class="header-left">
            <aq href="{{ route('dashboard.index') }}" class="logo">
                {{-- ahora ponemos la imagen para que sea responsive --}}
                <img src="{{ asset('images/LogoERPNet-17.png') }}"" alt="" class="img-responsive editImage3" />


            </a>
            <div class="pull-right">
                <a href="" class="menu-collapse">
                    <i class="fa fa-bars"></i>
                </a>
            </div>

            <span class="glyphicon glyphicon-pushpin" id="clip-menu"></span>
        </div><!-- header-left -->

        <div class="header-right">
            <div class="pull-center">
                <div class="btn-group  title-empresa">
                    <h5>Bienvenido: <strong>
                            {{ auth()->user()->user_name }}
                        </strong>
                    </h5>
                </div>
            </div>

            <div class="pull-right">
                <div class="btn-group  title-empresa">
                    <h5>Sucursal: <strong>
                            @if (session()->has('sucursal'))
                                {{ session()->get('sucursal')->branchOffices_name }}
                            @endif
                        </strong>
                    </h5>
                </div>
                
                <div class="btn-group  title-empresa">
                    <h5>Empresa: <strong>
                            @if (session()->has('company'))
                                {{ session()->get('company')->companies_name }}
                            @endif
                        </strong>
                    </h5>
                </div>
                <div class="btn-group btn-group-option">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-caret-down"></i>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        @if (auth()->user()->username == 'MASTER')
                        <li><a href="{{ route('licencias.index') }}"><i style="color:rgb(73, 95, 240);" class="glyphicon glyphicon-user"></i><span style="color:rgb(73, 95, 240);"> Licencias</span></a></li>
                      <li><a style="color:rgb(255, 115, 0);" href="{{ route('timbrado.index') }}"><i class="glyphicon glyphicon-cog"></i><span style="color:rgb(255, 115, 0);"> Timbres</span></a></li>
                      <li class="divider"></li>
                      @endif
                        <li>
                            <a class="dropdown-item logoutClick" href="{{ route('logout') }}"
                                onclick="event.preventDefault();event.target.href='#';this.onclick=null;document.getElementById('logout-form').submit(); limpiarLocalStorage();">
                                <i class="glyphicon glyphicon-log-out"></i><span style="color: red">Cerrar Sesión</span>
                            </a>
                        </li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </ul>
                </div><!-- btn-group -->

            </div><!-- pull-right -->

        </div><!-- header-right -->

    </div><!-- headerwrapper -->
</header>
<script>
    function limpiarLocalStorage() {
        localStorage.removeItem('reportesRecientes');
    }
</script>