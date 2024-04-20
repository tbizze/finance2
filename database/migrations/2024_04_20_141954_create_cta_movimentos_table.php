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
        Schema::create('cta_movimentos', function (Blueprint $table) {
            $table->id();
            $table->string('agencia', 12)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('nome', 30);
            $table->string('descricao', 100);
            $table->string('cod_externo', 10)->nullable();
            $table->boolean('ativo')->default(true);
            $table->string('notas')->nullable();

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
        Schema::dropIfExists('cta_movimentos');
    }
};
