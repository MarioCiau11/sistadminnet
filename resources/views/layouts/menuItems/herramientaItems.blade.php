<ul class="children">
    {{-- <li  @if(request()->routeIs("herramienta.index")) class="active" @endif><a href="{{ route('herramienta.index')}}">Movimientos al inventario</a></li> --}}

    @if(auth()->user()->can('Cambio de costos'))
    <li  @if(request()->routeIs("herramienta.cambioCostos.index")) class="active" @endif><a href="{{ route('herramienta.cambioCostos.index')}}">Cambio de costos</a></li>
    @endif
    @if(auth()->user()->can('Cambio de Precios de Venta'))
    <li  @if(request()->routeIs("herramienta.cambioPreciosVenta.index")) class="active" @endif><a href="{{ route('herramienta.cambioPreciosVenta.index')}}">Cambio de Precios de Venta</a></li>
    @endif
</ul>
  