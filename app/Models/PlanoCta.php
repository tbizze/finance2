<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanoCta extends Model
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
        'codigo','nome','notas','ativo','mascara','niveis'
    ];

    /**
     * RELACIONAMENTO: O PlanoCta 'tem muitos' (hasMany) PlanoCtaItem. 
     * Obtenha essa coleção de registros.
     */
    public function hasPlanoCtaItems(): HasMany
    {
        return $this->hasMany(PlanoCtaItem::class);
    }
}
