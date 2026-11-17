<?php

namespace App\Http\Controllers;

use App\Support\MockData;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.home', MockData::home());
    }
}
