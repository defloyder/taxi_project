<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('driver_id')->nullable()->constrained('users');
            $table->json('start_location');
            $table->json('end_location');
            $table->enum('status', ['pending', 'accepted', 'in_progress', 'completed', 'cancelled']);
            $table->decimal('price', 10, 2)->nullable();
            $table->enum('car_class', ['economy', 'comfort', 'business']);
            $table->enum('car_type', ['sedan', 'minivan', 'suv']);
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('waiting_time')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
