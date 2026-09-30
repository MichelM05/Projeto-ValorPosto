<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $row) {
            $row->id();
            $row->string('cnpj')->unique();
            $row->string('name');
            $row->string('brand')->nullable();
            $row->string('address');
            $row->string('neighborhood');
            $row->string('city');
            $row->decimal('latitude', 10, 8)->nullable();
            $row->decimal('longitude', 11, 8)->nullable();
            $row->timestamps();
        });

        Schema::create('fuel_prices', function (Blueprint $row) {
            $row->id();
            $row->foreignId('station_id')->constrained()->onDelete('cascade');
            $row->string('fuel_type');
            $row->decimal('price', 8, 3);
            $row->date('collected_at');
            $row->timestamps();

            $row->unique(['station_id', 'fuel_type', 'collected_at']);
        });

        Schema::create('imports', function (Blueprint $row) {
            $row->id();
            $row->string('source');
            $row->timestamp('imported_at');
            $row->integer('rows_read')->default(0);
            $row->integer('rows_inserted')->default(0);
            $row->integer('rows_ignored')->default(0);
            $row->json('errors')->nullable();
            $row->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imports');
        Schema::dropIfExists('fuel_prices');
        Schema::dropIfExists('stations');
    }
};
