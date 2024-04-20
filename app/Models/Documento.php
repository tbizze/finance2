<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
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
        'data_emissao','data_venc','notas','codigo','condicao','parcela','documento_tipo_id','documento_classe_id','pessoa_id','documento_status_id'
    ];

    /**
     * Configura formatos p/ colunas.
    */
    protected $casts = [
        'data_emissao' => 'date:d/m/Y',
        'data_venc' => 'date:d/m/Y',
    ];

    /**
     * RELACIONAMENTO: O Documento 'pertence a um' DocumentoTipo. 
     * Obtenha esse registro.
     */
    public function toDocumentoTipo(): BelongsTo
    {
        return $this->belongsTo(DocumentoTipo::class,'documento_tipo_id');
    }

    /**
     * RELACIONAMENTO: O Documento 'pertence a um' DocumentoClasse. 
     * Obtenha esse registro.
     */
    public function toDocumentoClasse(): BelongsTo
    {
        return $this->belongsTo(DocumentoClasse::class,'documento_classe_id');
    }

    /**
     * RELACIONAMENTO: O Documento 'pertence a um' DocumentoStatus. 
     * Obtenha esse registro.
     */
    public function toDocumentoStatus(): BelongsTo
    {
        return $this->belongsTo(DocumentoStatus::class,'documento_status_id');
    }

    /**
     * RELACIONAMENTO: O Documento 'pertence a um' Pessoa. 
     * Obtenha esse registro.
     */
    public function toPessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class,'pessoa_id');
    }

    /**
     * RELACIONAMENTO: O Documento 'tem muitos' (hasMany) DocumentoItem. 
     * Obtenha essa coleção de registros.
     */
    public function hasDocumentoItems(): HasMany
    {
        return $this->hasMany(DocumentoItem::class);
    }
    
    /**
     * RELACIONAMENTO: O Documento 'tem muitos' (hasMany) DocumentoBaixa. 
     * Obtenha essa coleção de registros.
     */
    /* public function hasDocumentoBaixas(): HasMany
    {
        return $this->hasMany(DocumentoBaixa::class);
    } */

    /**
     * RELACIONAMENTO: O Documento 'tem um' (hasMany) DocumentoBaixa. 
     * Obtenha essa coleção de registros.
     */
    public function hasDocumentoBaixa(): HasOne
    {
        return $this->hasOne(DocumentoBaixa::class);
    }
}
