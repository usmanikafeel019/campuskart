<?php

namespace App\Providers;

use App\Models\Material;
use App\Policies\MaterialPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Gate::define('admin', fn ($u) => $u->role === 'admin');
        Gate::policy(Material::class, MaterialPolicy::class);
    }
}
