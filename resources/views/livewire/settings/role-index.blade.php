<div>
    {{-- Cabeçalho da página --}}
    <x-mary-header :title="$page_title" subtitle="Últimos registros">
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live="search" />
        </x-slot:middle>
        <x-slot:actions>
            @can('setting.roles.create')
                <x-mary-button icon="o-plus" class="btn-primary" @click="$wire.showModalRegistro()" />
            @endcan

        </x-slot:actions>
    </x-mary-header>

    {{-- Renderiza tabela --}}
    <x-mary-card shadow class=" bg-white">
        <x-mary-table :headers="$headers" :rows="$roles" striped @row-click="$wire.edit($event.detail.id)"
            with-pagination :sort-by="$sortBy">
            @scope('actions', $roles)
                <div class="flex gap-1">
                    @can('setting.roles.edit')
                        <x-mary-button icon="o-shield-exclamation" :link="route('setting.role-permissions', $roles->id)" wire:navigate spinner
                            class="btn-sm btn-outline border-none p-1" />
                    @endcan
                    @can('setting.roles.delete')
                        <x-mary-button icon="o-trash" wire:click="confirmDelete({{ $roles->id }})" spinner
                            class="btn-sm btn-outline border-none text-error p-1" />
                    @endcan
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>


    {{-- MODAL: Criar/Editar --}}
    <x-mary-modal wire:model="modalRegistro" title="Criar/Editar registro" class="backdrop-blur">
        <x-mary-form wire:submit="save">
            <x-mary-input label="Nome" wire:model="form.name" />
            <x-mary-textarea label="Notas" wire:model="form.description" hint="Max. 150 caracteres" rows="3" />

            <x-slot:actions>
                <x-mary-button label="Cancel" @click="$wire.modalRegistro = false" />
                @can('setting.roles.edit')
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
