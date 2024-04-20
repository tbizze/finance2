<?php

namespace App\Livewire\Settings;

use App\Livewire\Forms\Settings\PermissionForm;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionIndex extends Component
{
    use Toast;
    use WithPagination;

    // Propriedades básicas da página.
    public $page_title = 'Config: Permissões';
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];
    public string $search = '';
    public $registro_id = '';

    // Propriedades para modals.
    public bool $modalRegistro = false;
    public bool $modalConfirmDelete = false;
    public bool $registroEditMode = false;
    public string $modalTitle = 'Criar/Editar registro';

    // Instancia objeto Form para este componente.
    public PermissionForm $form;


    //Renderiza componente
    #[Title('Permissões')]
    public function render()
    {
        return view('livewire.settings.permission-index',[
            'headers' => $this->headers(),
            'permissions' => $this->permissions(),
            'roles' => Role::get(['id','name']),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS BÁSICOS
    |--------------------------------------------------------------------------
    | Cabeçalho da tabela / Dados da tabela...
    */

    // Método p/ Cabeçalho da tabela
    public function headers()
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'bg-base-200 w-1'],
            ['key' => 'name', 'label' => 'Nome'],
            ['key' => 'description', 'label' => 'Descrição'],
            ['key' => 'model', 'label' => 'Modelo'],
            ['key' => 'roles_name', 'label' => 'Grupo', 'sortable' => false],
        ];
    }

    // Método p/ obter dados da tabela
    public function permissions()
    {
        return Permission::query()
            ->when($this->search, function ($query, $val) {
                $query->where('name', 'like', '%' . $val . '%');
                $query->orWhere('description', 'like', '%' . $val . '%');
                $query->orWhere('model', 'like', '%' . $val . '%');
                return $query;
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS DE AÇÕES
    |--------------------------------------------------------------------------
    | Ações diversas no componente.
    */

    // Método p/ habilitar modal Edit/Create.
    public function showModalRegistro()
    {
        $this->form->reset();
        $this->modalRegistro = true;
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS COM REGISTROS
    |--------------------------------------------------------------------------
    | Ações de salvar, chamar o delete, confirmar o delete
    */

    // Método p/ carregar inputs do form e exibir modal.
    public function edit(Permission $registro)
    {
        $this->form->setRegistro($registro);
        $this->registroEditMode = true;
        $this->modalTitle = 'Criar/Editar registro';
        $this->modalRegistro = true;
    }

    // Método p/ salvar: STORE ou UPDATE.
    public function save()
    {
        // Ação: UPDATE.
        if ($this->registroEditMode) {
            $this->form->update();
            $this->registroEditMode = false;
            $this->success('Registro salvo com sucesso!');
        // Ação: STORE.
        } else {
            $this->form->store();
            $this->success('Registro incluído com sucesso!');
        }
        // Oculta modal.
        $this->modalRegistro = false;
    }

    // Método p/ confirmar delete. Abre modal para confirmação.
    public function confirmDelete($id)
    {
        $this->registro_id = $id;
        $this->modalConfirmDelete = true;
    }

    // Método para deletar.
    public function delete($id)
    {
        Permission::find($id)->delete();
        $this->modalConfirmDelete = false;
        $this->success('Registro excluído com sucesso!');
    }
    
    // Método p/ copiar registro.
    // Carregar inputs, sem carregar modelo, assim salvar c/ STORE().
    // Por isso registroEditMode = false.
    public function copyRecord(Permission $registro)
    {
        $this->form->setRegistro($registro);
        $this->registroEditMode = false;
        $this->modalTitle = 'Copiando registro';
        $this->modalRegistro = true;
    }
}
