<ul class="children">
    @if (auth()->user()->can('Orden de Compra E') ||
            auth()->user()->can('Orden de Compra C') ||
            auth()->user()->can('Entrada por Compra E') ||
            auth()->user()->can('Entrada por Compra C') ||
            auth()->user()->can('Rechazo de Compra E') ||
            auth()->user()->can('Rechazo de Compra C'))
        <li @if (request()->routeIs('vista.modulo.compras')) class="active" @endif><a
                href="{{ route('vista.modulo.compras') }}">Compras</a></li>
    @endif
    @if (auth()->user()->can('Ajuste de Inventario E') ||
            auth()->user()->can('Ajuste de Inventario C') ||
            auth()->user()->can('Transferencia entre Alm. E') ||
            auth()->user()->can('Transferencia entre Alm. C') ||
            auth()->user()->can('Salida por Traspaso E') ||
            auth()->user()->can('Salida por Traspaso C') ||
            auth()->user()->can('Entrada por Traspaso E') ||
            auth()->user()->can('Entrada por Traspaso C'))
        <li @if (request()->routeIs('vista.modulo.inventarios')) class="active" @endif><a
                href="{{ route('vista.modulo.inventarios') }}">Inventarios</a></li>
    @endif
</ul>
