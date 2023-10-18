<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesVentasClienteExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_CUSTOMERS_CATEGORY;
use App\Models\agrupadores\CAT_CUSTOMERS_GROUP;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CAT_DEPOTS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\catalogos\CONF_UNITS;
use App\Models\modulos\PROC_SALES;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportesVentasArticuloClientesController extends Controller
{
    public function index()
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        $select_sucursales = $this->selectSucursales();
        $selectMonedas = $this->getMonedas();
        $unidad = $this->selectUnidades();
        $almacen = $this->selectAlmacenes();
        $select_users = $this->selectUsuarios();
        $articulos = $this->selectArticulos();
        $clientes = $this->selectClientes();
        $categorias = $this->selectCategoria();
        $grupos = $this->selectGrupo();
        $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
            ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
            ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->where('PROC_SALES.sales_company', session('company')->companies_key)
            ->where('PROC_SALES.sales_branchOffice', session('sucursal')->branchOffices_key)
            ->where('PROC_SALES.sales_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->orderBy('PROC_SALES.created_at', 'desc')
            //mostramos solo 50 registros
            ->paginate(25);

        // dd($ventas);

        return view('page.Reportes.Ventas.indexVentasCliente', compact('select_sucursales', 'selectMonedas', 'unidad', 'almacen', 'select_users', 'parametro', 'ventas', 'articulos', 'clientes', 'categorias', 'grupos'));
    }

    public function reportesVentasClienteAction(Request $request)
    {
        $nameCliente = $request->nameCliente;
        $nameCategoria = $request->nameCategoria;
        $nameGrupo = $request->nameGrupo;
        $nameMov = $request->nameMov;
        $nameArticulo = $request->nameArticulo;
        $nameUnidad = $request->nameUnidad;
        $nameUsuario = $request->nameUsuario;
        $status = $request->status;
        $nameFecha = $request->nameFecha;
        $nameAlmacen = $request->nameAlmacen;
        $nameSucursal = $request->nameSucursal;
        $nameMoneda = $request->nameMoneda;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;

        //  dd($request->all());

        //esto nos sirve para el rango de fechas, para validar que no estén vacíos
        if (
            $fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas"
        ) {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        switch ($request->input('action')) {
            case 'Búsqueda':
                $reportes_collection_filtro = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
                    ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                    ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
                    ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
                    ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->whereSalesCustomer($nameCliente)
                    ->whereCustomerCategory($nameCategoria)
                    ->whereCustomerGroup($nameGrupo)
                    ->whereSalesMovement($nameMov)
                    ->whereSalesArticle($nameArticulo)
                    ->whereSalesUnit($nameUnidad)
                    ->whereSalesUser($nameUsuario)
                    ->whereSalesStatus($status)
                    ->whereSalesDate($nameFecha)
                    ->whereSalesDepot($nameAlmacen)
                    ->whereSalesBranchOffice($nameSucursal)
                    ->whereSalesMoney($nameMoneda)
                    ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                    ->orderBy('PROC_SALES.created_at', 'desc')
                    ->get();

                $reportes_filtro_array = $reportes_collection_filtro->toArray();

                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.reportes.ventas-cliente-articulo')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameCliente', $nameCliente)
                    ->with('nameCategoria', $nameCategoria)
                    ->with('nameGrupo', $nameGrupo)
                    ->with('nameMov', $nameMov)
                    ->with('nameArticulo', $nameArticulo)
                    ->with('nameUnidad', $nameUnidad)
                    ->with('nameUsuario', $nameUsuario)
                    ->with('status', $status)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameAlmacen', $nameAlmacen)
                    ->with('nameSucursal', $nameSucursal)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);

                break;

            case 'Exportar excel':
                $venta = new ReportesVentasClienteExport($nameCliente, $nameCategoria, $nameGrupo, $nameMov, $nameArticulo, $nameUnidad, $nameUsuario, $status, $nameFecha, $nameAlmacen, $nameSucursal, $nameMoneda);
                return Excel::download($venta, 'Reporte Ventas Cliente.xlsx');
                break;

            case 'Exportar PDF':
                $venta = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
                    ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                    ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
                    ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
                    ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                    ->whereSalesCustomer($nameCliente)
                    ->whereCustomerCategory($nameCategoria)
                    ->whereCustomerGroup($nameGrupo)
                    ->whereSalesMovement($nameMov)
                    ->whereSalesArticle($nameArticulo)
                    ->whereSalesUnit($nameUnidad)
                    ->whereSalesUser($nameUsuario)
                    ->whereSalesStatus($status)
                    ->whereSalesDate($nameFecha)
                    ->whereSalesDepot($nameAlmacen)
                    ->whereSalesBranchOffice($nameSucursal)
                    ->whereSalesMoney($nameMoneda)
                    ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                    ->orderBy('PROC_SALES.created_at', 'asc')
                    ->get();
                // dd($venta);

                if ($venta->isEmpty()) {
                    return redirect()->route('vista.reportes.ventas-cliente-articulo')->with('message', 'No se pudo generar el reporte, no hay datos para mostrar.')->with('status', false);
                } else {

                    $collectionVentas = collect($venta);
                    $clientesVenta = $collectionVentas->unique('sales_customer')->unique()->all();

                    $sucursal_almacen = $collectionVentas->unique('sales_branchOffice')->unique()->first();

                    $sucursal = $sucursal_almacen->branchOffices_key . '-' . $sucursal_almacen->branchOffices_name;

                    $almacen = $sucursal_almacen->depots_key . '-' . $sucursal_almacen->depots_name;

                    $clientesPorVentas = [];
                    foreach ($clientesVenta as $cliente) {

                        $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
                            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
                            ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
                            ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                            ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                            ->whereSalesCustomer($cliente->sales_customer)
                            ->whereCustomerCategory($nameCategoria)
                            ->whereCustomerGroup($nameGrupo)
                            ->whereSalesMovement($nameMov)
                            ->whereSalesArticle($nameArticulo)
                            ->whereSalesUnit($nameUnidad)
                            ->whereSalesUser($nameUsuario)
                            ->whereSalesStatus($status)
                            ->whereSalesDate($nameFecha)
                            ->whereSalesDepot($nameAlmacen)
                            ->whereSalesBranchOffice($nameSucursal)
                            ->whereSalesMoney($nameMoneda)
                            ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)

                            ->orderBy('PROC_SALES.created_at', 'asc')
                            ->get();


                        $arrayClientes = $cliente->toArray();
                        $clientesPorVentas[] = array_merge($arrayClientes, ['ventas' => $ventas->toArray()]);
                    }

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

                    switch ($nameFecha) {

                            // $date = $date->format('l jS \\of F Y h:i:s A');
                        case 'Hoy':


                            $nameFecha = Carbon::now()->isoFormat('LL');
                            // ->format('l jS \\of F Y');

                            break;
                        case 'Ayer':
                            $nameFecha = new Carbon('yesterday');
                            break;
                        case 'Semana':
                            $fecha_actual = Carbon::now()->isoFormat('LL');
                            $fecha_semana = new Carbon('last week');
                            $fecha_formato = $fecha_semana->isoFormat('LL');
                            $nameFecha = 'Del ' . $fecha_formato . ' al ' . $fecha_actual;
                            break;
                        case 'Mes':
                            $now = Carbon::now();
                            $start    = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                            $end      = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');

                            $fecha_inicial = $start->format('Y-m-d');
                            $fecha_fin = $end->format('Y-m-d');
                            $fechaInicio = Carbon::parse($fecha_inicial)->isoFormat('LL');
                            $fechaFinal = Carbon::parse($fecha_fin)->isoFormat('LL');
                            $nameFecha = 'Del ' . $fechaInicio . ' al ' . $fechaFinal;
                            break;
                        case 'Año Móvil':
                            $fecha_actual = Carbon::now()->isoFormat('LL');
                            $fecha_año_actual = Carbon::now()->format('Y');
                            $fecha_inicial = $fecha_año_actual . '-01-01';
                            $fecha_formato = Carbon::parse($fecha_inicial)->isoFormat('LL');
                            $nameFecha = 'Del ' . $fecha_formato . ' al ' . $fecha_actual;

                            break;
                        case 'Año Pasado':
                            $fecha_año_inicioMes_pasado = new Carbon('last year');
                            $formato_fecha_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                            $inicoAñoPasado = $formato_fecha_inicioMes_pasado . '-01-01';
                            $finAñoPasado = $formato_fecha_inicioMes_pasado . '-12-31';
                            $fecha_inicio_formato = Carbon::parse($inicoAñoPasado)->isoFormat('LL');
                            $fecha_fin_formato = Carbon::parse($finAñoPasado)->isoFormat('LL');
                            $nameFecha = 'Del ' . $fecha_inicio_formato . ' al ' . $fecha_fin_formato;
                            break;
                        default:
                            $fechaInicio = Carbon::parse($fechaInicio)->isoFormat('LL');
                            $fechaFinal = Carbon::parse($fechaFinal)->isoFormat('LL');
                            $nameFecha = $fechaInicio . ' - ' . $fechaFinal;

                            break;
                    }


                    $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->orderBy('generalParameters_id', 'desc')->get();

                    $pdf = PDF::loadView('page.Reportes.Ventas.ventasCliente-reporte', ['logo' => $logoBase64, 'clientesVentas' => $clientesPorVentas, 'fecha' => $nameFecha, 'almacen' => $almacen, 'sucursal' => $sucursal, 'status' => $status, 'venta' => $venta, 'parametro' => $parametro, 'moneda' => $nameMoneda]);
                    $pdf->set_paper('a4', 'landscape');

                    return $pdf->stream();
                }




                break;
        }
    }

    function SelectSucursales()
    {
        $sucursales = CAT_BRANCH_OFFICES::WHERE('branchOffices_status', '=', 'Alta')
            ->WHERE('branchOffices_companyId', '=', session('company')->companies_key)->get();
        $sucursales_array = array();
        $sucursales_array['Todos'] = 'Todos';
        foreach ($sucursales as $sucursal) {
            $sucursales_array[$sucursal->branchOffices_key] = $sucursal->branchOffices_name;
        }
        return $sucursales_array;
    }

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

    function selectUnidades()
    {
        $unidades = CONF_UNITS::where('units_status', '=', 'Alta')->get();
        $unidades_array = array();
        foreach ($unidades as $unidades) {
            $unidades_array[$unidades->Todos] = 'Todos';
            $unidades_array[$unidades->units_unit] = $unidades->units_unit;
        }
        return $unidades_array;
    }

    function selectAlmacenes()
    {
        $almacen = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'CAT_BRANCH_OFFICES.branchOffices_companyId', '=', 'CAT_COMPANIES.companies_key')
            ->where('CAT_COMPANIES.companies_key', '=', session('company')->companies_key)
            ->where('CAT_BRANCH_OFFICES.branchOffices_key', '=', session('sucursal')->branchOffices_key)
            ->where('CAT_DEPOTS.depots_status', '=', 'Alta')
            ->select('CAT_DEPOTS.*')->get();
        $almacenes_array = array();
        $almacenes_array['Todos'] = 'Todos';
        foreach ($almacen as $almacenes) {
            $almacenes_array[$almacenes->depots_key] = $almacenes->depots_key . ' - ' . $almacenes->depots_name;
        }
        return $almacenes_array;
    }

    function selectUsuarios()
    {
        $usuarios = User::where('user_status', '=', 'Alta')->get();
        $usuarios_array = array();
        $usuarios_array['Todos'] = 'Todos';
        foreach ($usuarios as $usuario) {
            $usuarios_array[$usuario->username] = $usuario->username;
        }
        return $usuarios_array;
    }


    function selectArticulos()
    {
        $articulos = CAT_ARTICLES::where('articles_status', '=', 'Alta')->orderBy('articles_id', 'asc')->get();
        $articulos_array = array();
        foreach ($articulos as $articulos) {
            $articulos_array[$articulos->Todos] = 'Todos';
            $articulos_array[$articulos->articles_descript] = $articulos->articles_key . ' - ' . $articulos->articles_descript;
        }
        return $articulos_array;
    }

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
