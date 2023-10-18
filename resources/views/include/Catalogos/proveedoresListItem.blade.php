<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('listaShow', ['id' => Crypt::encrypt($proveedor['listProvider_id'])]) }}" class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye" aria-hidden="true"></i></a>
            <a href="{{ route('listaEdit', ['id' => Crypt::encrypt($proveedor['listProvider_id'])]) }}" class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if($proveedor['listProvider_status'] == 'Alta')
            {!! Form::open(['route' => ['listaDestroy', 'id' => Crypt::encrypt($proveedor['listProvider_id'])], 'method'=>'DELETE', 'class'=>'deleteForm']) !!}
            <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
            {!! Form::close() !!}
            @endif
            
                                        
        </div>
    </td>

    <td>{{ $proveedor['listProvider_id']}}</td>
    <td>{{ $proveedor['listProvider_name']}}</td>
    <td>{{ $proveedor['listProvider_status']}}</td>    
                      
</tr>