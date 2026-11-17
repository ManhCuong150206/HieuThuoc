<?php

namespace App\Http\Controllers;

use App\Support\MockData;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.checkout.index', MockData::checkout());
    }
}
