<ul class="children">
    @can('Párametros Generales')
        <li @if (request()->routeIs('configuracion.parametros-generales.create') ||
                request()->routeIs('configuracion.parametros-generales.create')) class="active" @endif><a
                href="{{ route('configuracion.parametros-generales.create') }}">Parámetros Generales</a></li>
    @endcan
    
    @if (Auth::user()->can('Roles y Usuarios'))
        <li class="dropdown-submenu @if (strpos(Request::url(), 'roles') || strpos(Request::url(), 'usuarios')) open @endif">
            @can('Roles y Usuarios')
                <a href="#" class="subCatalogoParent"><span class="span-arrow">Roles y Usuarios<div class="arrow"></div>
                    </span></a>
                <ul class="subCatalago" style="display: none">
                    <li @if (request()->routeIs('configuracion.roles.index') ||
                            request()->routeIs('configuracion.roles.create') ||
                            request()->routeIs('configuracion.roles.show') ||
                            request()->routeIs('configuracion.roles.edit')) class="active" @endif><a
                            href="{{ route('configuracion.roles.index') }}">Roles</a></li>

                    <li @if (request()->routeIs('configuracion.usuarios.index') ||
                            request()->routeIs('configuracion.usuarios.create') ||
                            request()->routeIs('configuracion.usuarios.show') ||
                            request()->routeIs('configuracion.usuarios.edit')) class="active" @endif><a
                            href="{{ route('configuracion.usuarios.index') }}">Usuarios</a></li>
                </ul>
            @endcan
        </li>
    @endif

    @can('Monedas')
        <li @if (request()->routeIs('configuracion.monedas.index') ||
                request()->routeIs('configuracion.monedas.create') ||
                request()->routeIs('configuracion.monedas.show') ||
                request()->routeIs('configuracion.monedas.edit')) class="active" @endif><a
                href="{{ route('configuracion.monedas.index') }}">Monedas</a></li>
    @endcan

    @can('Formas Cobro/Pago')
        <li @if (request()->routeIs('configuracion.formas-pago.index') ||
                request()->routeIs('configuracion.formas-pago.create') ||
                request()->routeIs('configuracion.formas-pago.show') ||
                request()->routeIs('configuracion.formas-pago.edit')) class="active" @endif><a
                href="{{ route('configuracion.formas-pago.index') }}">Formas de Pago y Cobro</a></li>
    @endcan

    @can('Condiciones de Crédito')
        <li @if (request()->routeIs('configuracion.condiciones-credito.index') ||
                request()->routeIs('configuracion.condiciones-credito.create') ||
                request()->routeIs('configuracion.condiciones-credito.show') ||
                request()->routeIs('configuracion.condiciones-credito.edit')) class="active" @endif><a
                href="{{ route('configuracion.condiciones-credito.index') }}">Términos de Crédito</a></li>
    @endcan

    @can('Unidades')
        <li @if (request()->routeIs('configuracion.unidades.index') ||
                request()->routeIs('configuracion.unidades.create') ||
                request()->routeIs('configuracion.unidades.show') ||
                request()->routeIs('configuracion.unidades.edit')) class="active" @endif><a
                href="{{ route('configuracion.unidades.index') }}">Unidades de Medida</a></li>
    @endcan

    {{-- @can('Unidades Empaque')
        <li @if (request()->routeIs('configuracion.unidades-empaque.index') ||
                request()->routeIs('configuracion.unidades-empaque.create') ||
                request()->routeIs('configuracion.unidades-empaque.show') ||
                request()->routeIs('configuracion.unidades-empaque.edit')) class="active" @endif><a
                href="{{ route('configuracion.unidades-empaque.index') }}">Unidades Empaque</a></li>
    @endcan --}}

    @can('Conceptos de Módulos')
        <li @if (request()->routeIs('configuracion.concepto-modulos.index') ||
                request()->routeIs('configuracion.concepto-modulos.create') ||
                request()->routeIs('configuracion.concepto-modulos.show') ||
                request()->routeIs('configuracion.concepto-modulos.edit')) class="active" @endif><a
                href="{{ route('configuracion.concepto-modulos.index') }}">Conceptos de Procesos</a></li>
    @endcan


    @can('Motivos de cancelación')
        <li @if (request()->routeIs('configuracion.motivos-cancelacion.index') ||
                request()->routeIs('configuracion.motivos-cancelacion.create') ||
                request()->routeIs('configuracion.motivos-cancelacion.show') ||
                request()->routeIs('configuracion.motivos-cancelacion.edit')) class="active" @endif><a
                href="{{ route('configuracion.motivos-cancelacion.index') }}">Motivos de Cancelación</a></li>
    @endcan
</ul>
