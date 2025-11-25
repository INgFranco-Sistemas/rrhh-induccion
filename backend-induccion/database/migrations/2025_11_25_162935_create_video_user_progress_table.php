<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->integer('segundos_vistos')->default(0);
            $table->boolean('completado')->default(false);
            $table->timestamp('completado_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'video_id']); // un registro por usuario+video
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_user_progress');
    }

};
