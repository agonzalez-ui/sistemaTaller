<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller {
    public function index() {
        return view( 'auth.register' );
    }

    public function store( SignupRequest $request ) {

        $data = $request->validated();

        //almacena en la base de datos
        $user = User::create( $data );

        /* como enviar el email */
        event( new Registered( $user ) );

        Auth::login( $user );

        /* redireccionar a la pagina */
        return redirect()->route('verification.notice');
    }
}
