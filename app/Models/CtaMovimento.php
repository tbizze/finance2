<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CtaMovimento extends Model
{
    use HasFactory;

    /**
     * Habilita o recurso de apagar para Lixeira.
     */
    use SoftDeletes;

    /**
     * Lista de campos em que é permitido a persistência no BD.
     */
    protected $fillable = [
        'nome','agencia','numero','descricao','cod_externo','notas','ativo'
    ];

    /**
     * RELACIONAMENTO: O CtaMovimento 'tem muitos' (hasMany) DocumentoBaixa. 
     * Obtenha essa coleção de registros.
     */
    public function hasDocumentoBaixas(): HasMany
    {
        return $this->hasMany(DocumentoBaixa::class);
    }
}
