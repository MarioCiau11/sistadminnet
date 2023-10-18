<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('catalogo.centroCostos.show', ['centroCosto' => Crypt::encrypt($costos['costCenter_id'])]) }}" class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye" aria-hidden="true"></i></a>
            <a href="{{ route('catalogo.centroCostos.edit', ['centroCosto' => Crypt::encrypt($costos['costCenter_id'])]) }}" class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if($costos['costCenter_status'] == 'Alta')
            {!! Form::open(['route' => ['catalogo.centroCostos.destroy', 'centroCosto' => Crypt::encrypt($costos['costCenter_id'])], 'method'=>'DELETE', 'class'=>'deleteForm']) !!}
            <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
            {!! Form::close() !!}
            @endif
            
                                        
        </div>
    </td>

    <td>{{ $costos['costCenter_key']}}</td>
    <td>{{ $costos['costCenter_name']}}</td>
    <td>{{ $costos['costCenter_status']}}</td>                 
</tr>