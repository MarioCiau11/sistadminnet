 


<?php
$usuario = auth()->user();
$permisosConfiguracionGeneral = $usuario->getAllPermissions()->where('categoria', '=', 'Configuración')->pluck('name')->toArray();
$permisosCatalogos = $usuario->getAllPermissions()->where('categoria', '=', 'Catálogos')->pluck('name')->toArray();
$permisosComercial = $usuario->getAllPermissions()->where('categoria', '=', 'Ventas')->pluck('name')->toArray();
$permisosCompras = $usuario->getAllPermissions()->where('categoria', '=', 'Compras')->pluck('name')->toArray();
$permisosInventario = $usuario->getAllPermissions()->where('categoria', '=', 'Inventarios')->pluck('name')->toArray();
$permisosGastos = $usuario->getAllPermissions()->where('categoria', '=', 'Gastos')->pluck('name')->toArray();
$permisosCuentasPorCobrar = $usuario->getAllPermissions()->where('categoria', '=', 'Cuentas por cobrar')->pluck('name')->toArray();
$permisosCuentasPorPagar = $usuario->getAllPermissions()->where('categoria', '=', 'Cuentas por pagar')->pluck('name')->toArray();
$permisosTesoreria = $usuario->getAllPermissions()->where('categoria', '=', 'Tesorería')->pluck('name')->toArray();
$permisosReportes = $usuario->getAllPermissions()->where('categoria', '=', 'Reportes Módulos')->pluck('name')->toArray();
$permisosHerramientas = $usuario->getAllPermissions()->where('categoria', '=', 'Herramientas')->pluck('name')->toArray();

?>

 
 <li @if (request()->routeIs('dashboard.index')) class="active" @endif><a href="{{ route('dashboard.index') }}"><i
             class="fa fa-tachometer"></i> <span>Dashboard</span></a></li>

@if($permisosConfiguracionGeneral != null)
 <li @if (strpos(Request::url(), 'monedas') || strpos(Request::url(), 'formas-pago') || strpos(Request::url(), 'condiciones-termino') || strpos(Request::url(), 'concepto-procesos') || strpos(Request::url(), 'parametros-generales') || strpos(Request::url(), 'unidades-empaque') || strpos(Request::url(), 'unidades') || strpos(Request::url(), 'roles') || strpos(Request::url(), 'usuarios') || strpos(Request::url(), 'motivos-cancelacion')) class="active parent" @else class="parent" @endif><a href="#"><i
             class="fa fa-cogs" aria-hidden="true"></i> <span>Configuración General</span></a>
     @include('layouts.menuItems.configuracionGeneralItems')
 </li>
@endif

@if($permisosCatalogos != null)
 <li @if (strpos(Request::url(), 'empresa') || strpos(Request::url(), 'sucursal') || strpos(Request::url(), 'almacen') || strpos(Request::url(), 'cuentas-bancos') || strpos(Request::url(), 'razones-gastos') || strpos(Request::url(), 'instituciones-financieras') || strpos(Request::url(), 'clientes') || strpos(Request::url(), 'operativos') || strpos(Request::url(), 'vehiculos') || strpos(Request::url(), 'proveedor') || strpos(Request::url(), 'articulos') || strpos(Request::url(), 'centroCosto') || strpos(Request::url(), 'lista')) class="active parent" @else class="parent" @endif><a href="#"><i
             class="fa fa-book"></i> <span>Catálogos</span></a>
     @include('layouts.menuItems.catalogosItems')
 </li>
@endif

@if($permisosCompras != null || $permisosInventario != null) 
 <li @if (strpos(Request::url(), 'logistica')) class="active parent" @else class="parent" @endif><a href="#"><i
             class="fa fa-bar-chart-o"></i> <span>Abastecimiento y Logística</span></a>
     @include('layouts.menuItems.logisticaItems')
 </li>
@endif

 @if($permisosGastos != null || $permisosCuentasPorCobrar != null || $permisosCuentasPorPagar != null || $permisosTesoreria != null)
 <li @if (strpos(Request::url(), 'cuentas_por_pagar') || strpos(Request::url(), 'gastos')  || strpos(Request::url(), 'cuentas_por_cobrar') || strpos(Request::url(), 'tesoreria') ) class="active parent" @else class="parent" @endif><a href="#"><i
        class="fa-solid fa-magnifying-glass-chart"></i> <span>Gestión y Finanzas</span></a>
        @include('layouts.menuItems.GestionFinanzasItems')
</li>
@endif


@if($permisosComercial != null)
<li @if (strpos(Request::url(), 'comercial')) class="active parent" @else class="parent" @endif><a href="#"><i
        class="fa-solid fa-money-bill-transfer"></i> <span style="font-size: 13px">Ingresos y Comercialización</span></a>
@include('layouts.menuItems.comercialItems')
</li>
@endif
@if($permisosReportes != null)
 {{-- <li @if (request()->routeIs('vista.reportes')) class="active" @endif><a href="{{ route('vista.reportes') }}"><i
     class="fa-solid fa-newspaper"></i> <span>Reportería</span></a></li> --}}
     @endif
     <li @if (strpos(Request::url(), 'Reportecompras') || strpos(Request::url(), 'reportes') || strpos(Request::url(), 'ReporteGasto') || strpos(Request::url(), 'Reportecxp') || strpos(Request::url(), 'ReporteUtilidad') ) class="active parent" @else class="parent" @endif><a href="#"><i
             class="fa-solid fa-newspaper"></i> <span>Reportería</span></a>
             @include('layouts.menuItems.reportesItems')
     </li>
     

@if($permisosHerramientas != null)
<li @if (strpos(Request::url(), 'Herramienta') || strpos(Request::url(), 'herramienta')) class="active parent" @else class="parent" @endif><a href="#"><i
        class="glyphicon glyphicon-wrench"></i> <span>Herramientas</span></a>
        @include('layouts.menuItems.herramientaItems')
</li>
@endif






