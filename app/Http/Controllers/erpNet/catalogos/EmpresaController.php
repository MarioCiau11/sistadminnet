<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\CatEmpresasExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\catalogosSAT\CAT_SAT_ESTADO;
use App\Models\catalogosSAT\CAT_SAT_MUNICIPIO;
use App\Models\catalogosSAT\CAT_SAT_PAIS;
use App\Models\CatalogosSAT\CAT_SAT_REGIMENFISCAL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class EmpresaController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:Empresa']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $empresa_collection = CAT_COMPANIES::where('companies_status', '=', 'Alta')->orderBy('companies_id', 'desc')->get();
        // dd($empresa_collection);
        return view('page.catalogos.empresa.index', compact('empresa_collection'));
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
        $create_ciudad_array = $this->selectCiudad();
        $create_regimen_array = $this->selectRegimenFiscal();
        $create_proveedor_array = $this->selectProveedores();

        // $create_colonia_array = $this->selectColonia();
        return view('page.catalogos.empresa.create', compact('create_pais_array', 'create_estado_array', 'create_ciudad_array', 'create_regimen_array', 'create_proveedor_array'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cat_empresas_request = $request->except('_token'); //

        // dd($cat_empresas_request);
        $directorios = Storage::allDirectories(('empresas'));
        // dd($directorios);
        $directorio = 'empresas/' . $cat_empresas_request['routeBov'];
        $directorio_sin = trim($directorio, '@/.');
        $isKeyEmp = CAT_COMPANIES::where('companies_key', $cat_empresas_request['keyCompany'])->first();
        $isDirectory = in_array($directorio_sin, $directorios);

        if ($isKeyEmp) {
            $message = "La clave: " . $cat_empresas_request['keyCompany'] . " ya existe en la base de datos";
            $status = false;
        } else {

            if ($isDirectory) {
                $message = "El directorio: " . $directorio_sin . " ya existe en la base de datos la empresa no se puede crear, favor de verificar";
                $status = false;
            } else {
                $cat_empresas = new CAT_COMPANIES(); //creamos un objeto de la clase CAT_EMPRESAS
                $cat_empresas->companies_key = $cat_empresas_request['keyCompany'];
                $cat_empresas->companies_name = $cat_empresas_request['company'];
                $cat_empresas->companies_nameShort = $cat_empresas_request['shortCompany'];
                $cat_empresas->companies_status = $cat_empresas_request['statusDG'];
                $cat_empresas->companies_descript = $cat_empresas_request['description'];
                $cat_empresas->companies_website = $cat_empresas_request['webSite'];
                $cat_empresas->companies_addres = $cat_empresas_request['address'];
                $cat_empresas->companies_suburb = $cat_empresas_request['coloniaBusqueda'];
                $cat_empresas->companies_cp = $cat_empresas_request['cpBusqueda'];
                $cat_empresas->companies_city = $cat_empresas_request['city'];
                $cat_empresas->companies_state = $cat_empresas_request['state'];
                $cat_empresas->companies_country = $cat_empresas_request['country'];
                $cat_empresas->companies_phone1 = $cat_empresas_request['phone1'];
                $cat_empresas->companies_phone2 = $cat_empresas_request['phone2'];
                $cat_empresas->companies_phone3 = $cat_empresas_request['phone3'];
                $cat_empresas->companies_mail = $cat_empresas_request['mail'];
                $cat_empresas->companies_rfc = $cat_empresas_request['rfc'];
                $cat_empresas->companies_taxRegime = $cat_empresas_request['taxRegime'];
                $cat_empresas->companies_employerRegistration = $cat_empresas_request['employerRegistration'];
                $cat_empresas->companies_representative = $cat_empresas_request['manager'];
                $cat_empresas->companies_routeFiles = $cat_empresas_request['routeBov'];
                $cat_empresas->companies_passwordKey = Crypt::encrypt($cat_empresas_request['passwordKey']);
                $cat_empresas->companies_referenceProvider = $cat_empresas_request['proveedor'];
                $cat_empresas->companies_calculateTaxes = isset($cat_empresas_request['calcularImpuesto']) ? 1 : 0;
                $cat_empresas->companies_stamped = $request->input('stamped', 0);
                // dd($cat_empresas);


                //obtenemos ruta para los archivos
                $rutaPrincipal = $cat_empresas_request['routeBov'];

                //obtenemos nombre de los archivos
                $imagePath = $request->file('logoEmpresa');
                $certPath = $request->file('certificadoCer');
                $keyPath = $request->file('certificadoKey');

                // dd($imagePath);
                //verificamos si existe el logo o (el certificado o la llave) para crear el directorio
                if (isset($imagePath) or (isset($certPath) or isset($keyPath))) {

                    //si solo existe el logo creamos el directorio y le pasamos el archivo
                    if ($imagePath) {
                        $imageName = $rutaPrincipal . $imagePath->getClientOriginalName();
                        Storage::disk('empresas')->put($imageName, File::get($imagePath));
                        $cat_empresas->companies_logo = $imageName;
                    } else {
                        $cat_empresas->companies_logo = null;
                    }
                    //si existe el certificado o la llave creamos el directorio y le pasamos los archivos
                    if ($certPath && $keyPath) {
                        $certName = $rutaPrincipal . 'CFDI/' . $certPath->getClientOriginalName();
                        $keyName = $rutaPrincipal . 'CFDI/' . $keyPath->getClientOriginalName();
                        Storage::disk('empresas')->put($certName, File::get($certPath));
                        Storage::disk('empresas')->put($keyName, File::get($keyPath));
                        $cat_empresas->companies_routeKey = $keyName;
                        $cat_empresas->companies_routeCertificate = $certName;
                    } else {
                        if ($certPath) {
                            $certName = $rutaPrincipal . 'CFDI/' . $certPath->getClientOriginalName();
                            Storage::disk('empresas')->put($certName, File::get($certPath));
                            $cat_empresas->companies_routeCertificate = $certName;
                        } else {
                            $cat_empresas->companies_routeCertificate = null;
                        }
                        if ($keyPath) {
                            $keyName = $rutaPrincipal . 'CFDI/' . $keyPath->getClientOriginalName();
                            Storage::disk('empresas')->put($keyName, File::get($keyPath));
                            $cat_empresas->companies_routeKey = $keyName;
                        } else {
                            $cat_empresas->companies_routeKey = null;
                        }
                    }
                } else {
                    Storage::disk('empresas')->makeDirectory($rutaPrincipal . 'CFDI/');

                    $cat_empresas->companies_logo = null;
                    $cat_empresas->companies_routeKey =  null;
                    $cat_empresas->companies_routeCertificate = null;
                }

                try {
                    $empresaCreate = $cat_empresas->save();

                    if ($empresaCreate) {
                        $message = "La empresa: " . $cat_empresas_request['company'] . " se ha creado correctamente";
                        $status = true;
                    } else {
                        $message = "La empresa: " . $cat_empresas_request['company'] . " no se ha creado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "La empresa: " . $cat_empresas_request['company'] . " no se ha guardado correctamente2";
                    $status = false;
                }
            }
        }


        return redirect()->route('catalogo.empresa.index')->with('status', $status)->with('message', $message);
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
            $cat_empresas = CAT_COMPANIES::where('companies_id', $id)->first();

            $show_pais_array = $this->selectPais();
            $show_estado_array = $this->selectEstado();
            $show_ciudad_array = $this->selectCiudad();
            $show_regimen_array = $this->selectRegimenFiscal();
            // $show_colonia_array = $this->selectColonia();
            return view('page.catalogos.empresa.show', compact('show_pais_array', 'show_estado_array', 'show_ciudad_array', 'show_regimen_array', 'cat_empresas'));
        } catch (\Exception $e) {
            return redirect()->route('catalogo.empresa.index')->with('status', false)->with('message', 'La empresa no se ha encontrado');
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
            $empresas_edit = CAT_COMPANIES::where('companies_id', $id)->first();
            $edit_pais_array = $this->selectPais();
            $edit_estado_array = $this->selectEstado();
            $edit_ciudad_array = $this->selectCiudad();
            $edit_regimen_array = $this->selectRegimenFiscal();
            $create_proveedor_array = $this->selectProveedores();
            return view('page.catalogos.empresa.edit', compact('edit_pais_array', 'edit_estado_array', 'edit_ciudad_array', 'edit_regimen_array', 'empresas_edit', 'create_proveedor_array'));
        } catch (\Exception $e) {
            return redirect()->route('catalogo.empresa.index')->with('status', false)->with('message', 'La empresa no se ha encontrado');
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
            // dd($id);
            $edit_empresa_request = $request->except('_token');
            $edit_empresas = CAT_COMPANIES::where('companies_id', $id)->first();
            // dd($edit_empresas);
            // dd($edit_empresa_request);

            $edit_empresas->companies_name = $edit_empresa_request['company'];
            $edit_empresas->companies_nameShort = $edit_empresa_request['shortCompany'];
            $edit_empresas->companies_descript = $edit_empresa_request['description'];
            $edit_empresas->companies_website = $edit_empresa_request['webSite'];
            $edit_empresas->companies_status = $edit_empresa_request['statusDG'];
            $edit_empresas->companies_addres = $edit_empresa_request['address'];
            $edit_empresas->companies_suburb = $edit_empresa_request['coloniaBusqueda'];
            $edit_empresas->companies_cp = $edit_empresa_request['cpBusqueda'];
            $edit_empresas->companies_city = $edit_empresa_request['city'];
            $edit_empresas->companies_country = $edit_empresa_request['country'];
            $edit_empresas->companies_state = $edit_empresa_request['state'];

            $edit_empresas->companies_phone1 = $edit_empresa_request['phone1'];
            $edit_empresas->companies_phone2 = $edit_empresa_request['phone2'];
            $edit_empresas->companies_phone3 = $edit_empresa_request['phone3'];
            $edit_empresas->companies_mail = $edit_empresa_request['mail'];
            $edit_empresas->companies_rfc = $edit_empresa_request['rfc'];
            $edit_empresas->companies_taxRegime = $edit_empresa_request['taxRegime'];
            $edit_empresas->companies_employerRegistration = $edit_empresa_request['employerRegistration'];
            $edit_empresas->companies_representative = $edit_empresa_request['manager'];
            $edit_empresas->companies_referenceProvider = $edit_empresa_request['proveedor'];
            $edit_empresas->companies_calculateTaxes = isset($edit_empresa_request['calcularImpuesto']) ? 1 : 0;
            $edit_empresas->companies_stamped = $request->input('stamped', 0);

            if (isset($edit_empresa_request['changePassword']) && $edit_empresa_request['changePassword'] === 'on') {
                $edit_empresas->companies_passwordKey = Crypt::encrypt($edit_empresa_request['passwordKey']);
            }

            //ruta en base de datos
            $rutaAnterior = $edit_empresas->companies_routeFiles;

            //ruta del formulario
            $ruta_edit = $edit_empresa_request['routeBov'];

            if ($ruta_edit == $rutaAnterior) {
                //obtenemos nombre de los archivos
                $imagePath = $request->file('logoEmpresa');
                $certPath = $request->file('certificadoCer');
                $keyPath = $request->file('certificadoKey');


                if ($imagePath) {
                    $imageName = $ruta_edit . $imagePath->getClientOriginalName();
                    // dd($imageName);
                    Storage::disk('empresas')->put($imageName, File::get($imagePath));
                    $edit_empresas->companies_logo = $imageName;
                }

                if ($certPath) {
                    $certName = $ruta_edit . 'CFDI/' . $certPath->getClientOriginalName();
                    //  dd($certName);
                    Storage::disk('empresas')->put($certName, File::get($certPath));
                    $edit_empresas->companies_routeCertificate = $certName;
                }

                if ($keyPath) {
                    $keyName = $ruta_edit . 'CFDI/' . $keyPath->getClientOriginalName();
                    // dd($imageName);
                    Storage::disk('empresas')->put($keyName, File::get($keyPath));
                    $edit_empresas->companies_routeKey = $keyName;
                }
            } else {
                // dd($ruta_edit_sin)
                if (Storage::disk('empresas')->exists($ruta_edit)) {
                    //Regresamos mensaje que no se puede crear ese directorio ya que existe
                    return redirect()->route('catalogo.empresa.index')->with('status', false)->with('message', 'El directorio ya existe en la base de datos');
                } else {
                    $imagePath = $request->file('logoEmpresa');
                    $certPath = $request->file('certificadoCer');
                    $keyPath = $request->file('certificadoKey');

                    if (isset($imagePath) or (isset($certPath) or (isset($keyPath)))) {
                        if ($imagePath) {
                            $imageName = $ruta_edit . $imagePath->getClientOriginalName();
                            // dd($imageName);
                            Storage::disk('empresas')->put($imageName, File::get($imagePath));
                            $edit_empresas->companies_logo = $imageName;
                        }

                        if ($certPath) {
                            $certName = $ruta_edit . 'CFDI/' . $certPath->getClientOriginalName();
                            //  dd($certName);
                            Storage::disk('empresas')->put($certName, File::get($certPath));
                            $edit_empresas->companies_routeCertificate = $certName;
                        }

                        if ($keyPath) {
                            $keyName = $ruta_edit . 'CFDI/' . $keyPath->getClientOriginalName();
                            // dd($imageName);
                            Storage::disk('empresas')->put($keyName, File::get($keyPath));
                            $edit_empresas->companies_routeKey = $keyName;
                            $edit_empresas->companies_routeFiles = $ruta_edit;
                        }
                    } else {
                        Storage::disk('empresas')->makeDirectory($ruta_edit . 'CFDI/');

                        $edit_empresas->companies_routeFiles = $ruta_edit;
                    }
                }
            }

            try {
                $isUpdate = $edit_empresas->update();
                if ($isUpdate) {
                    $empresaLogueada = session('company');
                    if ($empresaLogueada->companies_id === $edit_empresas->companies_id) {
                        session(['company' => $edit_empresas]);
                    }
                    return redirect()->route('catalogo.empresa.index')->with('status', true)->with('message', 'La empresa se ha actualizado correctamente');
                } else {
                    return redirect()->route('catalogo.empresa.index')->with('status', false)->with('message', 'La empresa no se ha actualizado');
                }
            } catch (\Exception $e) {
                dd($e);
                return redirect()->route('catalogo.empresa.index')->with('status', false)->with('message', 'La empresa no se ha guardado correctamente');
            }
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->route('catalogo.empresa.index')->with('status', false)->with('message', 'La empresa no se ha encontrado');
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
            $empresa_delete = CAT_COMPANIES::where('companies_id', $id)->first();
            $empresa_delete->companies_status = 'Baja';

            $isDelete = $empresa_delete->update();
            $status = false;
            if ($isDelete) {
                $message = 'La empresa se ha dado de baja correctamente';
                $status = true;
            } else {
                $message = 'La empresa no se ha eliminado';
                $status = false;
            }

            return redirect()->route('catalogo.empresa.index')->with('status', $status)->with('message', $message);
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->route('catalogo.empresa.index')->with('status', false)->with('message', 'La empresa no se ha encontrado');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function empresaAction(Request $request)
    {

        // dd($request->all());
        $keyEmpresa = $request->keyCompany;
        $nameEmpresa = $request->nameEmpresa;
        $status = $request->status;

        switch ($request->input('action')) {
            case 'Búsqueda':

                $empresa_collection_filtro = CAT_COMPANIES::whereCompaniesKey($keyEmpresa)->whereCompaniesName($nameEmpresa)->whereCompaniesStatus($status)->get();

                $empresa_filtro_array = $empresa_collection_filtro->toArray();

                // dd($empresa_filtro_array);
                return redirect()->route('catalogo.empresa.index')->with('empresa_filtro_array', $empresa_filtro_array)->with('nameEmpresa', $nameEmpresa)->with('keyEmpresa', $keyEmpresa)->with('status', $status);
                break;

            case 'Exportar excel':
                $empresa = new CatEmpresasExport($keyEmpresa, $nameEmpresa, $status);
                return Excel::download($empresa, 'catalogo_empresas.xlsx');
                break;

            default:
                # code...
                break;
        }
    }

    public function selectPais()
    {
        $country_array = [];
        $country_key_sat_collection = CAT_SAT_PAIS::all();
        $country_key_sat_array = $country_key_sat_collection->toArray();

        foreach ($country_key_sat_array as $key => $value) {
            $country_array[$value['descripcion'] . '-' . $value['c_Pais']] = $value['descripcion'] . ' - ' . $value['c_Pais'];
        }
        return $country_array;
    }

    public function selectCiudad()
    {
        $city_array = [];
        $city_key_sat_collection = CAT_SAT_MUNICIPIO::all();
        $city_key_sat_array = $city_key_sat_collection->toArray();

        // dd($city_key_sat_array);
        foreach ($city_key_sat_array as $key => $value) {
            $city_array[$value['descripcion'] . '-' . $value['c_Municipio']] = $value['descripcion'] . ' - ' . $value['c_Municipio'];
        }
        return $city_array;
    }

    public function selectProveedores()
    {
        $state_array = [];
        $state_key_sat_collection = CAT_PROVIDERS::where('providers_status', 'Alta')->get();
        $state_key_sat_array = $state_key_sat_collection->toArray();

        foreach ($state_key_sat_array as $key => $value) {
            $state_array[$value['providers_key']] = $value['providers_name'];
        }
        return $state_array;
    }

    public function selectEstado()
    {
        $state_array = [];
        $state_key_sat_collection = CAT_SAT_ESTADO::all();
        $state_key_sat_array = $state_key_sat_collection->toArray();

        foreach ($state_key_sat_array as $key => $value) {
            $state_array[$value['nombreEstado'] . '-' . $value['c_Estado']] = $value['nombreEstado'] . ' - ' . $value['c_Estado'];
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
}
