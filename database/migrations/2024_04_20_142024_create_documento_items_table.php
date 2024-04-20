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
        Schema::create('documento_items', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 130);
            $table->decimal('valor');
            $table->string('notas')->nullable();

            // Chave estrangeira: Documento.
            $table->foreignId('documento_id')->constrained('documentos');
            // Chave estrangeira: PlanoCtaItem.
            $table->foreignId('plano_cta_item_id')->constrained('plano_cta_items');

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
        Schema::table('documento_items', function (Blueprint $table) {
            $table->dropForeign(['documento_id']);
            $table->dropForeign(['plano_cta_item_id']);
        });
        Schema::dropIfExists('documento_items');
    }
};
