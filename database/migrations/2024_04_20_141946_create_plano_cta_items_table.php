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
        Schema::create('plano_cta_items', function (Blueprint $table) {
            $table->id();
            $table->integer('parent')->nullable();
            $table->string('codigo', 30)->index();
            $table->string('cod_externo', 10)->nullable();
            $table->string('nome', 40);
            $table->enum('natureza', ['C','D']);
            $table->boolean('lcto');
            $table->boolean('exibir_dre');
            $table->boolean('ativo')->default(true);
            $table->string('notas')->nullable();

            // Chave estrangeira: PlanoCta.
            $table->foreignId('plano_cta_id')->constrained('plano_ctas');

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
        Schema::table('plano_cta_items', function (Blueprint $table) {
            $table->dropForeign(['plano_cta_id']);
        });
        
        Schema::dropIfExists('plano_cta_items');
    }
};
