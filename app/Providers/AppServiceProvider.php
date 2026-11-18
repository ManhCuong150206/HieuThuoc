<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.header', function (\Illuminate\View\View $view): void {
            $view->with('cartQuantity', app(CartService::class)->quantityFor(Auth::user()));
            $view->with('navigationCategories', Category::orderBy('id')->get());
        });
    }
}
