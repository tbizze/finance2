<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoBaixaTipo extends Model
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
        'nome','notas','natureza','ativo',
    ];

    /**
     * RELACIONAMENTO: O DocumentoBaixaTipo 'tem muitos' (hasMany) DocumentoBaixas. 
     * Obtenha essa coleção de registros.
     */
    public function hasDocumentoBaixas(): HasMany
    {
        return $this->hasMany(DocumentoBaixa::class);
    }
}
