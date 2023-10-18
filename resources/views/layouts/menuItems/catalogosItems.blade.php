<ul class="children">

    @can('Empresa')
        <li @if (request()->routeIs('catalogo.empresa.index') ||
                request()->routeIs('catalogo.empresa.create') ||
                request()->routeIs('catalogo.empresa.show') ||
                request()->routeIs('catalogo.empresa.edit')) class="active" @endif><a href="{{ route('catalogo.empresa.index') }}">
                Empresas</a></li>
    @endcan

    @can('Sucursal')
        <li @if (request()->routeIs('catalogo.sucursal.index') ||
                request()->routeIs('catalogo.sucursal.create') ||
                request()->routeIs('catalogo.sucursal.show') ||
                request()->routeIs('catalogo.sucursal.edit')) class="active" @endif><a
                href="{{ route('catalogo.sucursal.index') }}">Sucursales</a></li>
    @endcan

    @can('Almacén')
        <li @if (request()->routeIs('catalogo.almacen.index') ||
                request()->routeIs('catalogo.almacen.create') ||
                request()->routeIs('catalogo.almacen.show') ||
                request()->routeIs('catalogo.almacen.edit')) class="active" @endif><a
                href="{{ route('catalogo.almacen.index') }}">Almacenes</a></li>
    @endcan
    @can('Instituciones Financieras')
        <li @if (request()->routeIs('catalogo.instituciones-financieras.index') ||
                request()->routeIs('catalogo.instituciones-financieras.create') ||
                request()->routeIs('catalogo.instituciones-financieras.show') ||
                request()->routeIs('catalogo.instituciones-financieras.edit')) class="active" @endif><a
                href="{{ route('catalogo.instituciones-financieras.index') }}">Instituciones Financieras</a>
        </li>
    @endcan
    @can('Cuentas de Dinero')
        <li @if (request()->routeIs('catalogo.cuenta-dinero.index') ||
                request()->routeIs('catalogo.cuenta-dinero.create') ||
                request()->routeIs('catalogo.cuenta-dinero.show') ||
                request()->routeIs('catalogo.cuenta-dinero.edit')) class="active" @endif><a
                href="{{ route('catalogo.cuenta-dinero.index') }}">Cuentas de Banco/Efectivos</a></li>
    @endcan

    {{-- @can('Proveedores')
        <li @if (request()->routeIs('catalogo.proveedor.index') ||
                request()->routeIs('catalogo.proveedor.create') ||
                request()->routeIs('catalogo.proveedor.show') ||
                request()->routeIs('catalogo.proveedor.edit')) class="active" @endif><a
                href="{{ route('catalogo.proveedor.index') }}">Proveedores/Acreedores</a></li>
    @endcan --}}
     @if(Auth::user()->can('Proveedores') || Auth::user()->can('Lista de Artículos'))
     <li class="dropdown-submenu @if(strpos(Request::url(), 'proveedor') || strpos(Request::url(), 'listaIndex') ) open @endif">
          <a href="#" class="subCatalogoParent"><span class="span-arrow">Proveedores<div class="arrow"></div></span></a>
          <ul class="subCatalago" style="display: none">

          @can('Proveedores')
               <li  @if(request()->routeIs("catalogo.proveedor.index") || request()->routeIs("catalogo.proveedor.create") || request()->routeIs('catalogo.proveedor.show') || request()->routeIs('catalogo.proveedor.edit')) class="active" @endif><a href="{{ route('catalogo.proveedor.index')}}">Proveedores/Acreedores</a></li>
          @endcan

             @can('Lista de Artículos')
                  <li  @if(request()->routeIs("listaIndex")) class="active" @endif><a href="{{ route('listaIndex')}}">Lista de Precios Proveedor</a></li>
             @endcan

           

           </ul>
     </li>
     @endif
    @can('Clientes')
        <li @if (request()->routeIs('catalogo.clientes.index') ||
                request()->routeIs('catalogo.clientes.create') ||
                request()->routeIs('catalogo.clientes.show') ||
                request()->routeIs('catalogo.clientes.edit')) class="active" @endif><a
                href="{{ route('catalogo.clientes.index') }}">Clientes</a></li>
    @endcan
    @can('Artículos')
        <li @if (request()->routeIs('catalogo.articulos.index') ||
                request()->routeIs('catalogo.articulos.create') ||
                request()->routeIs('catalogo.articulos.show') ||
                request()->routeIs('catalogo.articulos.edit')) class="active" @endif><a
                href="{{ route('catalogo.articulos.index') }}">Productos/Items</a></li>
    @endcan

    @can('Conceptos de Gastos')
        <li @if (request()->routeIs('catalogo.concepto-gastos.index') ||
                request()->routeIs('catalogo.concepto-gastos.create') ||
                request()->routeIs('catalogo.concepto-gastos.show') ||
                request()->routeIs('catalogo.concepto-gastos.edit')) class="active" @endif><a
                href="{{ route('catalogo.concepto-gastos.index') }}">Razones de Gastos</a></li>
    @endcan
    @can('Agentes')
        <li @if (request()->routeIs('catalogo.agentes.index') ||
                request()->routeIs('catalogo.agentes.create') ||
                request()->routeIs('catalogo.agentes.show') ||
                request()->routeIs('catalogo.agentes.edit')) class="active" @endif><a
                href="{{ route('catalogo.agentes.index') }}">Operativos</a></li>
    @endcan


    @can('Vehículos')
        <li @if (request()->routeIs('catalogo.vehiculos.index') ||
                request()->routeIs('catalogo.vehiculos.create') ||
                request()->routeIs('catalogo.vehiculos.show') ||
                request()->routeIs('catalogo.vehiculos.edit')) class="active" @endif><a
                href="{{ route('catalogo.vehiculos.index') }}">Vehículos/Camiones</a></li>
    @endcan
    
    <li @if (request()->routeIs('catalogo.centroCostos.index') ||
    request()->routeIs('catalogo.centroCostos.create') ||
    request()->routeIs('catalogo.centroCostos.show') ||
    request()->routeIs('catalogo.centroCostos.edit')) class="active" @endif><a
    href="{{ route('catalogo.centroCostos.index') }}">Centro de Costos</a></li>
</ul>
