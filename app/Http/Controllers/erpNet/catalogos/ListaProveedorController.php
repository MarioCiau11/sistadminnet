<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\CatProveedoresListExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_ARTICLES_LIST;
use App\Models\agrupadores\CAT_PROVIDER_LIST;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\modulos\helpers\PROC_ARTICLES_COST;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class ListaProveedorController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:Lista de Artículos']);
    }

    public function index()
    {
        $proveedor_collection = CAT_PROVIDER_LIST::where('listProvider_status', '=', 'Alta')->orderBy('listProvider_id')->get();
        // dd($proveedor_collection);
        return view('page.catalogos.proveedores.lista.index', compact('proveedor_collection'));
    }

    public function create()
    {

        $articulos = CAT_ARTICLES::where('articles_status', '=', 'Alta')->whereNotIn('articles_type', ['Servicio', 'Kit'])->orderBy('articles_key')->get();
        // DD($articulos);
        return view('page.catalogos.proveedores.lista.create', compact('articulos'));
    }

    public function store(Request $request)
    {
        //   dd($request->all());
        $listProveedor = new CAT_PROVIDER_LIST();

        $listProveedor->listProvider_name = $request->nameList;
        $listProveedor->listProvider_status = $request->statusDG;

        $listCreate = $listProveedor->save();

        if (isset($request->articulos)) {
            $articulos = $request->articulos;
            $articulos = json_decode($articulos, true);

            foreach ($articulos as $key => $value) {
                $lista = new CAT_ARTICLES_LIST();
                $lista->articlesList_article = $value['clave'];
                $lista->articlesList_listID = $listProveedor->listProvider_id;
                $lista->articlesList_nameArticle = $value['nombre'];
                $lista->articlesList_lastCost = str_replace(['$', ','], '', $value['costo']);
                $lista->articlesList_averageCost = str_replace(['$', ','], '', $value['promedio']);
                $lista->articlesList_penultimateCost = str_replace(['$', ','], '', $value['costo']);

                $lista->save();
            }
            // $listProveedor->articles()->attach($articulos);
            // dd($articulos);
        }

        if ($listCreate) {
            $status =  true;
            $message = 'Se ha creado la lista de proveedor correctamente';
        } else {
            $status = false;
            $message = 'No se ha podido crear la lista de proveedor';
        }

        return redirect()->route('listaIndex')->with('status', $status)->with('message', $message);
    }

    public function show($id)
    {

        try {

            $id = Crypt::decrypt($id);
            $provider = CAT_PROVIDER_LIST::find($id);

            $listaArticulos = CAT_ARTICLES_LIST::where('articlesList_listID', '=', $id)->get();

            return view('page.catalogos.proveedores.lista.show', compact('provider', 'listaArticulos'));
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->route('listaIndex')->with('status', false)->with('message', 'Error al mostrar el proveedor');
        }
    }

    public function edit($id)
    {
        try {

            $id = Crypt::decrypt($id);
            $provider = CAT_PROVIDER_LIST::find($id);

            $articulos = CAT_ARTICLES::where('articles_status', '=', 'Alta')->where('articles_type', '!=', 'Servicio')->orderBy('articles_key')->get();

            $listaArticulos = CAT_ARTICLES_LIST::where('articlesList_listID', '=', $id)->get();



            return view('page.catalogos.proveedores.lista.edit', compact('provider', 'articulos', 'listaArticulos'));
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->route('listaIndex')->with('status', false)->with('message', 'Error al mostrar el proveedor');
        }
    }

    public function update(Request $request, $id)
    {

        try {
            // dd($request->all());
            $id = Crypt::decrypt($id);
            $provider = CAT_PROVIDER_LIST::find($id);

            $provider->listprovider_name = $request->nameList;
            $provider->listprovider_status = $request->statusDG;

            $providerUpdate = $provider->save();

            if (isset($request->articulos)) {
                //borrar los articulos de la lista
                $listaArticulos = CAT_ARTICLES_LIST::where('articlesList_listID', '=', $provider->listProvider_id)->get();
                foreach ($listaArticulos as $key => $value) {
                    $value->delete();
                }
                $articulos = $request->articulos;
                $articulos = json_decode($articulos, true);

                foreach ($articulos as $key => $value) {
                    $lista = new CAT_ARTICLES_LIST();
                    $lista->articlesList_article = $value['clave'];
                    $lista->articlesList_listID = $provider->listProvider_id;
                    $lista->articlesList_nameArticle = $value['nombre'];
                    $lista->articlesList_penultimateCost = $lista->articlesList_lastCost;
                    $lista->articlesList_lastCost = str_replace(['$', ','], '', $value['costo']);
                    $lista->articlesList_averageCost = str_replace(['$', ','], '', $value['promedio']);

                    $lista->save();
                }
                // $listProveedor->articles()->attach($articulos);
                // dd($articulos);
            }

            if ($providerUpdate) {
                $status =  true;
                $message = 'Se ha actualizado la lista de proveedor correctamente';
            } else {
                $status = false;
                $message = 'No se ha podido actualizar la lista de proveedor';
            }

            return redirect()->route('listaIndex')->with('status', $status)->with('message', $message);
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->route('listaIndex')->with('status', false)->with('message', 'Error al mostrar el proveedor');
        }
    }

    public function destroy($id)
    {

        try {

            $id = Crypt::decrypt($id);
            $provider = CAT_PROVIDER_LIST::find($id);

            $provider->listprovider_status = 'Baja';

            $providerUpdate = $provider->save();

            if ($providerUpdate) {
                $status =  true;
                $message = 'Se ha eliminado la lista de proveedor correctamente';
            } else {
                $status = false;
                $message = 'No se ha podido eliminar la lista de proveedor';
            }

            return redirect()->route('listaIndex')->with('status', $status)->with('message', $message);
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->route('listaIndex')->with('status', false)->with('message', 'Error al mostrar el proveedor');
        }
    }

    public function getLista()
    {
        $providerLast = CAT_PROVIDER_LIST::count();
        $getId = $providerLast + 1;
        return response()->json(['providers_key' => $getId]);
    }

    public function listaAction(Request $request)
    {

        // dd($request->all());
        $keyProvider = $request->keyProvider;
        $nameProvider = $request->nameProvider;
        $status = $request->status;

        switch ($request->input('action')) {
            case 'Búsqueda':

                $provider_collection = CAT_PROVIDER_LIST::whereListproviderId($keyProvider)->whereListproviderName($nameProvider)->whereListproviderStatus($status)->get();

                $provider_filtro_array = $provider_collection->toArray();

                return redirect()->route('listaIndex')->with('provider_filtro_array', $provider_filtro_array)->with('keyProvider', $keyProvider)->with('nameProvider', $nameProvider)->with('status', $status);


                break;

            case 'Exportar excel':
                $proveedor = new CatProveedoresListExport($keyProvider, $nameProvider, $status);
                return Excel::download($proveedor, 'catalogo_proveedoresList.xlsx');
                break;

            default:

                break;
        }
    }

    public function getCosto(Request $request)
    {
        $costo = PROC_ARTICLES_COST::where('articlesCost_article', '=', $request->articulo)->where('articlesCost_companieKey', '=', session('company')->companies_key)->where('articlesCost_branchKey', '=', session('sucursal')->branchOffices_key)->first();

        return response()->json($costo);
    }
}
