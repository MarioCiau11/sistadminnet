<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\Licenses;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PharIo\Manifest\License;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // public function showLoginForm()
    // {
    //     return view('auth.login');
    // }

    public function username()
    {
        return 'username';
    }

    public function logout(Request $request)
    {
        $licenciaVigente = Licenses::where('license_UserID', session('userSession')->user_id)->where('license_Licenses', session('userSession')->password)->where('license_Active', 1)->first();
        // dd($licenciaVigente);

        if ($licenciaVigente) {
            $licenciaVigente->delete();
        }

        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect('/');
    }

    protected function authenticated(Request $request, $user)
    {
        $company = CAT_COMPANIES::where('companies_key', '=', $request->empresa)->first();
        $sucursal = CAT_BRANCH_OFFICES::where('branchOffices_key', '=', $request->sucursal)->first();
        session(['company' => $company]);
        session(['sucursal' => $sucursal]);
        $generalParameters = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', $request->empresa)->first();
        session(['generalParameters' => $generalParameters]);
        $request->session()->regenerate();
        session(['userSession' => $user]);

        //buscamos todas las licencias
        $licencias = Licenses::all();

        $licencias = $licencias->count();

        //si licencias es mayor a o igual a 10
        if ($licencias >= 3) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['license' => 'No hay licencias disponibles']);
        }

        $licenciaVigente = Licenses::where('license_UserID', '=', $user->user_id)->where('license_Active', '=', 1)->first();

        if ($licenciaVigente != null) {
            $ultimaActualizacion = $licenciaVigente->updated_at;
            $ultimaActualizacion = Carbon::parse($ultimaActualizacion);
            $fechaActual = Carbon::now();
            $diferencia = $fechaActual->diffInDays($ultimaActualizacion);

            if ($diferencia >= 15) {
                Auth::logoutOtherDevices($request->password);
                $licenciaVigente->delete();
                $this->agregarLicencia($user);
            } else {
                Auth::logout();
                return redirect()->route('login')->withErrors(['license' => 'Esta licencia ya esta en uso. Por favor contacte a su administrador']);
            }
        } else {
            $this->agregarLicencia($user);
        }
    }

    public function agregarLicencia($user)
    {
        $licence = new Licenses();

        $licence->license_UserID = $user->user_id;
        $licence->license_Licenses = $user->password;
        $licence->created_at = Carbon::now();
        $licence->license_Active = 1;
        $licence->save();
    }
}
