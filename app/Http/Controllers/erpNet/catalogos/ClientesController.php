<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\CatClientesExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_CUSTOMERS_CATEGORY;
use App\Models\agrupadores\CAT_CUSTOMERS_GROUP;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CAT_CUSTOMERS_FILES;
use App\Models\catalogos\CONF_CREDIT_CONDITIONS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogosSAT\CAT_SAT_ESTADO;
use App\Models\catalogosSAT\CAT_SAT_MUNICIPIO;
use App\Models\catalogosSAT\CAT_SAT_PAIS;
use App\Models\CatalogosSAT\CAT_SAT_REGIMENFISCAL;
use App\Models\catalogosSAT\CAT_SAT_USOCFDI;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ClientesController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:Clientes']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $cliente_collection = CAT_CUSTOMERS::where('customers_status', '=', 'Alta')->get();
        $categoria_array = $this->selectCategoria();
        $grupo_array = $this->selectGrupo();
        return view('page.catalogos.clientes.index', compact('categoria_array', 'grupo_array', 'cliente_collection'));
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
        $idCustomer = $this->getCostumer();
        $categoria_array = $this->selectCategoria();
        $grupo_array = $this->selectGrupo();
        $condicion_array = $this->selectCondiciones();
        $create_regimen_array = $this->selectRegimenFiscal();
        $create_usocfdi_array = $this->selectCFDI();
        return view('page.catalogos.clientes.create', compact('create_pais_array', 'create_estado_array', 'create_municipio_array', 'idCustomer', 'categoria_array', 'grupo_array', 'condicion_array', 'create_regimen_array', 'create_usocfdi_array'));
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

        $cliente_request = $request->except('_token');

        $customer = new CAT_CUSTOMERS();

        $cliente_request['tipoPersona'] == 0 ? $customer->customers_type = 0 : $customer->customers_type = 1;

        $customer->customers_businessName = $cliente_request['razonSocial'];
        $customer->customers_RFC = $cliente_request['rfc'];
        $customer->customers_CURP = $cliente_request['curp'];
        $customer->customers_name = $cliente_request['nameRepresentante'];
        $customer->customers_lastName = $cliente_request['apellidoPaterno'];
        $customer->customers_lastName2 = $cliente_request['apellidoMaterno'];
        $customer->customers_cellphone = $cliente_request['telefono'];
        $customer->customers_mail = $cliente_request['email'];
        $customer->customers_addres = $cliente_request['direccion'];
        $customer->customers_roads = $cliente_request['entreVialidades'];
        $customer->customers_outdoorNumber = $cliente_request['numExt'];
        $customer->customers_interiorNumber = $cliente_request['numInt'];
        $customer->customers_colonyFractionation = $cliente_request['coloniaBusqueda'];
        $customer->customers_townMunicipality = $cliente_request['localidadMuni'];
        $customer->customers_state = $cliente_request['estado'];
        $customer->customers_country = $cliente_request['pais'];
        $customer->customers_cp = $cliente_request['cpBusqueda'];
        $customer->customers_phone1 = $cliente_request['telefono1'];
        $customer->customers_phone2 = $cliente_request['telefono2'];
        $customer->customers_contact1 = $cliente_request['contacto1'];
        $customer->customers_mail1 = $cliente_request['email1'];
        $customer->customers_contact2 = $cliente_request['contacto2'];
        $customer->customers_mail2 = $cliente_request['email2'];
        $customer->customers_observations = $cliente_request['observaciones'];
        $customer->customers_group = $cliente_request['grupo'];
        $customer->customers_category = $cliente_request['categoria'];
        $customer->customers_status = $cliente_request['statusDG'];
        $customer->customers_priceList = $cliente_request['listaPrecios'];
        $customer->customers_creditCondition = $cliente_request['condicionPago'];
        $customer->customers_creditLimit = $cliente_request['limiteCredito'];
        $customer->customers_identificationCFDI = $cliente_request['identificadorCFDI'];
        $customer->customers_taxRegime = $cliente_request['regimenFiscal'];
        $customer->customers_numRegIdTrib = $cliente_request['identidadFiscal'];

        $nameDocs = $cliente_request['nombreDocumento'];
        $fileDocs = $request->file('field_name');

        // dd($nameDocs);
        if ($nameDocs[0] != null) {
            foreach ($nameDocs as $key => $value) {
                $nameDocs[$key] == null ? $nameDocs[$key] = 'Doc-' . time() : $nameDocs[$key] = $nameDocs[$key];
            }
        }

        // $nameDocs[0] == null ? $nameDocs[0]= 'Doc-'.time() : $nameDocs[0];

        try {
            $customerSave = $customer->save();
            $customer_data = $customer::latest('customers_key')->first();
            $lastCustomer = $customer_data->customers_key;




            //Obtenemos los parametros generales de la empresa
            $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

            if ($parametro === null || $parametro->generalParameters_filesCustomers === Null || $parametro->generalParameters_filesCustomers === '') {
                $empresaRuta = session('company')->companies_routeFiles . 'Clientes';
            } else {
                $empresaRuta = session('company')->companies_routeFiles . $parametro->generalParameters_filesCustomers;
            }


            if ($nameDocs[0] != null) {
                foreach ($nameDocs as $key => $value) {
                    $customer_documents = new CAT_CUSTOMERS_FILES();
                    $customer_documents->customersFiles_keyCustomer = $lastCustomer;
                    $customer_documents->customersFiles_path = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $lastCustomer . '-' . $nameDocs[$key]);
                    $customer_documents->customersFiles_file = $fileDocs[$key]->getClientOriginalName();
                    $rutaFinal = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $lastCustomer . '/' . $fileDocs[$key]->getClientOriginalName());
                    Storage::disk('empresas')->put($rutaFinal, File::get($fileDocs[$key]));

                    $customer_documents->save();
                }
            }

            if ($customerSave) {
                $message = 'Cliente creado correctamente';
                $status = true;
            } else {
                $message = 'Error al crear al cliente';
                $status = false;
            }

            $increment = $lastCustomer != null ? $lastCustomer : 0;
            DB::statement('DBCC CHECKIDENT ("CAT_CUSTOMERS", RESEED, ' . $increment . ')');
        } catch (\Exception $e) {
            dd($e);
            $message = 'Error al crear el proveedor';
            $status = false;
        }

        return redirect()->route('catalogo.clientes.index')->with('status', $status)->with('message', $message);
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
            $customer = CAT_CUSTOMERS::find($id);
            $show_pais_array = $this->selectPais();
            $show_estado_array = $this->selectEstado();
            $show_municipio_array = $this->selectMunicipio();
            $idCustomer = $this->getCostumer();
            $categoria_array = $this->selectCategoria();
            $grupo_array = $this->selectGrupo();
            $condicion_array = $this->selectCondiciones();
            $show_regimen_array = $this->selectRegimenFiscal();
            $show_usocfdi_array = $this->selectCFDI();

            $customer_documents = CAT_CUSTOMERS_FILES::where('customersFiles_keyCustomer', $id)->get();

            return view('page.catalogos.clientes.show', compact('show_pais_array', 'show_estado_array', 'show_municipio_array', 'idCustomer', 'categoria_array', 'grupo_array', 'condicion_array', 'show_regimen_array', 'show_usocfdi_array', 'customer', 'customer_documents'));
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('catalogo.clientes.index')->with('status', false)->with('message', 'Error al mostrar el cliente');
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
            $customer = CAT_CUSTOMERS::find($id);
            $edit_pais_array = $this->selectPais();
            $edit_estado_array = $this->selectEstado();
            $edit_municipio_array = $this->selectMunicipio();
            $edit_regimen_array = $this->selectRegimenFiscal();
            $categoria_array = $this->selectCategoria();
            $grupo_array = $this->selectGrupo();
            $condicion_array = $this->selectCondiciones();
            $show_usocfdi_array = $this->selectCFDI();
            $show_regimen_array = $this->selectRegimenFiscal();
            $documentosCustomer = CAT_CUSTOMERS_FILES::where('customersFiles_keyCustomer', $id)->get();

            return view('page.catalogos.clientes.edit', compact('edit_pais_array', 'edit_estado_array', 'edit_municipio_array', 'edit_regimen_array', 'customer',  'categoria_array', 'grupo_array', 'condicion_array', 'documentosCustomer', 'show_usocfdi_array', 'show_regimen_array'));
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->route('catalogo.clientes.index')->with('status', false)->with('message', 'Error al editar el cliente');
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
            $cliente_request = $request->except('_token');
            $customer = CAT_CUSTOMERS::find($id);


            $cliente_request['tipoPersona'] == 0 ? $customer->customers_type = 0 : $customer->customers_type = 1;

            $customer->customers_businessName = $cliente_request['razonSocial'];
            $customer->customers_RFC = $cliente_request['rfc'];
            $customer->customers_CURP = $cliente_request['curp'];
            $customer->customers_name = $cliente_request['nameRepresentante'];
            $customer->customers_lastName = $cliente_request['apellidoPaterno'];
            $customer->customers_lastName2 = $cliente_request['apellidoMaterno'];
            $customer->customers_cellphone = $cliente_request['telefono'];
            $customer->customers_mail = $cliente_request['email'];
            $customer->customers_addres = $cliente_request['direccion'];
            $customer->customers_roads = $cliente_request['entreVialidades'];
            $customer->customers_outdoorNumber = $cliente_request['numExt'];
            $customer->customers_interiorNumber = $cliente_request['numInt'];
            $customer->customers_colonyFractionation = $cliente_request['coloniaBusqueda'];
            $customer->customers_townMunicipality = $cliente_request['localidadMuni'];
            $customer->customers_state = $cliente_request['estado'];
            $customer->customers_country = $cliente_request['pais'];
            $customer->customers_cp = $cliente_request['cpBusqueda'];
            $customer->customers_phone1 = $cliente_request['telefono1'];
            $customer->customers_phone2 = $cliente_request['telefono2'];
            $customer->customers_contact1 = $cliente_request['contacto1'];
            $customer->customers_mail1 = $cliente_request['email1'];
            $customer->customers_contact2 = $cliente_request['contacto2'];
            $customer->customers_mail2 = $cliente_request['email2'];
            $customer->customers_observations = $cliente_request['observaciones'];
            $customer->customers_group = $cliente_request['grupo'];
            $customer->customers_category = $cliente_request['categoria'];
            $customer->customers_status = $cliente_request['statusDG'];
            $customer->customers_priceList = $cliente_request['listaPrecios'];
            $customer->customers_creditCondition = $cliente_request['condicionPago'];
            $customer->customers_creditLimit = $cliente_request['limiteCredito'];
            $customer->customers_identificationCFDI = $cliente_request['identificadorCFDI'];
            $customer->customers_taxRegime = $cliente_request['regimenFiscal'];
            $customer->customers_numRegIdTrib = $cliente_request['identidadFiscal'];


            $nameDocs = isset($cliente_request['nombreDocumento']) ? $cliente_request['nombreDocumento'] : null;
            $fileDocs = isset($cliente_request['field_name']) ? $cliente_request['field_name'] : null;


            //Obtenemos los parametros generales de la empresa
            $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

            if ($parametro === null || $parametro->generalParameters_filesCustomers === Null || $parametro->generalParameters_filesCustomers === '') {
                $empresaRuta = session('company')->companies_routeFiles . 'Clientes';
            } else {
                $empresaRuta = session('company')->companies_routeFiles . $parametro->generalParameters_filesCustomers;
            }


            if (!isset($cliente_request['docsEdit'])) {

                if ($nameDocs != null) {
                    foreach ($nameDocs as $key => $value) {

                        $customer_doc = new CAT_CUSTOMERS_FILES();
                        $customer_doc->customersFiles_keyCustomer = $customer->customers_key;
                        $customer_doc->customersFiles_path =  str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $customer->customers_key . '-' . $nameDocs[$key]);
                        $customer_doc->customersFiles_file = $fileDocs[$key]->getClientOriginalName();
                        $rutaFinal = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $customer->customers_key . '/' . $fileDocs[$key]->getClientOriginalName());
                        Storage::disk('empresas')->put($rutaFinal, File::get($fileDocs[$key]));
                        $customer_doc->save();
                    }
                }
            } else {
                foreach ($cliente_request['docsEdit'] as $key => $id) {
                    $customerEdit_doc = CAT_CUSTOMERS_FILES::find($id);

                    $fieldName = isset($cliente_request[$id . '-nombre']) ? $cliente_request[$id . '-nombre'] : null;

                    $fieldFile = isset($cliente_request[$id . '-file']) ? $cliente_request[$id . '-file'] : null;


                    if ($fieldName != null) {
                        $customerEdit_doc->customersFiles_path = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $customerEdit_doc->customersFiles_keyCustomer . '-' . $fieldName);
                    }

                    if ($fieldFile != null) {
                        $customerEdit_doc->customersFiles_file = $fieldFile->getClientOriginalName();
                        $rutaFinal = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $customer->customers_key . '/' . $fieldFile->getClientOriginalName());
                        Storage::disk('empresas')->put($rutaFinal, File::get($fieldFile));
                    }

                    $customerEdit_doc->update();
                }
                //Guardamos los documentos editados del proveedor
            }

            try {
                $isUpdate = $customer->update();
                if ($isUpdate) {
                    $message = 'Cliente editado correctamente';
                    $status = true;
                } else {
                    $message = 'Error al editar el cliente';
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas, no se puede actualizar el cliente: ";
                return redirect()->route('catalogo.clientes.index')->with('message', $message)->with('status', false);
            }

            return redirect()->route('catalogo.clientes.index')->with('status', $status)->with('message', $message);
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->route('catalogo.clientes.index')->with('status', false)->with('message', 'Error al editar el cliente');
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
            $customer_delete = CAT_CUSTOMERS::find($id);
            $customer_delete->customers_status = 'Baja';

            $isDelete = $customer_delete->update();
            if ($isDelete) {
                $message = 'Cliente eliminado correctamente';
                $status = true;
            } else {
                $message = 'Error al eliminar el cliente';
                $status = false;
            }

            return redirect()->route('catalogo.clientes.index')->with('status', $status)->with('message', $message);
        } catch (\Throwable $th) {
            $message = "Por favor, vaya con el administrador de sistemas, no se puede eliminar el cliente: ";
            return redirect()->route('catalogo.clientes.index')->with('message', $message)->with('status', false);
        }
    }

    public function customerAction(Request $request)
    {
        // dd($request->all());
        $keyCustomer = $request->keyCliente;
        $nameCustomer = $request->nameCliente;
        $bussinesName = $request->razonSocial;
        $category = $request->categoria;
        $group = $request->grupo;
        $status = $request->status;

        switch ($request->input('action')) {
            case 'Búsqueda':

                $customer_collection = CAT_CUSTOMERS::whereCustomersKey($keyCustomer)->whereCustomersName($nameCustomer)->whereCustomersBusinessName($bussinesName)->whereCustomersCategory($category)->whereCustomersGroup($group)->whereCustomersStatus($status)->get();

                $customer_filtro_array = $customer_collection->toArray();

                return redirect()->route('catalogo.clientes.index')->with('customer_filtro_array', $customer_filtro_array)->with('keyCustomer', $keyCustomer)->with('nameCustomer', $nameCustomer)->with('bussinesName', $bussinesName)->with('category', $category)->with('group', $group)->with('status', $status);


                break;

            case 'Exportar excel':
                $cliente = new CatClientesExport($keyCustomer, $nameCustomer, $bussinesName, $category, $group, $status);
                return Excel::download($cliente, 'catalogo_clientes.xlsx');
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
            $municipio_array[$value['c_Municipio'] . '-' . $value['c_Estado']] = $value['descripcion'] . ' - ' . $value['c_Municipio'];
        }
        return $municipio_array;
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

    public function selectCategoria()
    {
        $categoria_array = [];
        $categoria_key_sat_collection = CAT_CUSTOMERS_CATEGORY::where('categoryCostumer_status', 'Alta')->get();
        $categoria_key_sat_array = $categoria_key_sat_collection->toArray();

        foreach ($categoria_key_sat_array as $key => $value) {
            $categoria_array[$value['categoryCostumer_name']] = $value['categoryCostumer_name'];
        }
        return $categoria_array;
    }

    public function selectGrupo()
    {
        $grupo_array = [];
        $grupo_key_sat_collection = CAT_CUSTOMERS_GROUP::where('groupCustomer_status', 'Alta')->get();
        $grupo_key_sat_array = $grupo_key_sat_collection->toArray();

        foreach ($grupo_key_sat_array as $key => $value) {
            $grupo_array[$value['groupCustomer_name']] = $value['groupCustomer_name'];
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

    public function selectCFDI()
    {
        $cfdi_array = [];
        $cfdi_key_sat_collection = CAT_SAT_USOCFDI::all();
        $cfdi_key_sat_array = $cfdi_key_sat_collection->toArray();

        foreach ($cfdi_key_sat_array as $key => $value) {
            $cfdi_array[$value['c_UsoCFDI']] = $value['descripcion'] . ' - ' . $value['c_UsoCFDI'];
        }
        return $cfdi_array;
    }

    public function getCostumer()
    {
        $providerLast = CAT_CUSTOMERS::count();
        $getId = $providerLast + 1;
        return $getId;
    }
}
