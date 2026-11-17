<?php

namespace App\Http\Controllers;

use App\Support\MockData;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.account.index', MockData::account());
    }
}
