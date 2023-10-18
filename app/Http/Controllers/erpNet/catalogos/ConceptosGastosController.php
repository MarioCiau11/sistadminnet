<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\CatConceptosGastosExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_EXPENSE_CONCEPTS_CATEGORY;
use App\Models\agrupadores\CAT_EXPENSE_CONCEPTS_GROUP;
use App\Models\catalogos\CAT_EXPENSE_CONCEPTS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class ConceptosGastosController extends Controller
{


    public function __construct()
    {
        $this->middleware(['permission:Conceptos de Gastos']);
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
        $cptGastos_collection = CAT_EXPENSE_CONCEPTS::where('expenseConcepts_status', '=', 'Alta')->get();
        return view('page.catalogos.conceptoGastos.index', compact('categoria_array', 'grupo_array', 'cptGastos_collection'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $categoria_array = $this->selectCategoria();
        $grupo_array = $this->selectGrupo();
        return view('page.catalogos.conceptoGastos.create',  compact('categoria_array', 'grupo_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $cptGastos_request = $request->except('_token');
        $isKeyConcepto = CAT_EXPENSE_CONCEPTS::where('expenseConcepts_concept', $cptGastos_request['concepto'])->first();

        if ($isKeyConcepto) {
            $message = "El concepto: " . $cptGastos_request['concepto'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $concept = new CAT_EXPENSE_CONCEPTS();

            $concept->expenseConcepts_concept = $cptGastos_request['concepto'];
            $concept->expenseConcepts_tax = $cptGastos_request['impuesto'];
            $concept->expenseConcepts_retention = $cptGastos_request['retencion'];
            $concept->expenseConcepts_retention2 = $cptGastos_request['retencion2'];
            // $concept->expenseConcepts_retention3 = $cptGastos_request['retencion3'];
            $concept->expenseConcepts_exemptIVA = isset($cptGastos_request['iva']) ? $cptGastos_request['iva'] : 0;
            $concept->expenseConcepts_group = $cptGastos_request['grupo'];
            $concept->expenseConcepts_category = $cptGastos_request['categoria'];
            $concept->expenseConcepts_status = $cptGastos_request['statusDG'];
            try {
                $isSave = $concept->save();
                if ($isSave) {
                    $message = "El concepto: " . $cptGastos_request['concepto'] . " se guardó correctamente";
                    $status = true;
                } else {
                    $message = "El concepto: " . $cptGastos_request['concepto'] . " no se guardó correctamente";
                    $status = false;
                }
            } catch (\Throwable $th) {
                dd($th);
                $message = "El concepto: " . $cptGastos_request['concepto'] . " no se guardó correctamente";
                $status = false;
                return redirect()->route('catalogo.concepto-gastos.index')->with('message', $message)->with('status', $status);
            }
        }

        return redirect()->route('catalogo.concepto-gastos.index')->with('message', $message)->with('status', $status);
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
            $id = decrypt($id);
            $concepto = CAT_EXPENSE_CONCEPTS::findOrFail($id);
            $categoria_array = $this->selectCategoria();
            $grupo_array = $this->selectGrupo();
            return view('page.catalogos.conceptoGastos.show', compact('concepto', 'categoria_array', 'grupo_array'));
        } catch (\Throwable $th) {
            //throw $th;
            $message = "El concepto: " . $id . " no se encontro en la base de datos";
            $status = false;
            return redirect()->route('catalogo.concepto-gastos.index')->with('message', $message)->with('status', $status);
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
            $concepto = CAT_EXPENSE_CONCEPTS::findOrFail($id);
            $categoria_array = $this->selectCategoria();
            $grupo_array = $this->selectGrupo();
            return view('page.catalogos.conceptoGastos.edit', compact('concepto', 'categoria_array', 'grupo_array'));
        } catch (\Throwable $th) {
            $message = "El concepto: " . $id . " no se encontro en la base de datos";
            $status = false;
            return redirect()->route('catalogo.concepto-gastos.index')->with('message', $message)->with('status', $status);
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
        //    dd($request->all());

        try {
            $id = Crypt::decrypt($id);
            $gasto_request =  $request->except('_token');
            $conceptoNuevo = $gasto_request['concepto'];
            $concepto = CAT_EXPENSE_CONCEPTS::where('expenseConcepts_id', $id)->first();

            $conceptoOriginal = $concepto->expenseConcepts_concept;

            // dd($conceptoOriginal, $conceptoNuevo);

            if ($conceptoOriginal != $conceptoNuevo) {
                $isKeyConcepto = CAT_EXPENSE_CONCEPTS::where('expenseConcepts_concept', $conceptoNuevo)->first();
                $isKeyConcepto = true;
            } else {
                $isKeyConcepto = false;
            }

            if ($isKeyConcepto == true) {
                $message = "El concepto: " . $conceptoNuevo . " ya existe en la base de datos";
                $status = false;
            } else {

                $concepto->expenseConcepts_concept = $gasto_request['concepto'];
                $concepto->expenseConcepts_tax = $gasto_request['impuesto'];
                $concepto->expenseConcepts_retention = $gasto_request['retencion'];
                $concepto->expenseConcepts_retention2 = $gasto_request['retencion2'];
                // $concepto->expenseConcepts_retention3 = $gasto_request['retencion3'];
                $concepto->expenseConcepts_exemptIVA = isset($gasto_request['iva']) ? $gasto_request['iva'] : 0;
                $concepto->expenseConcepts_group = $gasto_request['grupo'];
                $concepto->expenseConcepts_category = $gasto_request['categoria'];
                $concepto->expenseConcepts_status = $gasto_request['statusDG'];
                try {
                    $isSave = $concepto->update();
                    if ($isSave) {
                        $message = "El concepto: " . $gasto_request['concepto'] . " se guardó correctamente";
                        $status = true;
                    } else {
                        $message = "El concepto: " . $gasto_request['concepto'] . " no se guardó correctamente";
                        $status = false;
                    }
                } catch (\Throwable $th) {
                    dd($th);
                    $message = "El concepto: " . $gasto_request['concepto'] . " no se guardó correctamente";
                    $status = false;
                    return redirect()->route('catalogo.concepto-gastos.index')->with('message', $message)->with('status', $status);
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            return redirect()->route('catalogo.concepto-gastos.index')->with('message', $message)->with('status', $status);
        }

        return redirect()->route('catalogo.concepto-gastos.index')->with('message', $message)->with('status', $status);
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
            $concepto = CAT_EXPENSE_CONCEPTS::where('expenseConcepts_id', $id)->first();

            $concepto->expenseConcepts_status = 'Baja';
            $isRemoved = $concepto->update();
            if ($isRemoved) {
                $message = "El concepto: " . $concepto->expenseConcepts_concept . " se eliminó correctamente";
                $status = true;
            } else {
                $message = "El concepto: " . $concepto->expenseConcepts_concept . " no se eliminó correctamente";
                $status = false;
            }
            return redirect()->route('catalogo.concepto-gastos.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            //throw $th;
            $message = "El concepto: " . $id . " no se encontro en la base de datos";
            $status = false;
            return redirect()->route('catalogo.concepto-gastos.index')->with('message', $message)->with('status', $status);
        }
    }

    public function conceptoAction(Request $request)
    {

        // dd($request->all());
        $nameConcept = $request->nameConcept;
        $category = $request->categoria;
        $group = $request->grupo;
        $status = $request->status;
        switch ($request->input('action')) {
            case 'Búsqueda':

                $concept_collection = CAT_EXPENSE_CONCEPTS::whereExpenseConceptsConcept($nameConcept)->whereExpenseConceptsGroup($group)->whereExpenseConceptsCategory($category)->whereExpenseConceptsStatus($status)->get();

                $concept_filtro_array = $concept_collection->toArray();

                return redirect()->route('catalogo.concepto-gastos.index')->with('concept_filtro_array', $concept_filtro_array)->with('nameConcept', $nameConcept)->with('categoria', $category)->with('gropo', $group)->with('status', $status);

                break;

            case 'Exportar excel':
                $concepto = new CatConceptosGastosExport($nameConcept, $category, $group, $status);
                return Excel::download($concepto, 'conceptos_gastos.xlsx');
                break;

            default:
                break;
        }
    }

    public function selectCategoria()
    {
        $categoria_array = [];
        $categoria_key_sat_collection = CAT_EXPENSE_CONCEPTS_CATEGORY::where('categoryExpenseConcept_status', 'Alta')->get();
        $categoria_key_sat_array = $categoria_key_sat_collection->toArray();

        foreach ($categoria_key_sat_array as $key => $value) {
            $categoria_array[$value['categoryExpenseConcept_name']] = $value['categoryExpenseConcept_name'];
        }
        return $categoria_array;
    }

    public function selectGrupo()
    {
        $grupo_array = [];
        $grupo_key_sat_collection = CAT_EXPENSE_CONCEPTS_GROUP::where('groupExpenseConcept_status', 'Alta')->get();
        $grupo_key_sat_array = $grupo_key_sat_collection->toArray();

        foreach ($grupo_key_sat_array as $key => $value) {
            $grupo_array[$value['groupExpenseConcept_name']] = $value['groupExpenseConcept_name'];
        }
        return $grupo_array;
    }
}
