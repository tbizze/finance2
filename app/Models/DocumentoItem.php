<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoItem extends Model
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
        'descricao','notas','valor','documento_id','plano_cta_item_id'
    ];

    /**
     * RELACIONAMENTO: O DocumentoItem 'pertence a um' Documento. 
     * Obtenha esse registro.
     */
    public function toDocumento(): BelongsTo
    {
        return $this->belongsTo(Documento::class,'documento_id');
    }

    /**
     * RELACIONAMENTO: O DocumentoItem 'pertence a um' EventoLocal. 
     * Obtenha esse registro.
     */
    public function toPlanoCtaItem(): BelongsTo
    {
        return $this->belongsTo(PlanoCtaItem::class,'plano_cta_item_id');
    }
}
