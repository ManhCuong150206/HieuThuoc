<?php

namespace App\Providers;

use App\Enums\AdminResource;
use App\Models\Category;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as BladeView;

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
        View::composer('layouts.admin', fn (BladeView $view) => $view->with('adminResources', AdminResource::cases()));

        View::composer('partials.header', function (BladeView $view): void {
            $view->with('cartQuantity', app(CartService::class)->quantityFor(Auth::user()));
            $view->with('navigationCategories', Category::orderBy('id')->get());
        });
    }
}
