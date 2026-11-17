<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AuthController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.auth.login');
    }
}
