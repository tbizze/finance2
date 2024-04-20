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
        Schema::create('pessoas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->index();
            $table->string('cpf_cnpj', 14)->unique();
            $table->string('rg_inscricao', 15)->nullable();
            $table->string('nome_razao', 150);
            $table->string('apelido_fantasia')->nullable();
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
        Schema::dropIfExists('pessoas');
    }
};
