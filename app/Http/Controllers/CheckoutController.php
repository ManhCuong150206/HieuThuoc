<?php

namespace App\Http\Controllers;

use App\Support\MockData;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View
    {
        return view('pages.checkout.index', MockData::checkout());
    }

    public function success(): View
    {
        return view('pages.checkout.success', ['order' => MockData::order()]);
    }
}
