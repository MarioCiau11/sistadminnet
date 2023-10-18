<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\CATAlmacenesExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_DEPOTS;
use App\Models\catalogos\CAT_SUCURSALES;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class AlmacenesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Almacén']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $almacenes = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->select('CAT_DEPOTS.*', 'CAT_BRANCH_OFFICES.branchOffices_name')
            ->where('CAT_DEPOTS.depots_status', '=', 'Alta')
            ->WHERE('branchOffices_companyId', '=', session('company')->companies_key)
            ->get();
        return view('page.catalogos.almacen.index', compact('almacenes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $select_sucursales = $this->SelectSucursales();
        // DD($select_sucursales);
        return view('page.catalogos.almacen.create', compact('select_sucursales'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cat_almacenes_request = $request->except('_token');
        $isKeyAlmacen = CAT_DEPOTS::where('depots_key', $cat_almacenes_request['keyAlmacen'])->first();
        if ($isKeyAlmacen) {
            $message = "La clave: " . $cat_almacenes_request['keyAlmacen'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $cat_almacen = new CAT_DEPOTS();


            $cat_almacen->depots_key = $cat_almacenes_request['keyAlmacen'];
            $cat_almacen->depots_name = $cat_almacenes_request['nameAlmacen'];
            $cat_almacen->depots_branchlId = $cat_almacenes_request['sucursal'];
            $cat_almacen->depots_type = $cat_almacenes_request['type'];
            $cat_almacen->depots_status = $cat_almacenes_request['statusDG'];
            try {
                $isCreate = $cat_almacen->save();
                // {{dd ($isCreate);}}

                if ($isCreate) {
                    $message = "La clave: " . $cat_almacenes_request['keyAlmacen'] . " se registró correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido crear el almacen " . $cat_almacenes_request['nameAlmacen'];
                    $status = false;
                }
            } catch (\Throwable $th) {
                dd($th);
                $message = "Por favor comuníquese con el administrador de sistemas ya que no se pudo crear el almacen.";
                return redirect()->route('catalogo.almacen.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('catalogo.almacen.index')->with('message', $message)->with('status', $status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            $id = crypt::decrypt($id);
            $select_sucursales = $this->SelectSucursales();
            $almacen = CAT_DEPOTS::where('depots_id', $id)->first();
            return view('page.catalogos.almacen.show', compact('almacen', 'select_sucursales'));
        } catch (\Throwable $th) {
            return redirect()->route('catalogo.almacen.index')->with('message', 'Por favor comuníquese con el administrador de sistemas ya que no se pudo encontrar el almacen.')->with('status', false);
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
            $almacen_edit = CAT_DEPOTS::where('depots_id', '=', $id)->first();
            $select_sucursales = $this->SelectSucursales();
            return view('page.catalogos.almacen.edit', compact('select_sucursales', 'almacen_edit'));
        } catch (\Exception $e) {
            return redirect()->route('catalogo.almacen.index')->with('message', 'No se pudo encontrar el almacen.')->with('status', false);
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


            $almacen = CAT_DEPOTS::where('depots_id', '=', $id)->first();
            $edit_almacen_request = $request->except('_token');
            $almacen->depots_name = $edit_almacen_request['nameAlmacen'];
            $almacen->depots_status = $edit_almacen_request['statusDG'];
            $almacen->depots_type = $edit_almacen_request['type'];
            $almacen->depots_branchlId = $edit_almacen_request['sucursal'];

            try {
                $isUpdate = $almacen->update();
                if ($isUpdate) {
                    $message = "El almacen se actualizó correctamente";
                    $status = true;
                } else {
                    $message = "No se pudo actualizar el almacen";
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor comuníquese con el administrador de sistemas ya que no se pudo actualizar el almacen.";
                return redirect()->route('catalogo.almacen.index')->with('message', $message)->with('status', false);
            }
            return redirect()->route('catalogo.almacen.index')->with('message', $message)->with('status', $status);
        } catch (\Exception $e) { { {
                    dd($e);
                }
            }
            return redirect()->route('catalogo.almacen.index')->with('message', 'No se pudo encontrar el almacen.')->with('status', false);
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

            $almacen_delete = CAT_DEPOTS::where('depots_id', $id)->first();
            $almacen_delete->depots_status = 'Baja';

            $isRemoved = $almacen_delete->update();
            $status = false;
            if ($isRemoved) {
                $message = "El almacen " . $almacen_delete->almacenes_name . " se dio de baja correctamente";
                $status = true;
            } else {
                $message = "No se ha podido dar de baja el almacen " . $almacen_delete->almacenes_name;
                $status = false;
            }
            return redirect()->route('catalogo.almacen.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('catalogo.almacen.index')->with('message', 'No se pudo encontrar el almacen.')->with('status', false);
        }
    }

    public function almacenAction(Request $request)
    {
        $keyAlmacen = $request->keyAlmacen;
        $nameAlmacen = $request->nameAlmacen;
        $status = $request->status;
        switch ($request->input('action')) {
            case 'Búsqueda':

                $almacen_collection_filtro = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->select('CAT_DEPOTS.*', 'CAT_BRANCH_OFFICES.branchOffices_name')->whereDepotsKey($keyAlmacen)->whereDepotsName($nameAlmacen)->whereDepotsStatus($status)->get();

                return redirect()->route('catalogo.almacen.index')->with('almacen_filtro_array', $almacen_collection_filtro)->with('keyAlmacen', $keyAlmacen)->with('nameAlmacen', $nameAlmacen)->with('status', $status);
                break;

            case 'Exportar excel':
                $almacen = new CATAlmacenesExport($keyAlmacen, $nameAlmacen, $status);
                return Excel::download($almacen, 'almacenes.xlsx');
                break;

            default:
                break;
        }
    }

    function SelectSucursales()
    {
        $sucursales = CAT_BRANCH_OFFICES::where('branchOffices_status', '=', 'Alta')
            ->WHERE('branchOffices_companyId', '=', session('company')->companies_key)->get();

        $sucursales_array = array();
        foreach ($sucursales as $sucursal) {
            $sucursales_array[$sucursal->branchOffices_key] = $sucursal->branchOffices_name;
        }
        return $sucursales_array;
    }
}
