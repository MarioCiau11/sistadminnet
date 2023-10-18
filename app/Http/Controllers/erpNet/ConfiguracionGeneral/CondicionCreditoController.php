<?php

namespace App\Http\Controllers\erpNet\ConfiguracionGeneral;

use App\Exports\ConfCondicionCreditoExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_CREDIT_CONDITIONS;
use App\Models\catalogosSAT\CAT_SAT_METODOPAGO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;


class CondicionCreditoController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:Condiciones de Crédito']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $condCredito_collection = CONF_CREDIT_CONDITIONS::where('creditConditions_status', '=', 'Alta')->orderBy('creditConditions_id', 'desc')->get();
        $condCredito_array = $condCredito_collection->toArray();
        return view('page.ConfiguracionGeneral.CondicionesCredito.index', compact('condCredito_array'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $create_metodoPago_array = $this->selectMetodoPago();
        return view('page.ConfiguracionGeneral.CondicionesCredito.create', compact('create_metodoPago_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conf_condicionCredito_request = $request->except('_token');
        $isCondicionCredito = CONF_CREDIT_CONDITIONS::where('creditConditions_name', $conf_condicionCredito_request['nameCondicionCredito'])->first();
        if ($isCondicionCredito) {
            $message = "El nombre: " . $conf_condicionCredito_request['nameCondicionCredito'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $conf_condCredito = new CONF_CREDIT_CONDITIONS();

            $tipoDias = isset($conf_condicionCredito_request['diasHabiles']) ?
                $conf_condicionCredito_request['diasHabiles']
                : (isset($conf_condicionCredito_request['tiposDias']) ? ($conf_condicionCredito_request['tiposDias'] === 'Naturales' ? 'Lun-Dom' : null) : null);

            $conf_condCredito->creditConditions_name = $conf_condicionCredito_request['nameCondicionCredito'];
            $conf_condCredito->creditConditions_type = $conf_condicionCredito_request['tipoCredito'];
            $conf_condCredito->creditConditions_days = isset($conf_condicionCredito_request['vencimiento']) ? $conf_condicionCredito_request['vencimiento'] : null;
            $conf_condCredito->creditConditions_typeDays = isset($conf_condicionCredito_request['tiposDias']) ? $conf_condicionCredito_request['tiposDias'] : null;
            $conf_condCredito->creditConditions_workDays = $tipoDias;
            $conf_condCredito->creditConditions_paymentMethod = $conf_condicionCredito_request['metodoPago'];
            $conf_condCredito->creditConditions_status = $conf_condicionCredito_request['status'];

            try {
                $isCreate =  $conf_condCredito->save();
                if ($isCreate) {
                    $message = "El nombre: " . $conf_condicionCredito_request['nameCondicionCredito'] . " se registró correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido crear la configuración condición de credito: " . $conf_condicionCredito_request['nameCondicionCredito'];
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas, no se pudo crear la configuración condicion de credito: " . $conf_condicionCredito_request['nameCondicionCredito'];
                return redirect()->route('configuracion.condiciones-credito.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('configuracion.condiciones-credito.index')->with('message', $message)->with('status', $status);
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
            $condCredito = CONF_CREDIT_CONDITIONS::where('creditConditions_id', $id)->first();

            $create_metodoPago_array = $this->selectMetodoPago();

            return view('page.ConfiguracionGeneral.CondicionesCredito.show', compact('condCredito', 'create_metodoPago_array'));
        } catch (\Exception $e) {
            return redirect()->route('configuracion.condiciones-credito.index')->with('message', 'No se pudo encontrar la configuración condición de credito')->with('status', false);
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
            $edit_metodoPago_array = $this->selectMetodoPago();
            $condCredito = CONF_CREDIT_CONDITIONS::where('creditConditions_id', $id)->first();

            return view('page.ConfiguracionGeneral.CondicionesCredito.edit', compact('edit_metodoPago_array', 'condCredito'));
        } catch (\Exception $e) {
            return redirect()->route('configuracion.condiciones-credito.index')->with('message', 'No se pudo encontrar la configuración moneda')->with('status', false);
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

            $conf_condicionCredito_request = $request->except('_token');
            $condCredito = CONF_CREDIT_CONDITIONS::where('creditConditions_id', $id)->first();

            $tipoDias = isset($conf_condicionCredito_request['diasHabiles']) ?
                $conf_condicionCredito_request['diasHabiles']
                : (isset($conf_condicionCredito_request['tiposDias']) ? ($conf_condicionCredito_request['tiposDias'] === 'Naturales' ? 'Lun-Dom' : null) : null);

            $condCredito->creditConditions_type = $conf_condicionCredito_request['tipoCredito'];
            $condCredito->creditConditions_days = isset($conf_condicionCredito_request['vencimiento']) ? $conf_condicionCredito_request['vencimiento'] : null;
            $condCredito->creditConditions_typeDays = isset($conf_condicionCredito_request['tiposDias']) ? $conf_condicionCredito_request['tiposDias'] : null;
            $condCredito->creditConditions_workDays = $tipoDias;
            $condCredito->creditConditions_paymentMethod = $conf_condicionCredito_request['metodoPago'];
            $condCredito->creditConditions_status = $conf_condicionCredito_request['status'];

            try {
                $isUpdate =  $condCredito->update();
                if ($isUpdate) {
                    $message = "La actualización se completó correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido actualizar la condicion de credito";
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas, no se puede actualizar la configuración condición de credito";
                return redirect()->route('configuracion.condiciones-credito.index')->with('message', $message)->with('status', false);
            }

            return redirect()->route('configuracion.condiciones-credito.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.condiciones-credito.index')->with('message', 'No se pudo encontrar la configuración condición de credito')->with('status', false);
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
            $condCredito_delete = CONF_CREDIT_CONDITIONS::where('creditConditions_id', $id)->first();
            $condCredito_delete->creditConditions_status = 'Baja';

            $isRemoved = $condCredito_delete->update();
            $status = false;
            if ($isRemoved) {
                $message = "La condición de credito se eliminó correctamente";
                $status = true;
            } else {
                $message = "No se ha podido eliminar la condición de credito";
                $status = false;
            }

            return redirect()->route('configuracion.condiciones-credito.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.condiciones-credito.index')->with('message', 'No se pudo mostrar la condicion de credito')->with('status', false);
        }
    }


    public function condicionCreditoAction(Request $request)
    {
        $nameCondicionCredito = $request->nameFormaPago;
        $status = $request->status;

        switch ($request->input('action')) {
            case 'Búsqueda':
                $condicionCredito_collection_filtro = CONF_CREDIT_CONDITIONS::whereConditionName($nameCondicionCredito)->whereStatus($status)->get();
                $condicionCredito_filtro_array = $condicionCredito_collection_filtro->toArray();

                return redirect()->route('configuracion.condiciones-credito.index')->with('nameFormaPago', $nameCondicionCredito)->with('status', $status)->with('condicionCredito_filtro_array', $condicionCredito_filtro_array);
                break;

            case 'Exportar excel':
                $condicionCreditoExcel = new ConfCondicionCreditoExport($nameCondicionCredito, $status);
                return Excel::download($condicionCreditoExcel, 'Condiciones de credito.xlsx');
                break;

            default:
                break;
        }
    }

    public function selectMetodoPago()
    {
        $metodoPago_array = [];
        $metodoPago_key_sat_collection = CAT_SAT_METODOPAGO::all();
        $metodoPago_key_sat_array = $metodoPago_key_sat_collection->toArray();

        foreach ($metodoPago_key_sat_array as $key => $value) {
            $metodoPago_array[$value['descripcion']] = $value['c_MetodoPago'] . ' - ' . $value['descripcion'];
        }
        return $metodoPago_array;
    }
}
