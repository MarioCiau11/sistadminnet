<?php

namespace App\Http\Controllers\erpNet\ConfiguracionGeneral;

use App\Exports\ConfConceptosModuloExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_MODULES_CONCEPT;
use App\Models\catalogos\CONF_MODULES_CONCEPT_MOVEMENT;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;

class ConceptoModulosController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:Conceptos de Módulos']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $concepto_collection = CONF_MODULES_CONCEPT::where('moduleConcept_status', '=', 'Alta')->orderBy('moduleConcept_id', 'desc')->get();
        $concepto_array = $concepto_collection->toArray();
        return view('page.ConfiguracionGeneral.ConceptoModulos.index', compact('concepto_array'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('page.ConfiguracionGeneral.ConceptoModulos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conf_concepto_request = $request->except('_token'); // Obtenemos todos los datos excepto el token
        $isNameConcepto = CONF_MODULES_CONCEPT::where('moduleConcept_name', $conf_concepto_request['nameConcepto'])->where('moduleConcept_module', $conf_concepto_request['modulo'])->first();
        if ($isNameConcepto) {
            $message = "El concepto: " . $conf_concepto_request['nameConcepto'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $conf_concepto = new CONF_MODULES_CONCEPT();

            $conf_concepto->moduleConcept_name = $conf_concepto_request['nameConcepto'];
            $conf_concepto->moduleConcept_module = $conf_concepto_request['modulo'];
            // $conf_concepto->moduleConcept_movement = $conf_concepto_request['movimiento'];
            $conf_concepto->moduleConcept_prodServ = isset($conf_concepto_request['prodServ']) ? $conf_concepto_request['prodServ'] : null;
            $conf_concepto->moduleConcept_status = $conf_concepto_request['status'];
            try {
                $isCreate = $conf_concepto->save();
                // dd($conf_concepto);

                foreach ($conf_concepto_request['movimiento'] as $key => $value){
                    $conf_concepto_movement = new CONF_MODULES_CONCEPT_MOVEMENT();
                    $conf_concepto_movement->moduleMovement_conceptID = $conf_concepto->moduleConcept_id;
                    $conf_concepto_movement->moduleMovement_moduleName = $conf_concepto->moduleConcept_module;
                    $conf_concepto_movement->moduleMovement_movementName = $value;
                    $conf_concepto_movement->save();
                }
                if ($isCreate) {
                    $message = "El concepto: " . $conf_concepto_request['nameConcepto'] . " se ha creado correctamente";
                    $status = true;
                } else {
                    $message = "El concepto: " . $conf_concepto_request['nameConcepto'] . " no se ha creado correctamente";
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas ya que no se pudo crear el concepto: " . $conf_concepto_request['nameConcepto'];
                return redirect()->route('configuracion.concepto-modulos.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('configuracion.concepto-modulos.index')->with('message', $message)->with('status', $status);
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
            $id = Crypt::decrypt($id);
            $movimientos = CONF_MODULES_CONCEPT_MOVEMENT::where('moduleMovement_conceptID', $id)->get();


            foreach ($movimientos as $key => $value) {
                $movimientosRelacionadosConcepto[] = $value->moduleMovement_movementName;
            }
            $movimientosList = CONF_MODULES_CONCEPT_MOVEMENT::where('moduleMovement_conceptID', $id)->get();
            $concepto_show = CONF_MODULES_CONCEPT::where('moduleConcept_id', $id)->first();
            return view('page.ConfiguracionGeneral.ConceptoModulos.show', compact('concepto_show', 'movimientosRelacionadosConcepto', 'movimientosList'));
        } catch (\Exception $e) {
            return redirect()->route('configuracion.concepto-modulos.index')->with('message', 'No se encontró el concepto')->with('status', false);
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
            $movimientosRelacionadosConcepto = [];


            $movimientos = CONF_MODULES_CONCEPT_MOVEMENT::where('moduleMovement_conceptID', $id)->get();
            

            foreach ($movimientos as $key => $value) {
                $movimientosRelacionadosConcepto[] = $value->moduleMovement_movementName;
            }
            $movimientosList = CONF_MODULES_CONCEPT_MOVEMENT::where('moduleMovement_conceptID', $id)->get();
            // dd($movimientosRelacionadosConcepto);
            $concepto_edit = CONF_MODULES_CONCEPT::where('moduleConcept_id', $id)->first();
            return view('page.ConfiguracionGeneral.ConceptoModulos.edit', compact('concepto_edit', 'movimientosRelacionadosConcepto', 'movimientosList'));
        } catch (\Exception $e) {
            return redirect()->route('configuracion.concepto-modulos.index')->with('message', 'No se encontró el concepto')->with('status', false);
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
        $edit_concepto_request = $request->except('_token'); // Obtenemos todos los datos excepto el token
        $concepto = CONF_MODULES_CONCEPT::where('moduleConcept_id', $id)->first();
        $concepto->moduleConcept_name = $edit_concepto_request['nameConcepto'];
        $concepto->moduleConcept_module = $edit_concepto_request['modulo'];
        // $concepto->moduleConcept_movement = $edit_concepto_request['movimiento'];
        $concepto->moduleConcept_prodServ = isset($edit_concepto_request['prodServ']) ? $edit_concepto_request['prodServ'] : null;
        $concepto->moduleConcept_status = $edit_concepto_request['status'];

        try {
            $isUpdate = $concepto->save();

            CONF_MODULES_CONCEPT_MOVEMENT::where('moduleMovement_conceptID', $id)->delete();

            foreach ($edit_concepto_request['movimiento'] as $key => $value){
                $conf_concepto_movement = new CONF_MODULES_CONCEPT_MOVEMENT();
                $conf_concepto_movement->moduleMovement_conceptID = $concepto->moduleConcept_id;
                $conf_concepto_movement->moduleMovement_moduleName = $concepto->moduleConcept_module;
                $conf_concepto_movement->moduleMovement_movementName = $value;
                $conf_concepto_movement->save();
            }
            if ($isUpdate) {
                $message = "El concepto: " . $edit_concepto_request['nameConcepto'] . " se ha actualizado correctamente";
                $status = true;
            } else {
                $message = "El concepto: " . $edit_concepto_request['nameConcepto'] . " no se actualizó";
                $status = false;
            }
        } catch (\Throwable $th) {
            $message = "Por favor, vaya con el administrador de sistemas ya que no se pudo actualizar el concepto: " . $edit_concepto_request['nameConcepto'];
            return redirect()->route('configuracion.concepto-modulos.index')->with('message', $message)->with('status', false);
        }

        return redirect()->route('configuracion.concepto-modulos.index')->with('message', $message)->with('status', $status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $concepto_delete = CONF_MODULES_CONCEPT::where('moduleConcept_id', $id)->first();
        $concepto_delete->moduleConcept_status = 'Baja';

        $IsRemoved = $concepto_delete->update();
        $status = false;
        if ($IsRemoved) {
            $message = "El concepto: " . $concepto_delete->moduleConcept_name . " se ha eliminado correctamente";
            $status = true;
        } else {
            $message = "El concepto: " . $concepto_delete->moduleConcept_name . " no se ha podido eliminar";
            $status = false;
        }

        return redirect()->route('configuracion.concepto-modulos.index')->with('message', $message)->with('status', $status);
    }

    public function conceptosAction(Request $request)
    {
        $nameConcepto = $request->nameConcepto;
        $nameProceso = $request->nameProceso;
        $status = $request->status;
        switch ($request->input('action')) {
            case 'Búsqueda':

                if (!$nameConcepto) {
                    $concepto_collection_filtro = CONF_MODULES_CONCEPT::whereStatus($status)
                    ->whereConceptModule($nameProceso)
                    ->get();
                } else {
                    $concepto_collection_filtro = CONF_MODULES_CONCEPT::whereConceptName($nameConcepto)
                    ->whereConceptModule($nameProceso)
                    ->whereStatus($status)->get();
                }

                $concepto_filtro_array = $concepto_collection_filtro->toArray();
                return redirect()->route('configuracion.concepto-modulos.index')->with('concepto_filtro_array', $concepto_filtro_array)
                ->with('nameConcepto', $nameConcepto)
                ->with('nameProceso', $nameProceso)
                ->with('status', $status);

                break;

            case 'Exportar excel':
                $concepto = new ConfConceptosModuloExport($nameConcepto, $nameProceso, $status);
                return Excel::download($concepto, 'conceptos.xlsx');
                break;

            default:
                break;
        }
    }
}
