<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('catalogo.empresa.show', ['empresa' => Crypt::encrypt($empresa['companies_id'])]) }}" class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye" aria-hidden="true"></i></a>
            <a href="{{ route('catalogo.empresa.edit', ['empresa' => Crypt::encrypt($empresa['companies_id'])]) }}" class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if($empresa['companies_status'] == 'Alta')
            {!! Form::open(['route' => ['catalogo.empresa.destroy', 'empresa' => Crypt::encrypt($empresa['companies_id'])], 'method'=>'DELETE', 'class'=>'deleteForm']) !!}
            <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
            {!! Form::close() !!}
            @endif
            
                                        
        </div>
    </td>

    <td>{{ $empresa['companies_key']}}</td>
    <td>{{ $empresa['companies_name']}}</td>
    <td>{{ $empresa['companies_nameShort']}}</td>
    <td>{{ $empresa['companies_rfc']}}</td>  
    <td>{{ $empresa['companies_status']}}</td>    
    <td>{{ $empresa['companies_logo']}}</td>                  
</tr>