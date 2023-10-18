<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\CatProveedoresExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_PROVIDER_CATEGORY;
use App\Models\agrupadores\CAT_PROVIDER_GROUP;
use App\Models\agrupadores\CAT_PROVIDER_LIST;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\catalogos\CAT_PROVIDERS_FILES;
use App\Models\catalogos\CONF_CREDIT_CONDITIONS;
use App\Models\catalogos\CONF_FORMS_OF_PAYMENT;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\catalogosSAT\CAT_SAT_ESTADO;
use App\Models\CatalogosSAT\CAT_SAT_LOCALIDAD;
use App\Models\catalogosSAT\CAT_SAT_MUNICIPIO;
use App\Models\catalogosSAT\CAT_SAT_PAIS;
use App\Models\CatalogosSAT\CAT_SAT_REGIMENFISCAL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

class ProveedorController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:Proveedores']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categoria_array = $this->selectCategoria();
        $grupo_array = $this->selectGrupo();
        $proveedor_collection = CAT_PROVIDERS::where('providers_status', '=', 'Alta')->orderBy('providers_key')->get();
        return view('page.catalogos.proveedores.index', compact('categoria_array', 'grupo_array', 'proveedor_collection'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $create_pais_array = $this->selectPais();
        $create_estado_array = $this->selectEstado();
        $create_municipio_array = $this->selectMunicipio();
        $create_localidad_array = $this->selectLocalidad();
        $create_regimen_array = $this->selectRegimenFiscal();
        $categoria_array = $this->selectCategoria();
        $grupo_array = $this->selectGrupo();
        $condicion_array = $this->selectCondiciones();
        $forma_pago_array = $this->selectFormaPago();
        $monedas = $this->selectMonedas();
        $listaProveedor = $this->selectListaProveedor();

        return view('page.catalogos.proveedores.create', compact('create_pais_array', 'create_estado_array', 'create_municipio_array', 'create_regimen_array', 'create_localidad_array', 'categoria_array', 'grupo_array', 'condicion_array', 'forma_pago_array', 'monedas', 'listaProveedor'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());

        // DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $provider_request = $request->except('_token');

        // dd($provider_request);

        $provider = new CAT_PROVIDERS();


        $provider->providers_name = $provider_request['nameProveedor'];
        $provider->providers_nameShort = $provider_request['nameShortProveedor'];
        $provider->providers_RFC = $provider_request['rfcProveedor'];
        $provider->providers_CURP = $provider_request['curpProveedor'];
        $provider->providers_type = $provider_request['type'];
        $provider->providers_status = $provider_request['statusDG'];
        $provider->providers_address = $provider_request['direccionProveedor'];
        $provider->providers_roads = $provider_request['vialidades'];
        $provider->providers_outdoorNumber = $provider_request['numberProveedor'];
        $provider->providers_interiorNumber = $provider_request['numberProveedor2'];
        $provider->providers_colonyFractionation = $provider_request['coloniaBusqueda'];
        $provider->providers_townMunicipality = $provider_request['municipio'];
        $provider->providers_state = $provider_request['estado'];
        $provider->providers_country = $provider_request['pais'];
        $provider->providers_cp = $provider_request['cpBusqueda'];
        $provider->providers_observations = $provider_request['observaciones'];
        $provider->providers_phone1 = $provider_request['phone1'];
        $provider->providers_phone2 = $provider_request['phone2'];
        $provider->providers_cellphone = $provider_request['cellphone'];
        $provider->providers_contact1 = $provider_request['contacto1'];
        $provider->providers_mail1 = $provider_request['correo1'];
        $provider->providers_contact2 = $provider_request['contacto2'];
        $provider->providers_mail2 = $provider_request['correo2'];
        $provider->providers_group = $provider_request['grupo'];
        $provider->providers_category = $provider_request['categoria'];
        $provider->providers_creditCondition = $provider_request['condicion'];
        $provider->providers_formPayment = $provider_request['formaPago'];
        $provider->providers_money = $provider_request['nameMoneda'];
        $provider->providers_taxRegime = $provider_request['regimenFiscal'];
        $provider->providers_bankAccount = $provider_request['cuentaBancaria'];
        $provider->providers_priceList = $provider_request['listaPrecios'];
        // $provider->providers_nameFile = $provider_request['nombreDocumento'];

        // $provider_request['nombreDocumento'] != null ? $provider->providers_nameFile = $provider_request['nombreDocumento'] : $provider->providers_nameFile = null;

        $nameDocs = $provider_request['nombreDocumento'];
        $fileDocs = $request->file('field_name');

        // dd($nameDocs);

        if ($nameDocs[0] != null) {
            foreach ($nameDocs as $key => $value) {
                $nameDocs[$key] == null ? $nameDocs[$key] = 'Doc-' . time() : $nameDocs[$key] = $nameDocs[$key];
            }
        }


        // dd($fileDocs);
        try {
            $providerCreate = $provider->save();
            $provider_data = $provider::latest('providers_key')->first();
            $lastProvider = $provider_data->providers_key;

            //Obtenemos los parametros generales de la empresa
            $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

            if ($parametro === null || $parametro->generalParameters_filesProviders === Null || $parametro->generalParameters_filesProviders === '') {
                $empresaRuta = session('company')->companies_routeFiles . 'Proveedores';
            } else {
                $empresaRuta = session('company')->companies_routeFiles . $parametro->generalParameters_filesProviders;
            }




            if ($nameDocs[0] != null) {
                foreach ($nameDocs as $key => $value) {
                    $provider_doc = new CAT_PROVIDERS_FILES();
                    $provider_doc->providersFiles_keyProvider = $lastProvider;
                    $provider_doc->providersFiles_path = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $lastProvider . '-' . $nameDocs[$key]);
                    $provider_doc->providersFiles_file = $fileDocs[$key]->getClientOriginalName();
                    $rutaFinal = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $lastProvider . '/' . $fileDocs[$key]->getClientOriginalName());
                    Storage::disk('empresas')->put($rutaFinal, File::get($fileDocs[$key]));
                    $provider_doc->save();
                }
            }

            // dd($provider_data);

            if ($providerCreate) {
                $message = 'Proveedor creado correctamente';
                $status = true;
            } else {
                $message = 'Error al crear el proveedor';
                $status = false;
            }

            //para evitar el salto del increment
            $increment = $lastProvider != null ? $lastProvider : 0;
            DB::statement('DBCC CHECKIDENT (CAT_PROVIDERS, RESEED, ' . $increment . ')');
        } catch (\Throwable $th) {
            dd($th);
            $message = 'Error al crear el proveedor';
            $status = false;
        }

        return redirect()->route('catalogo.proveedor.index')->with('status', $status)->with('message', $message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        try {

            $id = Crypt::decrypt($id);
            $provider = CAT_PROVIDERS::find($id);
            $show_pais_array = $this->selectPais();
            $show_estado_array = $this->selectEstado();
            $show_municipio_array = $this->selectMunicipio();
            $show_localidad_array = $this->selectLocalidad();
            $show_regimen_array = $this->selectRegimenFiscal();
            $categoria_array = $this->selectCategoria();
            $grupo_array = $this->selectGrupo();
            $condicion_array = $this->selectCondiciones();
            $forma_pago_array = $this->selectFormaPago();
            $listaProveedor = $this->selectListaProveedor();
            $monedas = $this->selectMonedas();

            $documentosProveedor = CAT_PROVIDERS_FILES::where('providersFiles_keyProvider', $id)->get();

            return view('page.catalogos.proveedores.show', compact('show_pais_array', 'show_estado_array', 'show_municipio_array', 'show_regimen_array', 'show_localidad_array', 'provider', 'categoria_array', 'grupo_array', 'condicion_array', 'forma_pago_array', 'monedas', 'documentosProveedor', 'listaProveedor'));
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->route('catalogo.proveedor.index')->with('status', false)->with('message', 'Error al mostrar el proveedor');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {

            $id = Crypt::decrypt($id);
            $edit_provider = CAT_PROVIDERS::find($id);
            $edit_pais_array = $this->selectPais();
            $edit_estado_array = $this->selectEstado();
            $edit_municipio_array = $this->selectMunicipio();
            $edit_localidad_array = $this->selectLocalidad();
            $edit_regimen_array = $this->selectRegimenFiscal();
            $categoria_array = $this->selectCategoria();
            $grupo_array = $this->selectGrupo();
            $condicion_array = $this->selectCondiciones();
            $forma_pago_array = $this->selectFormaPago();
            $listaProveedor = $this->selectListaProveedor();
            $monedas = $this->selectMonedas();

            $documentosProveedor = CAT_PROVIDERS_FILES::where('providersFiles_keyProvider', $id)->get();

            return view('page.catalogos.proveedores.edit', compact('edit_pais_array', 'edit_estado_array', 'edit_municipio_array', 'edit_regimen_array', 'edit_localidad_array', 'edit_provider',  'categoria_array', 'grupo_array', 'condicion_array', 'forma_pago_array', 'monedas', 'documentosProveedor', 'listaProveedor'));
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->route('catalogo.proveedor.index')->with('status', false)->with('message', 'Error al editar el proveedor');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        // dd($request->all());
        try {
            $id = Crypt::decrypt($id);
            $provider_request = $request->except('_token');
            $provider_edit = CAT_PROVIDERS::find($id);
            // dd($provider_edit, $provider_request);

            $provider_edit->providers_name = $provider_request['nameProveedor'];
            $provider_edit->providers_nameShort = $provider_request['nameShortProveedor'];
            $provider_edit->providers_RFC = $provider_request['rfcProveedor'];
            $provider_edit->providers_CURP = $provider_request['curpProveedor'];
            $provider_edit->providers_type = $provider_request['type'];
            $provider_edit->providers_status = $provider_request['statusDG'];
            $provider_edit->providers_address = $provider_request['direccionProveedor'];
            $provider_edit->providers_roads = $provider_request['vialidades'];
            $provider_edit->providers_outdoorNumber = $provider_request['outdoorNumber'];
            $provider_edit->providers_interiorNumber = $provider_request['interiorNumber'];
            $provider_edit->providers_colonyFractionation = $provider_request['coloniaBusqueda'];
            $provider_edit->providers_townMunicipality = $provider_request['municipio'];
            $provider_edit->providers_state = $provider_request['estado'];
            $provider_edit->providers_country = $provider_request['pais'];
            $provider_edit->providers_cp = $provider_request['cpBusqueda'];
            $provider_edit->providers_observations = $provider_request['observaciones'];
            $provider_edit->providers_phone1 = $provider_request['phone1'];
            $provider_edit->providers_phone2 = $provider_request['phone2'];
            $provider_edit->providers_cellphone = $provider_request['cellphone'];
            $provider_edit->providers_contact1 = $provider_request['contacto1'];
            $provider_edit->providers_mail1 = $provider_request['correo1'];
            $provider_edit->providers_contact2 = $provider_request['contacto2'];
            $provider_edit->providers_mail2 = $provider_request['correo2'];
            $provider_edit->providers_group = $provider_request['grupo'];
            $provider_edit->providers_category = $provider_request['categoria'];
            $provider_edit->providers_creditCondition = $provider_request['condicion'];
            $provider_edit->providers_formPayment = $provider_request['formaPago'];
            $provider_edit->providers_money = $provider_request['nameMoneda'];
            $provider_edit->providers_taxRegime = $provider_request['regimenFiscal'];
            $provider_edit->providers_bankAccount = $provider_request['cuentaBancaria'];
            $provider_edit->providers_priceList = $provider_request['listaPrecios'];
            $nameDocs = isset($provider_request['nombreDocumento']) ? $provider_request['nombreDocumento'] : null;
            $fileDocs = isset($provider_request['field_name']) ? $provider_request['field_name'] : null;
            //Obtenemos los parametros generales de la empresa
            $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();


            if ($parametro === null || $parametro->generalParameters_filesProviders === Null || $parametro->generalParameters_filesProviders === '') {
                $empresaRuta = session('company')->companies_routeFiles . 'Proveedores';
            } else {
                $empresaRuta = session('company')->companies_routeFiles . $parametro->generalParameters_filesProviders;
            }




            if (!isset($provider_request['docsEdit'])) {

                if ($nameDocs != null) {
                    foreach ($nameDocs as $key => $value) {
                        $provider_doc = new CAT_PROVIDERS_FILES();
                        $provider_doc->providersFiles_keyProvider = $provider_edit->providers_key;
                        $provider_doc->providersFiles_path = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $provider_edit->providers_key . '-' . $nameDocs[$key]);
                        $provider_doc->providersFiles_file = $fileDocs[$key]->getClientOriginalName();
                        $rutaFinal  = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $provider_edit->providers_key . '/' . $fileDocs[$key]->getClientOriginalName());
                        Storage::disk('empresas')->put($rutaFinal, File::get($fileDocs[$key]));
                        $provider_doc->save();
                    }
                }
            } else {
                foreach ($provider_request['docsEdit'] as $key => $id) {

                    $providerEdit_doc = CAT_PROVIDERS_FILES::find($id);

                    $fieldName = isset($provider_request[$id . '-nombre']) ? $provider_request[$id . '-nombre'] : null;

                    $fieldFile = isset($provider_request[$id . '-file']) ? $provider_request[$id . '-file'] : null;


                    if ($fieldName != null) {
                        $providerEdit_doc->providersFiles_path = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $providerEdit_doc->providersFiles_keyProvider . '-' . $fieldName);
                    }

                    if ($fieldFile != null) {
                        $providerEdit_doc->providersFiles_file = $fieldFile->getClientOriginalName();
                        $rutaFinal  = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $provider_edit->providers_key . '/' . $fieldFile->getClientOriginalName());
                        Storage::disk('empresas')->put($rutaFinal, File::get($fieldFile));
                    }

                    $providerEdit_doc->update();
                }
                //Guardamos los documentos editados del proveedor
            }

            try {
                $isUpdate = $provider_edit->update();
                if ($isUpdate) {
                    $message = 'Proveedor editado correctamente';
                    $status = true;
                } else {
                    $message = 'Error al editar el proveedor';
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas y muéstrele este mensaje: " . $th;
                return redirect()->route('catalogo.proveedor.index')->with('message', $message)->with('status', false);
            }

            return redirect()->route('catalogo.proveedor.index')->with('status', $status)->with('message', $message);
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->route('catalogo.proveedor.index')->with('status', false)->with('message', 'Error al editar el proveedor');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $provider_delete = CAT_PROVIDERS::find($id);
            $provider_delete->providers_status = 'Baja';

            $isDelete = $provider_delete->update();
            if ($isDelete) {
                $message = 'Proveedor eliminado correctamente';
                $status = true;
            } else {
                $message = 'Error al eliminar el proveedor';
                $status = false;
            }

            return redirect()->route('catalogo.proveedor.index')->with('status', $status)->with('message', $message);
        } catch (\Throwable $th) {
            $message = "Por favor, vaya con el administrador de sistemas, no se puede eliminar el proveedor: ";
            return redirect()->route('catalogo.proveedor.index')->with('message', $message)->with('status', false);
        }
    }

    public function providerAction(Request $request)
    {

        // dd($request->all());
        $keyProvider = $request->keyProvider;
        $nameProvider = $request->nameProvider;
        $category = $request->categoria;
        $group = $request->grupo;
        $status = $request->status;

        switch ($request->input('action')) {
            case 'Búsqueda':

                $provider_collection = CAT_PROVIDERS::whereProvidersKey($keyProvider)->whereProvidersName($nameProvider)->whereProvidersCategory($category)->whereProvidersGroup($group)->whereProvidersStatus($status)->get();

                $provider_filtro_array = $provider_collection->toArray();

                return redirect()->route('catalogo.proveedor.index')->with('provider_filtro_array', $provider_filtro_array)->with('keyProvider', $keyProvider)->with('nameProvider', $nameProvider)->with('categoria', $category)->with('grupo', $group)->with('status', $status);


                break;

            case 'Exportar excel':
                $proveedor = new CatProveedoresExport($keyProvider, $nameProvider, $category, $group, $status);
                return Excel::download($proveedor, 'catalogo_proveedores.xlsx');
                break;

            default:

                break;
        }
    }

    public function selectPais()
    {
        $country_array = [];
        $country_key_sat_collection = CAT_SAT_PAIS::all();
        $country_key_sat_array = $country_key_sat_collection->toArray();

        foreach ($country_key_sat_array as $key => $value) {
            $country_array[$value['c_Pais']] = $value['descripcion'] . ' - ' . $value['c_Pais'];
        }
        return $country_array;
    }

    public function selectMunicipio()
    {
        $municipio_array = [];
        $municipio_key_sat_collection = CAT_SAT_MUNICIPIO::all();
        $municipio_key_sat_array = $municipio_key_sat_collection->toArray();

        foreach ($municipio_key_sat_array as $key => $value) {
            $municipio_array[$value['c_Municipio'] . ' - ' . $value['c_Estado']] = $value['descripcion'] . ' - ' . $value['c_Municipio'];
        }
        return $municipio_array;
    }

    public function selectLocalidad()
    {
        $localidad_array = [];
        $localidad_key_sat_collection = CAT_SAT_LOCALIDAD::all();
        $localidad_key_sat_array = $localidad_key_sat_collection->toArray();

        foreach ($localidad_key_sat_array as $key => $value) {
            $localidad_array[$value['c_Localidad'] . '' . $value['c_Estado']] = $value['descripcion'] . ' - ' . $value['c_Localidad'];
        }
        return $localidad_array;
    }

    public function selectEstado()
    {
        $state_array = [];
        $state_key_sat_collection = CAT_SAT_ESTADO::all();
        $state_key_sat_array = $state_key_sat_collection->toArray();

        foreach ($state_key_sat_array as $key => $value) {
            $state_array[$value['c_Estado']] = $value['nombreEstado'] . ' - ' . $value['c_Estado'];
        }
        return $state_array;
    }

    public function selectRegimenFiscal()
    {
        $regimen_array = [];
        $regimen_key_sat_collection = CAT_SAT_REGIMENFISCAL::all();
        $regimen_key_sat_array = $regimen_key_sat_collection->toArray();

        foreach ($regimen_key_sat_array as $key => $value) {
            $regimen_array[$value['c_RegimenFiscal']] = $value['descripcion'] . ' - ' . $value['c_RegimenFiscal'];
        }
        return $regimen_array;
    }

    public function selectCategoria()
    {
        $categoria_array = [];
        $categoria_key_sat_collection = CAT_PROVIDER_CATEGORY::where('categoryProvider_status', 'Alta')->get();
        $categoria_key_sat_array = $categoria_key_sat_collection->toArray();

        foreach ($categoria_key_sat_array as $key => $value) {
            $categoria_array[$value['categoryProvider_name']] = $value['categoryProvider_name'];
        }
        return $categoria_array;
    }

    public function selectGrupo()
    {
        $grupo_array = [];
        $grupo_key_sat_collection = CAT_PROVIDER_GROUP::where('groupProvider_status', 'Alta')->get();
        $grupo_key_sat_array = $grupo_key_sat_collection->toArray();

        foreach ($grupo_key_sat_array as $key => $value) {
            $grupo_array[$value['groupProvider_name']] = $value['groupProvider_name'];
        }
        return $grupo_array;
    }

    public function selectCondiciones()
    {
        $condiciones_array = [];
        $condiciones_key_sat_collection = CONF_CREDIT_CONDITIONS::where('creditConditions_status', '=', 'Alta')->get();
        $condiciones_key_sat_array = $condiciones_key_sat_collection->toArray();

        foreach ($condiciones_key_sat_array as $key => $value) {
            $condiciones_array[$value['creditConditions_id']] = $value['creditConditions_name'];
        }
        return $condiciones_array;
    }

    public function selectListaProveedor()
    {
        $condiciones_array = [];
        $condiciones_key_sat_collection = CAT_PROVIDER_LIST::where('listProvider_status', '=', 'Alta')->get();
        $condiciones_key_sat_array = $condiciones_key_sat_collection->toArray();

        foreach ($condiciones_key_sat_array as $key => $value) {
            $condiciones_array[$value['listProvider_id']] = $value['listProvider_name'];
        }
        return $condiciones_array;
    }

    public function selectFormaPago()
    {
        $pagos_array = [];
        $pagos_key_sat_collection = CONF_FORMS_OF_PAYMENT::where('formsPayment_status', '=', 'Alta')->get();
        $pagos_key_sat_array = $pagos_key_sat_collection->toArray();

        foreach ($pagos_key_sat_array as $key => $value) {
            $pagos_array[$value['formsPayment_key']] = $value['formsPayment_name'];
        }
        return $pagos_array;
    }

    public function selectMonedas()
    {
        $monedas = [];
        $monedas_collection = CONF_MONEY::where('money_status', '=', 'Alta')->get();
        $monedas_array = $monedas_collection->toArray();

        foreach ($monedas_array as $key => $value) {
            $monedas[trim($value['money_key'])] = $value['money_key'];
        };
        return $monedas;
    }

    public function getProvider()
    {
        $providerLast = CAT_PROVIDERS::count();
        $getId = $providerLast + 1;
        return response()->json(['providers_key' => $getId]);
    }
}
