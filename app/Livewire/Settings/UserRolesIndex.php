<?php

namespace App\Livewire\Settings;

use App\Livewire\Forms\Settings\UserRolesForm;
use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Role;

class UserRolesIndex extends Component
{
    use Toast;
    use WithPagination;

    // Propriedades básicas da página.
    public $page_title = 'Config: Funções de usuários';
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];
    public string $search = '';
    public $registro_id = '';

    // Propriedades para modals.
    public bool $modalRegistro = false;
    public bool $modalConfirmDelete = false;
    public bool $registroEditMode = false;

    // Instancia objeto Form para este componente.
    public UserRolesForm $form;


    //Renderiza componente
    #[Title('User-Funções')]
    public function render()
    {
        return view('livewire.settings.user-roles-index', [
            'headers' => $this->headers(),
            'users' => $this->users(),
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
            ['key' => 'email', 'label' => 'E-mail'],
            ['key' => 'roles_name', 'label' => 'Funções', 'sortBy' => 'roles_name'],
        ];
    }

    // Método p/ obter dados da tabela
    public function users()
    {
        return User::query()
            ->withAggregate('roles', 'name')
            ->when($this->search, function ($query, $val) {
                $query->where('name', 'like', '%' . $val . '%');
                $query->orWhere('email', 'like', '%' . $val . '%');
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
    public function edit(User $registro)
    {
        $this->form->setRegistro($registro);
        $this->registroEditMode = true;
        $this->modalRegistro = true;
    }

    // Método p/ salvar: STORE ou UPDATE.
    public function save()
    {
        // Ação: UPDATE.
        if ($this->registroEditMode) {
            //dd('save');
            $this->form->update();
            $this->registroEditMode = false;
            $this->success('Registro salvo com sucesso!');
            // Ação: STORE.
        } /* else {
            $this->form->store();
            $this->success('Registro incluído com sucesso!');
        } */
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
    public function delete(User $registro)
    {
        //User::find($id)->delete();
        $registro->delete();
        $this->modalConfirmDelete = false;
        $this->success('Registro excluído com sucesso!');
    }
}
