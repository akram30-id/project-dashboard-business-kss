<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (Schema::hasTable('menus')) {
            // Tidak perlu registerPolicies() di sini
            Menu::all()->each(function ($menu) {
                Gate::define("read {$menu->name}", function ($user) use ($menu) {
                    return $user->can("read {$menu->name}");
                });
                // Tambahkan Gate lain jika perlu

                // Set timezone MySQL ke +07:00 (WIB / Asia/Jakarta)
                DB::statement("SET time_zone = '+07:00'");
            });
        }
    }
}
