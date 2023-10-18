<?php
$usuario = auth()->user();

$permisosGastos = $usuario->getAllPermissions()->where('categoria', '=', 'Gastos')->pluck('name')->toArray();
$permisosCuentasPorCobrar = $usuario->getAllPermissions()->where('categoria', '=', 'Cuentas por cobrar')->pluck('name')->toArray();
$permisosCuentasPorPagar = $usuario->getAllPermissions()->where('categoria', '=', 'Cuentas por pagar')->pluck('name')->toArray();
$permisosTesoreria = $usuario->getAllPermissions()->where('categoria', '=', 'Tesorería')->pluck('name')->toArray();

?>



<ul class="children">
    @if (count($permisosCuentasPorPagar) > 0)
    <li @if (request()->routeIs('vista.modulo.cuentasPagar.index')) class="active" @endif><a
            href="{{ route('vista.modulo.cuentasPagar.index') }}">Cuentas por Pagar</a></li>
    @endif
    @if (count($permisosCuentasPorCobrar) > 0)
    <li @if (request()->routeIs('vista.modulo.cuentasCobrar.index')) class="active" @endif><a
            href="{{ route('vista.modulo.cuentasCobrar.index') }}">Cuentas por Cobrar</a></li>
    @endif
    @if (count($permisosGastos) > 0)
        <li @if (request()->routeIs('vista.modulo.gastos.index')) class="active" @endif><a
            href="{{ route('vista.modulo.gastos.index') }}">Control de Gastos</a></li>
    @endif
    @if (count($permisosTesoreria) > 0)
        <li @if (request()->routeIs('vista.modulo.tesoreria.index')) class="active" @endif><a
            href="{{ route('vista.modulo.tesoreria.index') }}">Tesorería/Bancos</a></li>
    @endif
</ul>
