<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Http\Controllers\Controller;
use App\Models\historicos\FAVORITE_REPORTS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteriaController extends Controller
{
    public function indexCompras()
    {
        $favorites = $this->getIndexData('Compras');

        return view('page.Reportes.indexCompras', compact('favorites'));
    }

    public function indexGastos()
    {
        $favorites = $this->getIndexData('Gastos');
        return view('page.Reportes.indexGastos', compact('favorites'));
    }

    public function indexCxP()
    {
        $favorites = $this->getIndexData('CxP');
        return view('page.Reportes.indexCxP', compact('favorites'));
    }

    public function indexTesoreria()
    {
        $favorites = $this->getIndexData('Tesoreria');
        return view('page.Reportes.indexTesoreria', compact('favorites'));
    }
    
    public function indexVentas()
    {
        $favorites = $this->getIndexData('Ventas');
        return view('page.Reportes.indexVentas', compact('favorites'));
    }

    public function indexCxC()
    {
        $favorites = $this->getIndexData('CxC');
        return view('page.Reportes.indexCxC', compact('favorites'));
    }
    public function indexInventarios()
    {
        $favorites = $this->getIndexData('Inventarios');
        return view('page.Reportes.indexInventarios', compact('favorites'));
    }

    public function indexGerenciales()
    {
        $favorites = $this->getIndexData('Gerenciales');
        return view('page.Reportes.indexGerenciales', compact('favorites'));
    }


    public function getIndexData($category)
    {
        $user = Auth::user();
        $favorites = FAVORITE_REPORTS::where('user_id', $user->user_id)
            ->where('report_category', '=', $category)
            ->get();

        return $favorites;
    }

    public function addFavorite(Request $request)
    {
        $user = Auth::user();
        // dd($user);
        $reportId = $request->input('reportId');
        $reportName = $request->input('reportName');
        $reportIdentifier = $request->input('reportIdentifier');
        $reportCategory = $request->input('reportCategory');

        $favorite = new FAVORITE_REPORTS();
        $favorite->user_id = $user->user_id;
        $favorite->report_key = $reportId;
        $favorite->report_name = $reportName;
        $favorite->report_identifier = $reportIdentifier;
        $favorite->report_category = $reportCategory;
        $favorite->save();

        return response()->json(['success' => true]);
    }

    // Eliminar un favorito
    public function removeFavorite(Request $request)
    {
        $user = Auth::user();
        $reportIdentifier = $request->input('reportIdentifier');

        $favorite = FAVORITE_REPORTS::where('user_id', $user->user_id)
            ->where('report_identifier', $reportIdentifier)
            ->first();
            // dd($favorite);

        if ($favorite) {
            $favorite->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);

    }
    public function checkFavorite(Request $request)
    {
        $user = Auth::user();
        $reportIdentifier = $request->input('reportIdentifier');

        $favorite = FAVORITE_REPORTS::where('user_id', $user->user_id)
            ->where('report_identifier', $reportIdentifier)
            ->first();

        return response()->json(['favorite' => !is_null($favorite)]);
    }
}
