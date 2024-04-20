<?php

namespace App\Livewire\Forms\Settings;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class UserRolesForm extends Form
{
    public ?User $objetoForm;

    // Regras de validação.
    #[Validate([
        'user_has_roles' => ['array'],
    ])]

    // PROPRIEDADES: Campos da tabela.
    public $name;
    public $email;
    public $user_has_roles = [];

    // Método p/ popular classe 'objetoForm', a partir do BD.
    public function setRegistro(User $registro)
    {
        // Obtém as funções relacionadas com a permissão atual.
        // Método pluck('role_id') coloca a coluna definida num array.
        $roles = DB::table('model_has_roles')->where('model_id', $registro->id)->pluck('role_id');

        $this->objetoForm = $registro;
        $this->name = $registro->name;
        $this->email = $registro->email;
        $this->user_has_roles = $roles;
    }

    // Método p/ atualizar no BD.
    public function update()
    {
        $this->validate();
        
        // Atribui as funções passadas, ao usuário sendo editado.
        if ($this->user_has_roles) {
            // Está chegando array de strings. Converte em int.
            $user_roles_selected = $this->convertInt($this->user_has_roles);
            $this->objetoForm->roles()->sync($user_roles_selected);
        }
        $this->reset();
    }

    // Método p/ converter array de string em array de int. 
    private function convertInt($value)
    {
        return collect($value)->map(function (int $item) {
            return (int)$item;
        });
    }
}
