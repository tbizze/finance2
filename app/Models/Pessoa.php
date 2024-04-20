<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pessoa extends Model
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
        'notas','ativo','codigo','cpf_cnpj','rg_inscricao','nome_razao','apelido_fantasia'
    ];

    /**
     * RELACIONAMENTO: O Documento 'tem muitos' (hasMany) Documento. 
     * Obtenha essa coleção de registros.
     */
    public function hasDocumentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * RELACIONAMENTO: Os Pessoa 'pertencem a várias' PessoaGrupos. 
     * Obtenha esses registros.
     */
    public function pessoaGrupos(): BelongsToMany
    {
        return $this->belongsToMany(PessoaGrupo::class,'pessoas_grupos_pivot','pessoa_id','pessoa_grupo_id')
            //->whereNull('areas_eventos_pivot.deleted_at')
            //->withPivot(['deleted_at'])
            ->withTimestamps();
    }
}
