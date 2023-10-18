<?php

namespace App\Http\Controllers\erpNet\ConfiguracionGeneral;

use App\Http\Controllers\Controller;
use App\Models\catalogosSAT\CAT_SAT_MONEDA;
use Illuminate\Http\Request;
use App\Exports\ConfMonedasExport;
use App\Models\catalogos\CONF_MONEY;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class MonedasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Monedas']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $money_collection = CONF_MONEY::where('money_status', '=', 'Alta')->orderBy('money_id', 'desc')->get();
        $money_array = $money_collection->toArray();

        return view('page.ConfiguracionGeneral.Monedas.index', compact('money_array'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $create_money_array = $this->selectMonedas();
        return view('page.ConfiguracionGeneral.Monedas.create', compact('create_money_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $conf_money_request = $request->except('_token'); //rechazamos el token que nos pasa el formulario
        $isKeyMoney = CONF_MONEY::where('money_key', $conf_money_request['keyMoneda'])->first();
        if ($isKeyMoney) {
            $message = "La clave: " . $conf_money_request['keyMoneda'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $conf_money = new CONF_MONEY(); //creamos un objeto de la clase Conf_Monedas

            //Agremos la nueva información a las columnas correspondientes de la tabla Conf_Monedas
            $conf_money->money_key = $conf_money_request['keyMoneda'];
            $conf_money->money_name = $conf_money_request['nameMoneda'];
            $conf_money->money_change = (float) $conf_money_request['nameTipoCambio'];
            $conf_money->money_descript =  $conf_money_request['nameDescripcion'];
            $conf_money->money_keySat = $conf_money_request['nameclaveSAT'];
            $conf_money->money_status = $conf_money_request['statusDG'];
            try {
                $isCreate =  $conf_money->save();
                if ($isCreate) {
                    $message = "La clave: " . $conf_money_request['keyMoneda'] . " se registró correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido crear la configuración moneda: " . $conf_money_request['nameMoneda'];
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas, no se pudo crear la configuración moneda: " . $conf_money_request['nameMoneda'];
                return redirect()->route('configuracion.monedas.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('configuracion.monedas.index')->with('message', $message)->with('status', $status);
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
            $money = CONF_MONEY::where('money_id', $id)->first();
            $show_money_array = $this->selectMonedas();
            return view('page.ConfiguracionGeneral.Monedas.show', compact('show_money_array', 'money'));
        } catch (\Exception $e) {
            return redirect()->route('configuracion.monedas.index')->with('message', 'No se pudo encontrar la configuración moneda')->with('status', false);
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
            $money_edit = CONF_MONEY::where('money_id', $id)->first();
            return view('page.ConfiguracionGeneral.Monedas.edit', compact('edit_money_array', 'money_edit'));
        } catch (\Exception $e) {
            return redirect()->route('configuracion.monedas.index')->with('message', 'No se pudo encontrar la configuración moneda')->with('status', false);
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
            $edit_money_request = $request->except('_token');
            $money = CONF_MONEY::where('money_id', $id)->first();
            $money->money_name = $edit_money_request['nameMoneda'];
            $money->money_change = (float) $edit_money_request['nameTipoCambio'];
            $money->money_descript =  $edit_money_request['nameDescripcion'];
            $money->money_keySat = $edit_money_request['nameclaveSAT'];
            $money->money_status = $edit_money_request['statusDG'];

            try {
                $isUpdate =  $money->update();
                if ($isUpdate) {
                    $message = "La moneda se actualizó correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido actualizar la moneda";
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas, no se puede actualizar la configuración moneda: ";
                return redirect()->route('configuracion.monedas.index')->with('message', $message)->with('status', false);
            }

            return redirect()->route('configuracion.monedas.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.monedas.index')->with('message', 'No se pudo encontrar la configuración moneda')->with('status', false);
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

            $money_delete = CONF_MONEY::where('money_id', $id)->first();
            $money_delete->money_status = 'Baja';

            $isRemoved = $money_delete->update();
            $status = false;
            if ($isRemoved) {
                $message = "La moneda se eliminó correctamente";
                $status = true;
            } else {
                $message = "No se ha podido eliminar la moneda";
                $status = false;
            }

            return redirect()->route('configuracion.monedas.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.monedas.index')->with('message', 'No se pudo mostrar la moneda')->with('status', false);
        }
    }

    public function moneyAction(Request $request)
    {

        $keyMoneda = $request->keyMoneda;
        $nameMoneda = $request->nameMoneda;
        $status = $request->status;
        switch ($request->input('action')) {
            case 'Búsqueda':

                $money_collection_filtro = CONF_MONEY::whereMonedasKey($keyMoneda)->whereMonedasName($nameMoneda)->whereMonedasStatus($status)->get();

                $money_filtro_array = $money_collection_filtro->toArray();
                return redirect()->route('configuracion.monedas.index')->with('money_filtro_array', $money_filtro_array)->with('keyMoneda', $keyMoneda)->with('nameMoneda', $nameMoneda)->with('status', $status);
                break;

            case 'Exportar excel':
                $money = new ConfMonedasExport($keyMoneda, $nameMoneda, $status);
                return Excel::download($money, 'monedas.xlsx');
                break;

            default:
                break;
        }
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
