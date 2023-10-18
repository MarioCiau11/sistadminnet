<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesCXPAntiguedadSaldosExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_PROVIDER_CATEGORY;
use App\Models\agrupadores\CAT_PROVIDER_GROUP;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE_P;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportesCXPAntiguedadSaldosController extends Controller
{
    public function index()
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        $proveedor = $this->selectProveedores();
        $categorias = $this->selectCategoria();
        $grupos = $this->selectGrupo();
        $selectMonedas = $this->getMonedas();
        $cuentasxpagar = PROC_ACCOUNTS_PAYABLE_P::join('CAT_PROVIDERS', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_provider', '=', 'CAT_PROVIDERS.providers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('CONF_MONEY', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_money', '=', 'CONF_MONEY.money_key')
            ->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_company', '=', session('company')->companies_key)
            ->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->whereIn('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_movement', ['Factura de Gasto', 'Entrada por Compra'])

            ->orderBy('PROC_ACCOUNTS_PAYABLE_P.updated_at', 'DESC')

            ->get();

        return view('page.Reportes.CuentasXPagar.indexCXPAntiguedadSaldos', compact('proveedor', 'selectMonedas', 'parametro', 'cuentasxpagar', 'categorias', 'grupos'));
    }

    public function reportesCXPAction(Request $request)
    {
        $nameProveedorUno = $request->nameProveedorUno;
        $nameProveedorDos = $request->nameProveedorDos;
        $nameProveedor = $request->nameProveedor;
        $nameCategoria = $request->nameCategoria;
        $nameGrupo = $request->nameGrupo;
        $namePlazo = $request->namePlazo;
        $nameMoneda = $request->nameMoneda;


        switch ($request->input('action')) {
            case 'Búsqueda':
                $reportes_collection_filtro = PROC_ACCOUNTS_PAYABLE_P::join('CAT_PROVIDERS', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CONF_MONEY', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_money', '=', 'CONF_MONEY.money_key')
                    ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_company', '=', 'CAT_COMPANIES.companies_key')
                    ->whereIn('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_movement', ['Factura de Gasto', 'Entrada por Compra'])
                    ->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_company', '=', session('company')->companies_key)
                    ->whereAccountsPayablePProvider($nameProveedorUno, $nameProveedorDos, $nameProveedor)
                    ->whereProviderCategory($nameCategoria)
                    ->whereProviderGroup($nameGrupo)
                    ->whereAccountsPayablePMoratoriumDays($namePlazo)
                    ->whereAccountsPayablePMoney($nameMoneda)
                    ->orderBy('PROC_ACCOUNTS_PAYABLE_P.updated_at', 'DESC')

                    ->get();


                $reportes_filtro_array = $reportes_collection_filtro->toArray();

                return redirect()->route('vista.reportes.cxp-antiguedad-saldos')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameProveedorUno', $nameProveedorUno)
                    ->with('nameProveedorDos', $nameProveedorDos)
                    ->with('nameProveedor', $nameProveedor)
                    ->with('nameCategoria', $nameCategoria)
                    ->with('nameGrupo', $nameGrupo)
                    ->with('namePlazo', $namePlazo)
                    ->with('nameMoneda', $nameMoneda);

                break;

            case 'Exportar excel':
                try {
                    $cxp = new ReportesCXPAntiguedadSaldosExport($nameProveedorUno, $nameProveedorDos, $nameProveedor, $nameCategoria, $nameGrupo, $namePlazo, $nameMoneda);
                    return Excel::download($cxp, 'Reporte de Antiguedad de Saldos.xlsx');
                } catch (\Throwable $th) {
                    return redirect()->route('vista.reportes.cxp-antiguedad-saldos')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                }





                break;

            case 'Exportar PDF':

                $cxp = PROC_ACCOUNTS_PAYABLE_P::join('CAT_PROVIDERS', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CONF_MONEY', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_money', '=', 'CONF_MONEY.money_key')
                    ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_company', '=', 'CAT_COMPANIES.companies_key')
                    ->whereIn('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_movement', ['Factura de Gasto', 'Entrada por Compra'])
                    ->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_company', '=', session('company')->companies_key)
                    ->whereAccountsPayablePProvider($nameProveedorUno, $nameProveedorDos, $nameProveedor)
                    ->whereAccountsPayablePMoratoriumDays($namePlazo)
                    ->whereAccountsPayablePMoney($nameMoneda)
                    ->whereProviderCategory($nameCategoria)
                    ->whereProviderGroup($nameGrupo)
                    ->orderBy('PROC_ACCOUNTS_PAYABLE_P.updated_at', 'DESC')

                    ->get();

                if ($cxp->isEmpty()) {
                    return redirect()->route('vista.reportes.cxp-antiguedad-saldos')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                } else {
                    $collectionCXP = collect($cxp);

                    // dd($collectionCXP, $proveedoresCXP);
                    $sucursal_almacen = $collectionCXP->unique('accountsPayableP_branchOffice')->unique()->first();

                    $sucursal = $sucursal_almacen->branchOffices_key . '-' . $sucursal_almacen->branchOffices_name;

                    $proveedoresPorCXP = [];

                    foreach ($collectionCXP as $proveedor) {
                        $cuentasxp = PROC_ACCOUNTS_PAYABLE_P::join('CAT_PROVIDERS', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_provider', '=', 'CAT_PROVIDERS.providers_key')
                            ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                            ->join('CONF_MONEY', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_money', '=', 'CONF_MONEY.money_key')
                            ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_company', '=', 'CAT_COMPANIES.companies_key')
                            ->whereIn('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_movement', ['Factura de Gasto', 'Entrada por Compra'])
                            ->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_company', '=', session('company')->companies_key)
                            ->whereAccountsPayablePProvider($proveedor->providers_key, $proveedor->providers_key, $proveedor->providers_key)
                            ->whereAccountsPayablePMoney($proveedor->accountsPayableP_money)
                            ->whereAccountsPayablePMoratoriumDays($namePlazo)
                            ->whereProviderCategory($nameCategoria)
                            ->whereProviderGroup($nameGrupo)
                            ->orderBy('PROC_ACCOUNTS_PAYABLE_P.updated_at', 'DESC')
                            ->get();
                        // dd($cuentasxp);

                        if (!array_key_exists($proveedor->providers_name . '-' . $proveedor->accountsPayableP_money, $proveedoresPorCXP)) {
                            $proveedoresPorCXP[$proveedor->providers_name . '-' . $proveedor->accountsPayableP_money] = $proveedor->toArray();
                        }
                        $proveedoresPorCXP[$proveedor->providers_name . '-' . $proveedor->accountsPayableP_money] =  array_merge($proveedoresPorCXP[$proveedor->providers_name . '-' . $proveedor->accountsPayableP_money], ['cuentasxp' => $cuentasxp->toArray()]);
                    }

                    // dd($proveedoresPorCXP);
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

                    // dd($proveedoresPorCXP);

                    $pdf = PDF::loadView('page.Reportes.CuentasXPagar.antiguedadSaldosCXP-reporte', ['logo' => $logoBase64, 'cxp' => $cxp, 'nameProveedor' => $nameProveedorUno,  'nameMoneda' => $nameMoneda, 'proveedoresEstado' => $proveedoresPorCXP, 'sucursal' => $sucursal,]);
                    $pdf->set_paper('a4', 'landscape');
                    return $pdf->stream();
                }
                break;
        }
    }

    function selectProveedores()
    {
        $proveedores = CAT_PROVIDERS::where('providers_status', '=', 'Alta')->get();
        $proveedores_array = array();
        foreach ($proveedores as $proveedores) {
            $proveedores_array[$proveedores->Todos] = 'Todos';
            $proveedores_array[$proveedores->providers_key] = $proveedores->providers_key . ' - ' . $proveedores->providers_name;
        }
        return $proveedores_array;
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

    function selectCategoria()
    {
        $categorias = CAT_PROVIDER_CATEGORY::where('categoryProvider_status', '=', 'Alta')->get();
        $categorias_array = array();
        foreach ($categorias as $categorias) {
            $categorias_array[$categorias->Todos] = 'Todos';
            $categorias_array[$categorias->categoryProvider_name] = $categorias->categoryProvider_name;
        }
        return $categorias_array;
    }

    function selectGrupo()
    {
        $grupos = CAT_PROVIDER_GROUP::where('groupProvider_status', '=', 'Alta')->get();
        $grupos_array = array();
        foreach ($grupos as $grupos) {
            $grupos_array[$grupos->Todos] = 'Todos';
            $grupos_array[$grupos->groupProvider_name] = $grupos->groupProvider_name;
        }
        return $grupos_array;
    }
}
