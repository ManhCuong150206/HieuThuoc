<?php

namespace App\Http\Controllers;

use App\Support\MockData;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.admin.dashboard', MockData::dashboard());
    }
}
