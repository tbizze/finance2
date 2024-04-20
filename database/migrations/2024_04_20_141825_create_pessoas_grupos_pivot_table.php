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
        Schema::create('pessoas_grupos_pivot', function (Blueprint $table) {
            $table->id();
            
            // Chave estrangeira: PessoaGrupo.
            $table->foreignId('pessoa_grupo_id')->references('id')->on('pessoa_grupos');
            // Chave estrangeira: Pessoa.
            $table->foreignId('pessoa_id')->references('id')->on('pessoas');

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
        // Remove a chave estrangeira
        Schema::table('pessoas_grupos_pivot', function (Blueprint $table) {
            $table->dropForeign(['pessoa_grupo_id']);
            $table->dropForeign(['pessoa_id']);
        });
        
        Schema::dropIfExists('pessoas_grupos_pivot');
    }
};
