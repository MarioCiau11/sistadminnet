<?php

namespace App\Http\Controllers\erpNet\ConfiguracionGeneral;

use App\Exports\ConfFormasPagoExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_FORMS_OF_PAYMENT;
use App\Models\catalogosSAT\CAT_SAT_FORMAPAGO;
use App\Models\catalogosSAT\CAT_SAT_MONEDA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;


class FormasPagoController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:Formas Cobro/Pago']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $formas_collection = CONF_FORMS_OF_PAYMENT::where('formsPayment_status', '=', 'Alta')->orderBy('formsPayment_id', 'desc')->get();
        $formas_array = $formas_collection->toArray();
        return view('page.ConfiguracionGeneral.FormasPago.index', compact('formas_array'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $create_money_array = $this->selectMonedas();
        $create_formaPago_array = $this->selectFormaPago();
        return view('page.ConfiguracionGeneral.FormasPago.create', compact('create_formaPago_array', 'create_money_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conf_formas_request = $request->except('_token'); //rechazamos el token que nos pasará el formulario
        $isKeyForma = CONF_FORMS_OF_PAYMENT::where('formsPayment_key', $conf_formas_request['keyFormaPago'])->first();
        if ($isKeyForma) {
            $message = "La clave: " . $conf_formas_request['keyFormaPago'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $conf_formas = new CONF_FORMS_OF_PAYMENT();

            //guardamos los datos en la base de datos
            $conf_formas->formsPayment_key = $conf_formas_request['keyFormaPago'];
            $conf_formas->formsPayment_name = $conf_formas_request['nameFormaPago'];
            $conf_formas->formsPayment_descript = $conf_formas_request['description'];
            $conf_formas->formsPayment_sat = $conf_formas_request['formaPagoSat'];
            $conf_formas->formsPayment_money = $conf_formas_request['moneda'];
            $conf_formas->formsPayment_status = $conf_formas_request['status'];
            try {
                $isCreate = $conf_formas->save();
                if ($isCreate) {
                    $message = "la clave: " . $conf_formas_request['keyFormaPago'] . " se ha guardado correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido crear la Forma de Pago: " . $conf_formas_request['nameFormaPago'];
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas, no se pudo crear la Forma de Pago: " . $conf_formas_request['nameFormaPago'];
                return redirect()->route('configuracion.formas-pago.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('configuracion.formas-pago.index')->with('message', $message)->with('status', $status);
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

            $forma = CONF_FORMS_OF_PAYMENT::where('formsPayment_id', $id)->first();

            $show_money_array = $this->selectMonedas();
            $show_formaPago_array = $this->selectFormaPago();
            return view('page.ConfiguracionGeneral.FormasPago.show', compact('show_formaPago_array', 'show_money_array', 'forma'));
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.formas-pago.index')->with('message', 'No se pudo mostrar la Forma de Pago')->with('status', false);
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
            $edit_money_array = $this->selectMonedas();
            $edit_formaPago_array = $this->selectFormaPago();
            $formaPago_edit = CONF_FORMS_OF_PAYMENT::where('formsPayment_id', $id)->first();

            return view('page.ConfiguracionGeneral.FormasPago.edit', compact('edit_formaPago_array', 'edit_money_array', 'formaPago_edit'));
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.formas-pago.index')->with('message', 'No se pudo mostrar la Forma de Pago')->with('status', false);
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

            $conf_formas_request = $request->except('_token');
            $formaPago = CONF_FORMS_OF_PAYMENT::where('formsPayment_id', $id)->first();

            $formaPago->formsPayment_name = $conf_formas_request['nameFormaPago'];
            $formaPago->formsPayment_descript = $conf_formas_request['description'];
            $formaPago->formsPayment_sat = $conf_formas_request['formaPagoSat'];
            $formaPago->formsPayment_money = $conf_formas_request['moneda'];
            $formaPago->formsPayment_status = $conf_formas_request['status'];

            try {
                $isUpdate = $formaPago->update();
                if ($isUpdate) {
                    $message = "La Forma de Pago: " . $formaPago['formasPago_clave'] . " se ha actualizado correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido actualizar la Forma de Pago: " . $formaPago['formasPago_clave'];
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas, no se pudo actualizar la Forma de Pago: " . $conf_formas_request['nameFormaPago'];
                return redirect()->route('configuracion.formas-pago.index')->with('message', $message)->with('status', false);
            }

            return redirect()->route('configuracion.formas-pago.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.formas-pago.index')->with('message', 'No se pudo mostrar la Forma de Pago')->with('status', false);
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

            $formasPago_delete = CONF_FORMS_OF_PAYMENT::where('formsPayment_id', $id)->first();
            $formasPago_delete->formsPayment_status = 'Baja';

            $isRemoved = $formasPago_delete->update();
            $status = false;
            if ($isRemoved) {
                $message = "La forma de pago se eliminó correctamente";
                $status = true;
            } else {
                $message = "No se ha podido eliminar la forma de pago";
                $status = false;
            }

            return redirect()->route('configuracion.formas-pago.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.formas-pago.index')->with('message', 'No se pudo mostrar la Forma de Pago')->with('status', false);
        }
    }

    public function formasPagoAction(Request $request)
    {
        $keyForma = $request->keyFormaPago;
        $nameForma = $request->nameFormaPago;
        $status = $request->status;

        switch ($request->input('action')) {
            case 'Búsqueda':

                $formas_collection_filtro = CONF_FORMS_OF_PAYMENT::whereFormsPaymentKey($keyForma)->whereFormsPaymentName($nameForma)->whereFormsPaymentStatus($status)->get();


                $formas_filtro_array = $formas_collection_filtro->toArray();
                return redirect()->route('configuracion.formas-pago.index')->with('formas_filtro_array', $formas_filtro_array)->with('keyForma', $keyForma)->with('nameForma', $nameForma)->with('status', $status);
                break;

            case 'Exportar excel':
                $formasPago = new ConfFormasPagoExport($keyForma, $nameForma, $status);
                return Excel::download($formasPago, 'formas de pago.xlsx');
                break;
            default:
                break;
        }
    }

    public function selectFormaPago()
    {
        $formaPago_array = [];
        $formaPago_key_sat_collection = CAT_SAT_FORMAPAGO::all();
        $formaPago_key_sat_array = $formaPago_key_sat_collection->toArray();

        foreach ($formaPago_key_sat_array as $key => $value) {
            $formaPago_array[$value['c_FormaPago']] = $value['c_FormaPago'] . ' - ' . $value['descripcion'];
        }
        return $formaPago_array;
    }

    public function selectMonedas()
    {
        $money_array = [];
        $money_key_sat_collection = CAT_SAT_MONEDA::all();
        $money_key_sat_array = $money_key_sat_collection->toArray();

        foreach ($money_key_sat_array as $key => $value) {
            $money_array[$value['c_Moneda']] = $value['descripcion'] . ' - ' . $value['c_Moneda'];
        }
        return $money_array;
    }
}
