 <tr>
     <td class="td-option">
         <div class="contenedor-opciones">
             <a href="{{ route('configuracion.monedas.show', ['moneda' => Crypt::encrypt($money['money_id'])]) }}"
                 class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                     aria-hidden="true"></i></a>
             <a href="{{ route('configuracion.monedas.edit', ['moneda' => Crypt::encrypt($money['money_id'])]) }}"
                 class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                     class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

             @if ($money['money_status'] == 'Alta')
                 {!! Form::open([
                     'route' => ['configuracion.monedas.destroy', 'moneda' => Crypt::encrypt($money['money_id'])],
                     'method' => 'DELETE',
                     'class' => 'deleteForm',
                 ]) !!}
                 <a href="" class="delete" data-toggle="tooltip" data-placement="top"
                     title="Eliminar registro"><i class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                 {!! Form::close() !!}
             @endif


         </div>
     </td>

     <td>{{ $money['money_key'] }}</td>
     <td>{{ $money['money_name'] }}</td>
     <td>{{ (float) $money['money_change'] }}</td>
     <td>{{ $money['money_descript'] }}</td>
     <td>{{ $money['money_keySat'] }}</td>
     <td>{{ $money['money_status'] }}</td>
 </tr>
