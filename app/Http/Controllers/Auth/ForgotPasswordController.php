<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:150']]);
        $user = User::where('email', $data['email'])->where('active', true)->first();

        if ($user) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return back()->with('success', 'Si existe una cuenta activa con ese correo, recibirá un enlace para restablecer la contraseña.');
    }
}
