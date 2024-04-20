<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoClasse extends Model
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
        'nome','notas','order','ativo',
    ];

    /**
     * RELACIONAMENTO: O DocumentoClasse 'tem muitos' (hasMany) Documentos. 
     * Obtenha essa coleção de registros.
     */
    public function hasDocumentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}
