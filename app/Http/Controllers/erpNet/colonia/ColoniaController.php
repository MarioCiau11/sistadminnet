<?php

namespace App\Http\Controllers\erpNet\colonia;

use App\Http\Controllers\Controller;
use App\Models\CatalogosSAT\CAT_SAT_COLONIA;
use Illuminate\Http\Request;

class ColoniaController extends Controller
{

     public function buscarColonia(Request $request)
     {
          $cp = $request->cp;
          $search = $request->search;



          if (!isset($coloniaBusqueda)) {
               $coloniaBuscado = CAT_SAT_COLONIA::where('c_CodigoPostal', '=', $cp)->get()->toArray();
          }

          if (ctype_digit($search)) {
               $coloniaBuscado = CAT_SAT_COLONIA::WHERE('c_CodigoPostal', 'LIKE', "%" . $search . "%")->get()->toArray();
          } else {
               $coloniaBuscado = CAT_SAT_COLONIA::ORWHERE('asentamiento', 'LIKE', '%' . $search . '%')->get()->toArray();
          }

          return response()->json($coloniaBuscado);
     }
}
