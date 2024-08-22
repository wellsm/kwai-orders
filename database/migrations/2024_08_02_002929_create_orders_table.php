<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained();
            $table->string('status');
            $table->string('name', 500);
            $table->unsignedBigInteger('product');
            $table->unsignedTinyInteger('commission');
            $table->decimal('price', 8, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('revenue', 8, 2);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
