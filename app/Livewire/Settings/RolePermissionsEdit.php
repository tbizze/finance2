<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionsEdit extends Component
{
    use Toast;

    // Propriedades básicas da página.
    public $page_title = '';
    public $page_subtitle = '';
    public $role_permissions = [];
    public $registro_id = '';

    //Renderiza componente
    #[Title('Permissões-Funções')]
    public function render()
    {
        return view('livewire.settings.role-permissions-edit',[
            'permissions' => Permission::query()
                ->selectRaw('id,name,description,model')
                ->get()
                ->groupBy('model')
        ]);
    }

    public function mount(Role $role){
        $this->registro_id = $role->id;
        $this->page_title = 'Função: ' . $role->name;
        $this->page_subtitle = 'Atribuir permissões à função || ' . $role->description;

        // Obtém as permissões relacionadas com a função atual.
        $this->role_permissions = $role->permissions->pluck('id');
    }

    // Método p/ Cancelar.
    public function cancel()
    {
        $this->redirectRoute('setting.roles.index');
    }

    // Método p/ salvar: STORE ou UPDATE
    public function save()
    {
        // Atribui as funções passadas, à permissão criada.
        if ($this->role_permissions) {

            // Carrega modelo da Função presente.             
            $role = Role::find($this->registro_id);
            // Está chegando array de strings. Converte em int.
            $permission_selected = $this->convertInt($this->role_permissions);
            // Persiste no BD. Sincroniza permissões na função editada.
            $role->syncPermissions($permission_selected);

            //$this->redirectRoute('admin.roles');
            $this->success('Registro salvo com sucesso!', redirectTo: '/setting/roles');
        }
    }

    // Método p/ converter array de string em array de int. 
    private function convertInt($value)
    {
        return collect($value)->map(function (int $item) {
            return (int)$item;
        });
    }
}
