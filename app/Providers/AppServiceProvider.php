<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Menu;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Tidak perlu registerPolicies() di sini
        Menu::all()->each(function ($menu) {
            Gate::define("read {$menu->name}", function ($user) use ($menu) {
                return $user->can("read {$menu->name}");
            });
            // Tambahkan Gate lain jika perlu
        });
    }
}