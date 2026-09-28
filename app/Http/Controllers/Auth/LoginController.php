<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SignInRequest;
use App\Support\SecurityAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    /* metodo */
    public function store(SignInRequest $request)
    {
        $data = $request->validated();
        $throttleKey = Str::lower($data['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withInput($request->only('email'))->with('error', "Demasiados intentos. Intente nuevamente en {$seconds} segundos.");
        }

        /* verificamos si el usuario ingreso el password correcto */
        if (! Auth::attempt($data + ['active' => true])) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withInput($request->only('email'))->with('error', 'Las credenciales proporcionadas no son correctas.');
        }

        if (Auth::user()->role_id && ! Auth::user()->role?->active) {
            Auth::logout();

            return back()->with('error', 'Su rol está deshabilitado. Contacte al administrador.');
        }

        $request->session()->regenerate();
        RateLimiter::clear($throttleKey);
        SecurityAudit::openAccess($request);

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        SecurityAudit::closeAccess($request, 'MANUAL');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
