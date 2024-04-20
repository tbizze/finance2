<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoBaixa extends Model
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
        'dt_baixa','valor_baixa','dt_compensa','refer_baixa','cta_movimento_id','documento_id','documento_baixa_tipo_id'
    ];

    /**
     * Configura formatos p/ colunas.
    */
    protected $casts = [
        'dt_baixa' => 'date:d/m/Y',
        'dt_compensa' => 'date:d/m/Y',
    ];

    /**
     * RELACIONAMENTO: O DocumentoBaixa 'pertence a um' CtaMovimento. 
     * Obtenha esse registro.
     */
    public function toCtaMovimento(): BelongsTo
    {
        return $this->belongsTo(CtaMovimento::class,'cta_movimento_id');
    }

    /**
     * RELACIONAMENTO: O DocumentoBaixa 'pertence a um' Documento. 
     * Obtenha esse registro.
     */
    public function toDocumento(): BelongsTo
    {
        return $this->belongsTo(Documento::class,'documento_id');
    }

    /**
     * RELACIONAMENTO: O DocumentoBaixa 'pertence a um' DocumentoBaixaTipo. 
     * Obtenha esse registro.
     */
    public function toDocumentoBaixaTipo(): BelongsTo
    {
        return $this->belongsTo(DocumentoBaixaTipo::class,'documento_baixa_tipo_id');
    }
}
