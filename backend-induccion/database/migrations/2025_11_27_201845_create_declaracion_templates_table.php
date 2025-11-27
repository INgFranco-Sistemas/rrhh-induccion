<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('declaracion_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();   // nombre original del archivo
            $table->string('file_path');            // ruta en storage
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declaracion_templates');
    }
};