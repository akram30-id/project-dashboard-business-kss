<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accurate_invoice_webhooks', function (Blueprint $table) {
            $table->index('year', 'accurate_invoice_webhooks_year_index');
        });
    }

    public function down(): void
    {
        Schema::table('accurate_invoice_webhooks', function (Blueprint $table) {
            $table->dropIndex('accurate_invoice_webhooks_year_index');
        });
    }
};
