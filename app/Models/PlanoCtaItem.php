<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanoCtaItem extends Model
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
        'nome','notas','ativo','parent','codigo','cod_externo','natureza','lcto','exibir_dre','plano_cta_id'
    ];

    /**
     * RELACIONAMENTO: O PlanoCtaItem 'pertence a um' PlanoCta. 
     * Obtenha esse registro.
     */
    public function toPlanoCta(): BelongsTo
    {
        return $this->belongsTo(PlanoCta::class,'plano_cta_id');
    }
}
