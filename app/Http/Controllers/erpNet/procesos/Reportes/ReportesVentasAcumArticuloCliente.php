<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesVentasAcumuladasExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_ARTICLES_CATEGORY;
use App\Models\agrupadores\CAT_ARTICLES_FAMILY;
use App\Models\agrupadores\CAT_ARTICLES_GROUP;
use App\Models\agrupadores\CAT_CUSTOMERS_CATEGORY;
use App\Models\agrupadores\CAT_CUSTOMERS_GROUP;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\PROC_SALES;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportesVentasAcumArticuloCliente extends Controller
{
    public function index()
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        $selectMonedas = $this->getMonedas();
        $articulos = $this->selectArticulos();
        $clientes = $this->selectClientes();
        $categorias = $this->selectCategoria();
        $grupos = $this->selectGrupo();
        $select_categoria = $this->selectCategoriaArt();
        $select_grupo = $this->selectGrupoArt();
        $select_familia = $this->selectFamiliaArt();
        $select_anio = $this->selectAnio();
        $select_anio_actual = Carbon::now()->format('Y');
        $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
            ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('PROC_ASSISTANT_UNITS', 'PROC_SALES.sales_movementID', '=', 'PROC_ASSISTANT_UNITS.assistantUnit_movementID')
            ->join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
            ->where('PROC_ASSISTANT_UNITS.assistantUnit_movement', '=', 'Factura')
            ->where('PROC_SALES.sales_movement', '=', 'Factura')
            ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
            ->where('PROC_ASSISTANT_UNITS.assistantUnit_canceled', '=', '0')
            ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
            ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_SALES.sales_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->orderBy('PROC_SALES.updated_at', 'DESC')
            ->paginate(25);

        // dd($ventas);

        return view('page.Reportes.Ventas.indexVentasAcumArticuloCliente', compact('selectMonedas', 'articulos', 'clientes', 'categorias', 'grupos', 'parametro', 'ventas', 'select_categoria', 'select_grupo', 'select_familia', 'select_anio', 'select_anio_actual'));
    }

    public function reportesVentasAcumuladoClienteAction(Request $request)
    {
        $nameClienteUno = $request->nameClienteUno;
        $nameClienteDos = $request->nameClienteDos;
        $nameCategoria = $request->nameCategoria;
        $nameGrupo = $request->nameGrupo;
        $nameMoneda = $request->nameMoneda;
        $nameEjercicio = $request->nameEjercicio;
        $nameArticuloUno = $request->nameArticuloUno;
        $nameArticuloDos = $request->nameArticuloDos;
        $categoria = $request->categoria;
        $grupo = $request->grupo;
        $familia = $request->familia;

        switch ($request->input('action')) {
            case 'Búsqueda':
                $reportes_collection_filtro = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                    ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('PROC_ASSISTANT_UNITS', 'PROC_SALES.sales_movementID', '=', 'PROC_ASSISTANT_UNITS.assistantUnit_movementID')
                    ->join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
                    ->where('PROC_ASSISTANT_UNITS.assistantUnit_movement', '=', 'Factura')
                    ->where('PROC_SALES.sales_movement', '=', 'Factura')
                    ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                    ->where('PROC_ASSISTANT_UNITS.assistantUnit_canceled', '=', '0')
                    ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                    ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
                    ->whereSaleNameCustomer($nameClienteUno, $nameClienteDos)
                    ->whereCustomerCategory($nameCategoria)
                    ->WhereCustomerGroup($nameGrupo)
                    ->whereSalesMoney($nameMoneda)
                    ->whereAssistantUnitYear($nameEjercicio)
                    ->whereAssistantUnitAccount($nameArticuloUno, $nameArticuloDos)
                    ->whereArticleCategory($categoria)
                    ->whereArticleGroup($grupo)
                    ->whereArticleFamily($familia)
                    ->orderBy('PROC_SALES.updated_at', 'DESC')
                    ->get();

                /* Convertimos el objeto a un array. */
                $reportes_filtro_array = $reportes_collection_filtro->toArray();

                /* Vamos a redirigir a la ruta de la vista con todos los datos de la vista para que no se borren al dar clic al botón */

                return redirect()->route('vista.reportes.ventas-acumulado-cliente')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameClienteUno', $nameClienteUno)
                    ->with('nameClienteDos', $nameClienteDos)
                    ->with('nameCategoria', $nameCategoria)
                    ->with('nameGrupo', $nameGrupo)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('nameEjercicio', $nameEjercicio)
                    ->with('nameArticuloUno', $nameArticuloUno)
                    ->with('nameArticuloDos', $nameArticuloDos)
                    ->with('categoria', $categoria)
                    ->with('grupo', $grupo)
                    ->with('familia', $familia);
                break;

            case 'Exportar excel':

                /* Creamos una nueva instancia de la clase para pasarle los parametros y después descargar el excel. */
                $compra = new ReportesVentasAcumuladasExport($nameClienteUno, $nameClienteDos, $nameCategoria, $nameGrupo, $nameMoneda, $nameEjercicio, $nameArticuloUno, $nameArticuloDos, $categoria, $grupo, $familia);
                return Excel::download($compra, 'REPORTE DE VENTAS ACUMULADO ARTICULO-CLIENTE.xlsx');

                break;

            case 'Exportar PDF':

                $venta = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                    ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('PROC_ASSISTANT_UNITS', 'PROC_SALES.sales_movementID', '=', 'PROC_ASSISTANT_UNITS.assistantUnit_movementID')
                    ->join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
                    ->where('PROC_ASSISTANT_UNITS.assistantUnit_movement', '=', 'Factura')
                    ->where('PROC_SALES.sales_movement', '=', 'Factura')
                    ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                    ->where('PROC_ASSISTANT_UNITS.assistantUnit_canceled', '=', '0')
                    ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                    ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
                    ->where('PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', session('company')->companies_key)
                    ->where('PROC_ASSISTANT_UNITS.assistantUnit_branchKey', '=', session('sucursal')->branchOffices_key)
                    ->whereSaleNameCustomer($nameClienteUno, $nameClienteDos)
                    ->whereCustomerCategory($nameCategoria)
                    ->WhereCustomerGroup($nameGrupo)
                    ->whereSalesMoney($nameMoneda)
                    ->whereAssistantUnitYear($nameEjercicio)
                    ->whereAssistantUnitAccount($nameArticuloUno, $nameArticuloDos)
                    ->whereArticleCategory($categoria)
                    ->whereArticleGroup($grupo)
                    ->whereArticleFamily($familia)
                    ->get();

                if ($venta->isEmpty()) {
                    return redirect()->route('vista.reportes.ventas-acumulado-cliente')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                } else {

                    /*Obtenemos la imagen del storage y la convertimos a base64. */
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
                    // dd($venta);


                    $pdf = PDF::loadView('page.Reportes.Ventas.ventasAcumArticuloCliente-reporte', [
                        'logo' => $logoBase64,
                        'ventas' => $venta,


                    ]);

                    return $pdf->stream();
                }



                break;
        }
    }


    /**
     * Obtiene todas las monedas de la base de datos y las devuelve como un array
     * 
     * @return array:2 [▼
     *       "Todos" => "Todos"
     *       "MXN" => "Peso Mexicano"
     *      "USD" => "Dolar Americano"
     *     "EUR" => "Euro"
     *     ]
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
     * Esta función devuelve un array de todos los artículos en la base de datos, siendo el primer elemento 'Todos' (todos).
     * El resto de los elementos son el articles_key y el articles_descript.
     * 
     * @return un array de todos los artículos en la base de datos.
     */
    function selectArticulos()
    {
        $articulos = CAT_ARTICLES::where('articles_status', '=', 'Alta')->orderBy('articles_id', 'ASC')->get();

        $articulos_array = array();
        foreach ($articulos as $articulos) {
            $articulos_array[$articulos->Todos] = 'Todos';
            $articulos_array[$articulos->articles_key] = $articulos->articles_key . ' - ' . $articulos->articles_descript;
        }
        return $articulos_array;
    }

    /**
     * Esta función devuelve un array de todos los proveedores en la base de datos, siendo el primer elemento 'Todos' (todos).
     * seguido de los proveedores_key y el providers_name.
     * 
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
     * Esta función devuelve un array de todas las categorías de proveedores en la base de datos, siendo el primer elemento 'Todos' (todos).
     * seguido de los categoryProvider_name.
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
     * Esta función devuelve un array de todos los grupos de proveedores en la base de datos, siendo el primer elemento 'Todos' (todos).
     * seguido de los groupProvider_name.
     * 
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



    public function selectCategoriaArt()
    {
        $categorias = CAT_ARTICLES_CATEGORY::where('categoryArticle_status', 'Alta')->get();
        $categorias_array = array();
        foreach ($categorias as $categorias) {
            $categorias_array[$categorias->Todos] = 'Todos';
            $categorias_array[$categorias->categoryArticle_name] = $categorias->categoryArticle_name;
        }
        return $categorias_array;
    }

    public function selectGrupoArt()
    {
        $grupos = CAT_ARTICLES_GROUP::where('groupArticle_status', 'Alta')->get();
        $grupos_array = array();
        foreach ($grupos as $grupos) {
            $grupos_array[$grupos->Todos] = 'Todos';
            $grupos_array[$grupos->groupArticle_name] = $grupos->groupArticle_name;
        }
        return $grupos_array;
    }

    public function selectFamiliaArt()
    {
        $familias = CAT_ARTICLES_FAMILY::where('familyArticle_status', 'Alta')->get();
        $familias_array = array();
        foreach ($familias as $familias) {
            $familias_array[$familias->Todos] = 'Todos';
            $familias_array[$familias->familyArticle_name] = $familias->familyArticle_name;
        }
        return $familias_array;
    }

    public function selectAnio()
    {
        //para esta función tenemos que validar el año en el que se creó la empresa y validarlo con el año actual para mostrar los años que se han creado
        $anio = CAT_COMPANIES::where('companies_key', session('company')->companies_key)->first();
        $anio_array = array();
        //Ahora tomamos la fecha en la que se creó la empresa y la transformamos en año para poder validarla
        $anio_creacion = date('Y', strtotime($anio->created_at));
        //Ahora tomamos el año actual para poder validarla
        $anio_actual = date('Y');
        //Ahora validamos el año en el que se creó la empresa con el año actual para mostrarlo en el select
        if ($anio_creacion == $anio_actual) {
            $anio_array[$anio_creacion] = $anio_creacion;
        } else {
            for ($i = $anio_creacion; $i <= $anio_actual; $i++) {
                $anio_array[$i] = $i;
            }
        }
        return $anio_array;
    }
}
