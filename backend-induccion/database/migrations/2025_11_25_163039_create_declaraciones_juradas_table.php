<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('declaraciones_juradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('texto_declaracion'); // declaracion jurada que acepta
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('firmado_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declaraciones_juradas');
    }
};
