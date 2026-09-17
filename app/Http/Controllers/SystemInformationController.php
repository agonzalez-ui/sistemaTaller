<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SystemInformationController extends Controller
{
    public function about(): View
    {
        return view('system.about', ['information' => config('simrh')]);
    }

    public function help(): View
    {
        return view('system.help');
    }
}
