@php
    $usuario = auth()->user();
    $permisosReportesCompras = $usuario->getAllPermissions()->where('tipoReporte', '=', 'Reportes de Compras')->pluck('name')->toArray();
    $permisosReportesGastos = $usuario->getAllPermissions()->where('tipoReporte', '=', 'Reportes de Gastos')->pluck('name')->toArray();
    $permisosReportesCxp = $usuario->getAllPermissions()->where('tipoReporte', '=', 'Reportes de Cuentas por Pagar')->pluck('name')->toArray();
    $permisosReportesTesoreria = $usuario->getAllPermissions()->where('tipoReporte', '=', 'Reportes de Tesorería')->pluck('name')->toArray();
    $permisosReportesVentas = $usuario->getAllPermissions()->where('tipoReporte', '=', 'Reportes de Ventas')->pluck('name')->toArray();
    $permisosReportesCxc = $usuario->getAllPermissions()->where('tipoReporte', '=', 'Reportes de Cuentas por Cobrar')->pluck('name')->toArray();
    $permisosReportesInventarios = $usuario->getAllPermissions()->where('tipoReporte', '=', 'Reportes de Inventarios')->pluck('name')->toArray();
    $permisosReportesGerenciales = $usuario->getAllPermissions()->where('tipoReporte', '=', 'Reportes Gerenciales')->pluck('name')->toArray();

@endphp
<ul class="children">


    @if($permisosReportesCompras != null)
    <li @if (request()->routeIs('vista.reportes.compras')) class="active" @endif><a href="{{ route('vista.reportes.compras') }}">
    Compras</a></li>
    @endif
    {{-- hacemos de gastos ahora --}}
    @if($permisosReportesGastos != null)
    <li @if (request()->routeIs('vista.reportes.gastos')) class="active" @endif><a href="{{ route('vista.reportes.gastos') }}">
    Gastos</a></li>
    @endif

    @if($permisosReportesCxp != null)
    <li @if (request()->routeIs('vista.reportes.cxp')) class="active" @endif><a href="{{ route('vista.reportes.cxp') }}">
    Cuentas por Pagar</a></li>
    @endif
    
    @if($permisosReportesTesoreria != null)
    <li @if (request()->routeIs('vista.reportes.tesoreria')) class="active" @endif><a href="{{ route('vista.reportes.tesoreria') }}">
    Tesorería</a></li>
    @endif
    
    @if($permisosReportesVentas != null)
    <li @if (request()->routeIs('vista.reportes.ventas')) class="active" @endif><a href="{{ route('vista.reportes.ventas') }}">
    Ventas</a></li>
    @endif

    @if($permisosReportesCxc != null)
    <li @if (request()->routeIs('vista.reportes.cxc')) class="active" @endif><a href="{{ route('vista.reportes.cxc') }}">
    Cuentas por Cobrar</a></li>
    @endif

    @if($permisosReportesInventarios != null)
    <li @if (request()->routeIs('vista.reportes.inventarios')) class="active" @endif><a href="{{ route('vista.reportes.inventarios') }}">
    Inventarios</a></li>
    @endif

    @if($permisosReportesGerenciales != null)
    <li @if (request()->routeIs('vista.reportes.gerenciales')) class="active" @endif><a href="{{ route('vista.reportes.gerenciales') }}">
    Gerenciales</a></li>
    @endif

</ul>
