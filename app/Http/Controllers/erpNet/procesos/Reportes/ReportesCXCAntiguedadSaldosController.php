<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesCXCAntiguedadSaldosExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_CUSTOMERS_CATEGORY;
use App\Models\agrupadores\CAT_CUSTOMERS_GROUP;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE_P;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportesCXCAntiguedadSaldosController extends Controller
{
    /**
     * Función del index de la vista de reportes de cuentas por cobrar
     */
    public function index()
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        /**
         * Se obtienen los clientes, categorias, grupos, monedas y parametros generales
         */
        $clientes = $this->selectClientes();
        $categorias = $this->selectCategoria();
        $grupos = $this->selectGrupo();
        $selectMonedas = $this->getMonedas();

        /**
         * Se obtienen los datos de las cuentas por cobrar
         * Se hace un join con las tablas de clientes, sucursales, compañias, monedas
         * Se filtra por compañia, sucursal, moneda y movimiento
         * Se ordena por fecha de actualizacion        
         */
        $cuentasxcobrar = PROC_ACCOUNTS_RECEIVABLE_P::join('CAT_CUSTOMERS', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_customer', '=', 'CAT_CUSTOMERS.customers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('CONF_MONEY', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_money', '=', 'CONF_MONEY.money_key')
            ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_company', '=', session('company')->companies_key)
            ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_movement', '=', 'Factura')
            // ->orWhere('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_movement', '=', 'Entrada por Compra')
            ->orderBy('PROC_ACCOUNTS_RECEIVABLE_P.updated_at', 'DESC')
            ->get();

        /* retorna la vista con los datos de los clientes, categorias, grupos, monedas, parametros generales y cuentas por cobrar */
        return view('page.Reportes.CuentasXCobrar.indexCXCAntiguedadSaldos', compact('clientes', 'selectMonedas', 'parametro', 'cuentasxcobrar', 'categorias', 'grupos'));
    }
    /**
     * Esta función nos sirve para los botones de exportar a excel y PDF así como para el filtro de la vista de reportes de cuentas por cobrar
     */
    public function reportesCXCAction(Request $request)
    {
        /**
         * Se obtienen los valores del formulario de la vista para aplicar el filtro y lo almacenamos en variables
         */
        $nameClienteUno = $request->nameClienteUno;
        $nameClienteDos = $request->nameClienteDos;
        $nameCliente = $request->nameCliente;
        $nameCategoria = $request->nameCategoria;
        $nameGrupo = $request->nameGrupo;
        $namePlazo = $request->namePlazo;
        $nameMoneda = $request->nameMoneda;


        switch ($request->input('action')) {
            case 'Búsqueda':
                /* Obtener los datos de la base de datos y almacenarlos en la variable $reportes_collection_filtro */
                $reportes_collection_filtro = PROC_ACCOUNTS_RECEIVABLE_P::join('CAT_CUSTOMERS', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_customer', '=', 'CAT_CUSTOMERS.customers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('CONF_MONEY', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_money', '=', 'CONF_MONEY.money_key')
                    ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_movement', '=', 'Factura')
                    ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_company', '=', session('company')->companies_key)
                    // ->orWhere('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_movement', '=', 'Entrada por Compra')
                    ->WhereAccountsReceivablePCustomer($nameClienteUno, $nameClienteDos, $nameCliente)
                    ->whereCustomerCategory($nameCategoria)
                    ->whereCustomerGroup($nameGrupo)
                    ->whereAccountsReceivableMoratoriumDays($namePlazo)
                    ->whereAccountsReceivableMoney($nameMoneda)
                    ->orderBy('PROC_ACCOUNTS_RECEIVABLE_P.updated_at', 'DESC')
                    ->get();

                $reportes_filtro_array = $reportes_collection_filtro->toArray();

                /* Retornamos a la vista la vista con los datos. */
                return redirect()->route('vista.reportes.cxc-antiguedad-saldos')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameClienteUno', $nameClienteUno)
                    ->with('nameClienteDos', $nameClienteDos)
                    ->with('nameCliente', $nameCliente)
                    ->with('nameCategoria', $nameCategoria)
                    ->with('nameGrupo', $nameGrupo)
                    ->with('namePlazo', $namePlazo)
                    ->with('nameMoneda', $nameMoneda);

                break;

            case 'Exportar excel':
                /* Crear una nueva instancia de la clase ReportesCXCAntiguedadSaldosExport y luego devolver el archivo de Excel. */
                $cxc = new ReportesCXCAntiguedadSaldosExport($nameClienteUno, $nameClienteDos, $nameCliente, $nameCategoria, $nameGrupo, $namePlazo, $nameMoneda);
                return Excel::download($cxc, 'Reporte de Antiguedad de Saldos.xlsx');
                break;

            case 'Exportar PDF':

                /* Obtener los datos de la base de datos y almacenarlos en la variable $cxc */

                $cxc = PROC_ACCOUNTS_RECEIVABLE_P::join('CAT_CUSTOMERS', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_customer', '=', 'CAT_CUSTOMERS.customers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('CONF_MONEY', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_money', '=', 'CONF_MONEY.money_key')
                    ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_movement', '=', 'Factura')
                    ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_company', '=', session('company')->companies_key)
                    // ->orWhere('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_movement', '=', 'Entrada por Compra')
                    ->WhereAccountsReceivablePCustomer($nameClienteUno, $nameClienteDos, $nameCliente)
                    ->whereCustomerCategory($nameCategoria)
                    ->whereCustomerGroup($nameGrupo)
                    ->whereAccountsReceivableMoratoriumDays($namePlazo)
                    ->whereAccountsReceivableMoney($nameMoneda)
                    ->orderBy('PROC_ACCOUNTS_RECEIVABLE_P.updated_at', 'DESC')
                    ->get();

                /* Comprobando si la colección está vacía. Si está vacío, redirigirá a la ruta con un mensaje.
                    Si no está vacío, seguirá con el código. */
                if ($cxc->isEmpty()) {
                    return redirect()->route('vista.reportes.cxc-antiguedad-saldos')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                } else {




                    /* Obtenemos el primer valor único de la colección. */
                    $cxccollectionCXC = collect($cxc);

                    $sucursal_almacen = $cxccollectionCXC->unique('accountsReceivableP_branchOffice')->unique()->first();

                    $sucursal = $sucursal_almacen->branchOffices_key . '-' . $sucursal_almacen->branchOffices_name;

                    $clientesPorCXC = [];

                    /* Obtener los datos de la base de datos.. */
                    foreach ($cxccollectionCXC as $cliente) {
                        $cuentasxc = PROC_ACCOUNTS_RECEIVABLE_P::join('CAT_CUSTOMERS', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_customer', '=', 'CAT_CUSTOMERS.customers_key')
                            ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                            ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_company', '=', 'CAT_COMPANIES.companies_key')
                            ->join('CONF_MONEY', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_money', '=', 'CONF_MONEY.money_key')
                            ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_movement', '=', 'Factura')
                            ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_company', '=', session('company')->companies_key)
                            ->whereAccountsReceivablePCustomer($cliente->customers_key, $cliente->customers_key, $cliente->customers_businessName)
                            ->whereAccountsReceivableMoratoriumDays($namePlazo)
                            ->whereCustomerCategory($nameCategoria)
                            ->whereCustomerGroup($nameGrupo)
                            ->whereAccountsReceivableMoney($nameMoneda)
                            ->orderBy('PROC_ACCOUNTS_RECEIVABLE_P.updated_at', 'DESC')
                            ->get();



                        /* Fusión del array del cliente con el array de la cuenta por cobrar. */
                        if (!array_key_exists($cliente->customers_businessName . '-' . $cliente->accountsReceivableP_money, $clientesPorCXC)) {
                            $clientesPorCXC[$cliente->customers_businessName . '-' . $cliente->accountsReceivableP_money] = $cliente->toArray();
                        }
                        $clientesPorCXC[$cliente->customers_businessName . '-' . $cliente->accountsReceivableP_money] = array_merge($clientesPorCXC[$cliente->customers_businessName . '-' . $cliente->accountsReceivableP_money], ['cuentasxc' => $cuentasxc->toArray()]);
                    }

                    /**
                     * Obtener el logo de la empresa.
                     * Si el logo es nulo, se obtiene el logo por defecto.
                     * Si el logo por defecto es nulo, se obtiene una cadena vacía.
                     * Si el logo por defecto no es nulo, se obtiene la imagen en base64.
                     */
                    if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
                        $logoFile = null;
                    } else {
                        $logoFile = Storage::disk('empresas')->get(session('company')->companies_logo);
                    }

                    if ($logoFile == null) {
                        $logoFile = Storage::disk('empresas')->get('default.png');

                        if ($logoFile == null) {
                            $logoBase64 = '';
                        } else {
                            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
                        }
                    } else {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
                    }



                    /**
                     * En esta parte se genera el PDF con los datos obtenidos.
                     * Se devuelve el PDF en formato de stream.
                     */
                    $pdf = PDF::loadView('page.Reportes.CuentasXCobrar.antiguedadSaldosCXC-reporte', ['logo' => $logoBase64, 'cxc' => $cxc, 'nameProveedor' => $nameClienteUno,  'nameMoneda' => $nameMoneda, 'clientesEstado' => $clientesPorCXC, 'sucursal' => $sucursal,]);
                    $pdf->set_paper('a4', 'landscape');
                    return $pdf->stream();
                }
                break;
        }
    }

    /**
     * Función para obtener los clientes.
     * @return array
     */
    function selectClientes()
    {
        $clientes = CAT_CUSTOMERS::where('customers_status', '=', 'Alta')->get();
        $clientes_array = array();
        foreach ($clientes as $clientes) {
            $clientes_array[$clientes->Todos] = 'Todos';
            $clientes_array[$clientes->customers_key] = $clientes->customers_key . ' - ' . $clientes->customers_businessName;
        }
        return $clientes_array;
    }

    /**
     * Función para obtener las monedas.
     * @return array
     */
    public function getMonedas()
    {
        $monedas = CONF_MONEY::where('money_status', '=', 'Alta')->get();
        $monedas_array = array();
        $monedas_array['Todos'] = 'Todos';
        foreach ($monedas as $moneda) {
            $monedas_array[trim($moneda->money_key)] = $moneda->money_name;
        }
        return $monedas_array;
    }

    /**
     * Función para obtener las categorías.
     * @return array
     */
    function selectCategoria()
    {
        $categorias = CAT_CUSTOMERS_CATEGORY::where('categoryCostumer_status', '=', 'Alta')->get();
        $categorias_array = array();
        foreach ($categorias as $categorias) {
            $categorias_array[$categorias->Todos] = 'Todos';
            $categorias_array[$categorias->categoryCostumer_name] = $categorias->categoryCostumer_name;
        }
        return $categorias_array;
    }

    /**
     * Función para obtener los grupos.
     * @return array
     */
    function selectGrupo()
    {
        $grupos = CAT_CUSTOMERS_GROUP::where('groupCustomer_status', '=', 'Alta')->get();
        $grupos_array = array();
        foreach ($grupos as $grupos) {
            $grupos_array[$grupos->Todos] = 'Todos';
            $grupos_array[$grupos->groupCustomer_name] = $grupos->groupCustomer_name;
        }
        return $grupos_array;
    }
}
