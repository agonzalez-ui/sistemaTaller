<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SignInRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index(){
        return view('auth.login');
    }

    /* metodo */
    public function store(SignInRequest $request){
        $data = $request->validated();

        /* verificamos si el usuario ingreso el password correcto */
        if(!Auth::attempt($data)){
            return back()->with('error', 'Credenciales Incorrectas.');
        }

        return redirect()->route('dashboard');
    }
}
