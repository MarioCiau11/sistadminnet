<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\LoginController;
use App\Models\Licenses;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;

class SessionExpired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user()->user_id;
            $userLicense = Licenses::where('license_UserID', $user)->first();

            if ($userLicense != null) {
                $vidaLicencia = (int)env('SESSION_INTER_LIFETIME');
                $horaActual = Carbon::now();
                $ultimaActividad = Carbon::parse($userLicense->updated_at);
                $diferencia = $horaActual->diffInMinutes($ultimaActividad);

                if ($diferencia > $vidaLicencia) {
                    $loginController = new LoginController();
                    $loginController->logout($request);
                    // dd('La sesion ha expirado');
                    return redirect()->route('login')->withErrors(['license' => 'La licencia ha expirado']);
                }
                $userLicense->updated_at = Carbon::now();
                // dd($userLicense);
                $userLicense->save();
            } else {
                Auth::logout();
                return redirect(route('login'));
            }
        }
        return $next($request);
    }
}
