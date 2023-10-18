<?php

namespace App\Http\Controllers\erpNet\ConfiguracionGeneral;

use App\Exports\ConfMotivosCancelacionExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_REASON_CANCELLATIONS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class MotivosCancelacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $motivosCancelacion = CONF_REASON_CANCELLATIONS::where('reasonCancellations_status', 'Alta')->get();
        return view('page.ConfiguracionGeneral.MotivoCancelacion.index', compact('motivosCancelacion'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('page.ConfiguracionGeneral.MotivoCancelacion.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conf_concepto_request = $request->except('_token');
        $isNameMotivo = CONF_REASON_CANCELLATIONS::where('reasonCancellations_name', $conf_concepto_request['nameMotivo'])->where('reasonCancellations_module', $conf_concepto_request['modulo'])->first();

        if ($isNameMotivo) {
            $message = "El motivo: " . $conf_concepto_request['nameMotivo'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $conf_motivo = new CONF_REASON_CANCELLATIONS();

            //Ahora agregamos la información del request al modelo
            $conf_motivo->reasonCancellations_name = $conf_concepto_request['nameMotivo'];
            $conf_motivo->reasonCancellations_module = $conf_concepto_request['modulo'];
            $conf_motivo->reasonCancellations_status = $conf_concepto_request['status'];

            try {
                $isCreate = $conf_motivo->save();
                if ($isCreate) {
                    $message = "El motivo: " . $conf_concepto_request['nameMotivo'] . " se ha creado correctamente";
                    $status = true;
                } else {
                    $message = "El motivo: " . $conf_concepto_request['nameMotivo'] . " no se ha podido crear";
                    $status = false;
                }
            } catch (\Throwable $th) {
                dd($th);
                $message = "Por favor, vaya con el administrador de sistemas ya que no se pudo crear el concepto: " . $conf_concepto_request['nameMotivo'];
                return redirect()->route('configuracion.motivos-cancelacion.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('configuracion.motivos-cancelacion.index')->with('message', $message)->with('status', $status);
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
            $motivo_show = CONF_REASON_CANCELLATIONS::where('reasonCancellations_id', $id)->first();
            return view('page.ConfiguracionGeneral.MotivoCancelacion.show', compact('motivo_show'));
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.motivos-cancelacion.index')->with('message', 'No se encontró el motivo')->with('status', false);
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
            $motivo_edit = CONF_REASON_CANCELLATIONS::where('reasonCancellations_id', $id)->first();
            return view('page.ConfiguracionGeneral.MotivoCancelacion.edit', compact('motivo_edit'));
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.motivos-cancelacion.index')->with('message', 'No se encontró el motivo')->with('status', false);
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
        $edit_motivo_request = $request->except('_token');
        $motivo = CONF_REASON_CANCELLATIONS::where('reasonCancellations_id', $id)->first();

        $motivo->reasonCancellations_name = $edit_motivo_request['nameMotivo'];
        $motivo->reasonCancellations_module = $edit_motivo_request['modulo'];
        $motivo->reasonCancellations_status = $edit_motivo_request['status'];

        try {
            $isUpdate = $motivo->save();
            if ($isUpdate) {
                $message = "El motivo: " . $edit_motivo_request['nameMotivo'] . " se ha actualizado correctamente";
                $status = true;
            } else {
                $message = "El motivo: " . $edit_motivo_request['nameMotivo'] . " no se ha podido actualizar";
                $status = false;
            }
        } catch (\Throwable $th) {
            $message = "Por favor, vaya con el administrador de sistemas ya que no se pudo actualizar el motivo: " . $edit_motivo_request['nameMotivo'];
            return redirect()->route('configuracion.motivos-cancelacion.index')->with('message', $message)->with('status', false);
        }
        return redirect()->route('configuracion.motivos-cancelacion.index')->with('message', $message)->with('status', $status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $motivo_delete = CONF_REASON_CANCELLATIONS::where('reasonCancellations_id', $id)->first();
        $motivo_delete->reasonCancellations_status = 'Baja';

        $IsRemove = $motivo_delete->save();
        $status = false;
        if ($IsRemove) {
            $message = "El motivo: " . $motivo_delete->reasonCancellations_name . " se ha eliminado correctamente";
            $status = true;
        } else {
            $message = "El motivo: " . $motivo_delete->reasonCancellations_name . " no se ha podido eliminar";
        }

        return redirect()->route('configuracion.motivos-cancelacion.index')->with('message', $message)->with('status', $status);
    }

    public function motivosAction(Request $request)
    {
        $nameMotivo = $request->nameMotivo;
        $modulo = $request->modulo;
        $status = $request->status;

        switch ($request->input('action')) {
            case 'Búsqueda':

                if (!$nameMotivo) {
                    $motivo_collection_filtro = CONF_REASON_CANCELLATIONS::whereStatus($status)->get();
                } else {
                    $motivo_collection_filtro = CONF_REASON_CANCELLATIONS::whereReasonCancellationsName($nameMotivo)->whereStatus($status)->get();
                }

                $motivo_filtro_array = $motivo_collection_filtro->toArray();
                return redirect()->route('configuracion.motivos-cancelacion.index')->with('motivo_filtro_array', $motivo_filtro_array)->with('status', $status)->with('nameMotivo', $nameMotivo)->with('modulo', $modulo);

                break;

            case 'Exportar excel':
                $motivo = new ConfMotivosCancelacionExport($nameMotivo, $modulo, $status);
                return Excel::download($motivo, 'MotivosCancelacion.xlsx');
                break;

            default:
                break;
        }
    }
}
