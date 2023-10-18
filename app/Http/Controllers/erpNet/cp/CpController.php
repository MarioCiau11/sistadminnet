<?php

namespace App\Http\Controllers\erpNet\cp;

use App\Http\Controllers\Controller;
use App\Models\CatalogosSAT\CAT_SAT_CP;
use Illuminate\Http\Request;

class CpController extends Controller
{

    public function buscarCp(Request $request)
    {
        $search = $request->search;
        $cpBuscado = CAT_SAT_CP::where('c_CodigoPostal', 'LIKE', '%' . $search . '%')->get()->toArray();
        return response()->json($cpBuscado);
    }
}
