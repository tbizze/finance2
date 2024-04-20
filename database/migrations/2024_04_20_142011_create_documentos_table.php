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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->date('data_emissao');
            $table->date('data_venc');
            $table->string('codigo', 30)->index();
            $table->bigInteger('condicao');
            $table->bigInteger('parcela');
            $table->string('notas')->nullable();

            // Chave estrangeira: Documento.
            $table->foreignId('documento_tipo_id')->constrained('documento_tipos');
            // Chave estrangeira: PlanoCtaItem.
            $table->foreignId('documento_classe_id')->constrained('documento_classes');
            // Chave estrangeira: PlanoCtaItem.
            $table->foreignId('documento_status_id')->constrained('documento_statuses');
            // Chave estrangeira: PlanoCtaItem.
            $table->foreignId('pessoa_id')->constrained('pessoas');

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
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['documento_tipo_id']);
            $table->dropForeign(['documento_classe_id']);
            $table->dropForeign(['documento_status_id']);
            $table->dropForeign(['pessoa_id']);
        });

        Schema::dropIfExists('documentos');
    }
};
