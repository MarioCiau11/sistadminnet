<?php

namespace App\Http\Controllers\erpNet\Timbrado;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\historicos\HIST_STAMPED;
use Illuminate\Http\Request;

class TimbresController extends Controller
{
    public $company;

    public function __construct(CAT_COMPANIES $company)
    {
        $this->company = $company;
    }
    public function index()
    {
        $company = new CAT_COMPANIES();
        $stamps = new HIST_STAMPED();
        // dd($stamps->getTotalAyer());

        // dd($company->getCompany());
        return view('page.Timbres.index', [
            'company' => $company->getCompany(),
            'total' => $stamps->getTotalTimbres(),
            'totalAyer' => $stamps->getTotalAyer(),
            'totalHoy' => $stamps->getTotalHoy(),
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $stamps = CAT_COMPANIES::where('companies_status', "Alta")->get();

        return view('page.Timbres.create', compact('stamps'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $timbres = $request->timbres;

        foreach ($timbres as $timbre) {
            $company = CAT_COMPANIES::where('companies_key', $timbre['id'])->first();

            if ($company->companies_AvailableStamps != $timbre['timbres']) {
                $company->companies_AvailableStamps = $timbre['timbres'];
                $company->companies_LastUpdateStamps = date('Y-m-d H:i:s');
                $company->save();
            }
        }
        if ($company) {
            $message = 'Timbres agregados correctamente';
            $status = 'success';
            $redirect = route('timbrado.index');
        } else {
            $message = 'Error al agregar los timbres';
            $status = 'error';
            $redirect = route('timbres.create');
        }
        //retornamos la respuesta en formato json y devolvemos a la vista del create
        return response()->json(['message' => $message, 'status' => $status, 'redirect' => $redirect]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
