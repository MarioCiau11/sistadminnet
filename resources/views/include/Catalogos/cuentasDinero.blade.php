<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('catalogo.cuenta-dinero.show', ['cuentas_banco' => Crypt::encrypt($moneyAccount->moneyAccounts_id)]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('catalogo.cuenta-dinero.edit', ['cuentas_banco' => Crypt::encrypt($moneyAccount->moneyAccounts_id)]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($moneyAccount->moneyAccounts_status == 'Alta')
                {!! Form::open([
                    'route' => ['catalogo.cuenta-dinero.destroy', 'cuentas_banco' => Crypt::encrypt($moneyAccount->moneyAccounts_id)],
                    'method' => 'DELETE',
                    'id' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif
        </div>
    </td>
    <td>{{ $moneyAccount->moneyAccounts_key }}</td>
    <td>{{ $moneyAccount->instFinancial_name }}</td>
    <td>{{ $moneyAccount->moneyAccounts_numberAccount }}</td>
    <td>{{ $moneyAccount->moneyAccounts_keyAccount }}</td>
    <td>{{ $moneyAccount->moneyAccounts_referenceBank }}</td>
    <td>{{ $moneyAccount->moneyAccounts_bankAgreement }}</td>
    <td>{{ $moneyAccount->moneyAccounts_accountType == 'Caja' ? 'Caja/Efectivos' : 'Banco' }}</td>
    <td>{{ $moneyAccount->money_key }}</td>
    <td>{{ $moneyAccount->companies_name }}</td>
    <td>{{ $moneyAccount->moneyAccounts_status }}</td>
</tr>
