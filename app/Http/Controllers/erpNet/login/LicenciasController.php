<?php

namespace App\Http\Controllers\erpNet\login;

use App\Http\Controllers\Controller;
use App\Models\Licenses;
use App\Models\User;
use Illuminate\Http\Request;

class LicenciasController extends Controller
{


    public function index()
    {
        $licenses = Licenses::join('users', 'users.user_id', '=', 'licenses.license_UserID', 'left outer')
            ->select('licenses.license_ID as Identificador', 'licenses.*', 'users.*')
            ->get();


        return view('page.Licencias.index', compact('licenses'));
    }

    public function edit($id)
    {
        // dd($id);
        $license = Licenses::find($id);
        // dd($license);

        if ($license != null) {
            $license->delete();
        }
        return redirect()->route('licencias.index');
    }
}
