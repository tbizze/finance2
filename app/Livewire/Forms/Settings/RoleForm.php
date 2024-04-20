<?php

namespace App\Livewire\Forms\Settings;

use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class RoleForm extends Form
{
    public ?Role $objetoForm;

    // Regras de validação.
    #[Validate([
        'name' => ['required', 'string', 'min:3', 'max:50'],
        'description' => ['required', 'string', 'min:3', 'max:150'],
    ])]

    // PROPRIEDADES: Campos da tabela.
    public $name;
    public $description;

    // Método p/ popular classe 'objetoForm', a partir do BD.
    public function setRegistro(Role $registro)
    {
        $this->objetoForm = $registro;
        $this->name = $registro->name;
        $this->description = $registro->description;
        // Limpa as validações da memória, deixando o saco de erros vazio.
        $this->resetValidation();
    }

    // Método p/ criar no BD.
    public function store()
    {
        $this->validate();
        Role::create([
            'name' => $this->name,
            'description' => $this->description,
            'guard_name' => 'web',
        ]);
        $this->reset();
    }

    // Método p/ atualizar no BD.
    public function update()
    {
        $this->validate();
        $this->objetoForm->update(
            $this->all()
        );
        $this->reset();
    }
}
