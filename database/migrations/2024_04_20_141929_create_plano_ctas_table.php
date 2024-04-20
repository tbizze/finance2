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
        Schema::create('plano_ctas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 12);
            $table->string('nome', 30);
            $table->integer('niveis')->nullable();
            $table->string('mascara', 30)->nullable();
            $table->string('notas')->nullable();
            $table->boolean('ativo')->default(true);

            // Data de criação e de edição.
            $table->timestamps();
            // Recurso SoftDelete = excluir p/ lixeira.
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plano_ctas');
    }
};
