<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accurate_invoice_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('webhook_id', 200)->index('idx_webhook_id');
            $table->string('year', 4);
            $table->json('data', 1000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accurate_invoice_webhooks');
    }
};
