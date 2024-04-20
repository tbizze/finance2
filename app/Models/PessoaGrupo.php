<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PessoaGrupo extends Model
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
        'nome','notas','ativo',
    ];

    /**
     * RELACIONAMENTO: Os PessoaGrupos 'pertencem a várias' Pessoas. 
     * Obtenha esses registros.
     */
    public function pessoas(): BelongsToMany
    {
        return $this->belongsToMany(Pessoa::class,'pessoas_grupos_pivot','pessoa_id','pessoa_grupo_id')
            //->whereNull('areas_eventos_pivot.deleted_at')
            //->withPivot(['deleted_at'])
            ->withTimestamps();
    }
}
