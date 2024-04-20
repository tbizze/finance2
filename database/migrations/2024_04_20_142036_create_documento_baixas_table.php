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
        Schema::create('documento_baixas', function (Blueprint $table) {
            $table->id();
            $table->string('refer_baixa', 10)->nullable();
            $table->decimal('valor_baixa');
            $table->date('dt_baixa');
            $table->date('dt_compensa')->nullable();

            // Chave estrangeira: Documento.
            $table->foreignId('cta_movimento_id')->constrained('cta_movimentos');
            // Chave estrangeira: Documento.
            $table->foreignId('documento_id')->constrained('documentos');
            // Chave estrangeira: Documento.
            $table->foreignId('documento_baixa_tipo_id')->constrained('documento_baixa_tipos');

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
        Schema::table('documento_baixas', function (Blueprint $table) {
            $table->dropForeign(['cta_movimento_id']);
            $table->dropForeign(['documento_id']);
            $table->dropForeign(['documento_baixa_tipo_id']);
        });
        Schema::dropIfExists('documento_baixas');
    }
};
