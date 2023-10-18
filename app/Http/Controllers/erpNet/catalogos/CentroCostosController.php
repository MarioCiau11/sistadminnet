<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\CATCostCenterExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_COST_CENTER;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class CentroCostosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware(['permission:Centro de Costos']);
    }
    
    public function index()
    {
        $centroCostos = CAT_COST_CENTER::where('costCenter_status', '=', 'Alta')->orderBy('costCenter_id', 'desc')->get();
        return view('page.catalogos.CentroCostos.index', compact('centroCostos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('page.catalogos.CentroCostos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $centroCostos_request = $request->except('_token');
        $iskeyExist = CAT_COST_CENTER::where('costCenter_key', '=', $centroCostos_request['keyCentroCosto'])->first();
        if ($iskeyExist) {
            $message = "La clave: " . $centroCostos_request['keyCentroCosto'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $centroCostos = new CAT_COST_CENTER();
            $centroCostos->costCenter_key = $centroCostos_request['keyCentroCosto'];
            $centroCostos->costCenter_name = $centroCostos_request['nameCentroCosto'];
            $centroCostos->costCenter_status = $centroCostos_request['status'];
            // dd($centroCostos);
            try {
                $isCreated = $centroCostos->save();
                // dd($isCreated);
                if ($isCreated) {
                    $message = "El centro de costo: " . $centroCostos_request['nameCentroCosto'] . " se ha creado correctamente";
                    $status = true;
                } else {
                    $message = "El centro de costo: " . $centroCostos_request['nameCentroCosto'] . " no se ha podido crear";
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya a la sección de soporte y reporte el siguiente error: " . $th->getMessage();
                $status = false;
                return redirect()->route('catalogo.centroCostos.index')->with('status', $status)->with('message', $message);
            }
        }
        return redirect()->route('catalogo.centroCostos.index')->with('status', $status)->with('message', $message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            $id = Crypt::decrypt($id);
            $centroCostos = CAT_COST_CENTER::where('costCenter_id', '=', $id)->first();
            return view('page.catalogos.CentroCostos.show', compact('centroCostos'));
        } catch (\Throwable $th) {
            $message = "Por favor, vaya a la sección de soporte y reporte el siguiente error: " . $th->getMessage();
            $status = false;
            return redirect()->route('catalogo.centroCostos.index')->with('status', $status)->with('message', $message);
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
        try{
        $id = Crypt::decrypt($id);
        $centro_edit = CAT_COST_CENTER::where('costCenter_id', '=', $id)->first();
        return view('page.catalogos.CentroCostos.edit', compact('centro_edit'));
        } catch (\Throwable $th) {
            $message = "Por favor, vaya a la sección de soporte y reporte el siguiente error: " . $th->getMessage();
            $status = false;
            return redirect()->route('catalogo.centroCostos.index')->with('status', $status)->with('message', $message);
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
        try {
            $id = Crypt::decrypt($id);

            $centro = CAT_COST_CENTER::where('costCenter_id', '=', $id)->first();
            $edit_centro_request = $request->except('_token');
            $centro->costCenter_name = $edit_centro_request['nameCentroCosto'];
            $centro->costCenter_status = $edit_centro_request['status'];

            try {
                $isUpdated = $centro->save();
                if ($isUpdated) {
                    $message = "El centro de costo: " . $edit_centro_request['nameCentroCosto'] . " se ha actualizado correctamente";
                    $status = true;
                } else {
                    $message = "El centro de costo: " . $edit_centro_request['nameCentroCosto'] . " no se ha podido actualizar";
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya a la sección de soporte y reporte el siguiente error: " . $th->getMessage();
                $status = false;
                return redirect()->route('catalogo.centroCostos.index')->with('status', $status)->with('message', $message);
            }
            return redirect()->route('catalogo.centroCostos.index')->with('status', $status)->with('message', $message);
        } catch (\Throwable $th) {
            $message = "Por favor, vaya a la sección de soporte y reporte el siguiente error: " . $th->getMessage();
            $status = false;
            return redirect()->route('catalogo.centroCostos.index')->with('status', $status)->with('message', $message);
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
            $centro_delete = CAT_COST_CENTER::where('costCenter_id', '=', $id)->first();
            $centro_delete->costCenter_status = 'Baja';

            $isRemoved = $centro_delete->update();
            $status = false;
            if ($isRemoved) {
                $message = "El centro de costo: " . $centro_delete->costCenter_name . " se ha eliminado correctamente";
                $status = true;
            } else {
                $message = "El centro de costo: " . $centro_delete->costCenter_name . " no se ha podido eliminar";
                $status = false;
            }
            return redirect()->route('catalogo.centroCostos.index')->with('status', $status)->with('message', $message);
        } catch (\Throwable $th) {
            $message = "Por favor, vaya a la sección de soporte y reporte el siguiente error: " . $th->getMessage();
            $status = false;
            return redirect()->route('catalogo.centroCostos.index')->with('status', $status)->with('message', $message);
        }
    }

    public function centroCostosAction(Request $request)
    {
        $keyCentroCosto = $request->keyCentroCosto;
        $nameCentroCosto = $request->nameCentroCosto;
        $status = $request->status;
        
        switch ($request->input('action')) {
            case 'Búsqueda':
                $centroCostos_collection_filtro = CAT_COST_CENTER::whereCostCenterKey($keyCentroCosto)
                ->whereCostCenterName($nameCentroCosto)
                ->whereCostCenterStatus($status)
                ->get();
    
                $centroCostos_filtro_array = $centroCostos_collection_filtro->toArray();
    
                return redirect()->route('catalogo.centroCostos.index')->with('centroCostos_filtro_array', $centroCostos_filtro_array)->with('keyCentroCosto', $keyCentroCosto)->with('nameCentroCosto', $nameCentroCosto)->with('status', $status);
                break;

                case 'Exportar excel':
                    $centroCostos = new CATCostCenterExport($keyCentroCosto, $nameCentroCosto, $status);
                    return Excel::download($centroCostos, 'centroCostos.xlsx');
                    break;
            
            default:
                # code...
                break;
        }
    }

}