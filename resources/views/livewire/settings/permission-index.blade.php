<div>
    {{-- Cabeçalho da página --}}
    <x-mary-header :title="$page_title" subtitle="Últimos registros">
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live="search" />
        </x-slot:middle>
        <x-slot:actions>
            @can('setting.permissions.create')
                <x-mary-button icon="o-plus" class="btn-primary" @click="$wire.showModalRegistro()" />
            @endcan

        </x-slot:actions>
    </x-mary-header>

    {{-- Renderiza tabela --}}
    <x-mary-card shadow class=" bg-white">
        <x-mary-table :headers="$headers" :rows="$permissions" striped @row-click="$wire.edit($event.detail.id)"
            with-pagination :sort-by="$sortBy">
            @scope('cell_roles_name', $permission)
                <div class=" flex gap-1">
                    @foreach ($permission->roles as $role)
                        <x-mary-badge :value="$role->name" class="badge-outline " />
                    @endforeach
                </div>
            @endscope
            @scope('actions', $permission)
            <div class="flex gap-1">
                @can('setting.permissions.edit')
                <x-mary-button icon="o-document-duplicate" wire:click="copyRecord({{ $permission->id }})" spinner
                    class="btn-sm btn-outline border-none p-1" />
                @endcan
                @can('setting.permissions.delete')
                    <x-mary-button icon="o-trash" wire:click="confirmDelete({{ $permission->id }})" spinner
                        class="btn-sm btn-outline border-none text-error p-1" />
                @endcan
            </div>
            @endscope
        </x-mary-table>
    </x-mary-card>


    {{-- MODAL: Criar/Editar --}}
    <x-mary-modal wire:model="modalRegistro" :title="$modalTitle" class="backdrop-blur">
        <x-mary-form wire:submit="save">
            <x-mary-input label="Nome" wire:model="form.name" />
            <x-mary-input label="Model" wire:model="form.model" />
            <x-mary-textarea label="Notas" wire:model="form.description" hint="Max. 250 caracteres" rows="3" />
            <hr />
            <div class="font-semibold">Funções</div>
            <div class="flex flex-wrap gap-2">
            @foreach ($roles as $role)
                <x-mary-checkbox :id="'role_' . $role->id" :label="$role->name" wire:model="form.permission_to_roles"
                    :value="$role->id" />
            @endforeach
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" @click="$wire.modalRegistro = false" />
                @can('setting.permissions.edit')
                    <x-mary-button label="Salvar" class="btn-primary" type="submit" spinner="save" />
                @endcan
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>

    {{-- MODAL: Confirma delete --}}
    <x-mary-modal wire:model="modalConfirmDelete" title="Deletar registro" class="backdrop-blur">
        <div class="mb-5">Deseja realmente excluir o <span class=" font-bold">registro nº
                [{{ $registro_id }}]</span>?</div>
        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.modalConfirmDelete = false" />
            <x-mary-button label="Excluir" wire:click="delete({{ $registro_id }})" class="btn-error" spinner="save" />
        </x-slot:actions>
    </x-mary-modal>
</div>
