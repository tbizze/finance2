<div>
    {{-- Cabeçalho da página --}}
    <x-mary-header :title="$page_title" :subtitle="$page_subtitle" />

    {{-- Renderiza tabela --}}
    <x-mary-form wire:submit="save">
        <div class="flex gap-5 flex-wrap">
            @foreach ($permissions as $permission => $roles)
                <x-mary-card shadow class=" bg-white w-96">
                    <h3 class=" font-bold text-lg text-primary uppercase">{{ $permission }}</h3>
                    @foreach ($roles as $iten)
                        <x-mary-list-item :item="$iten" no-separator>
                            <x-slot:value>
                                {{ $iten->description }}
                            </x-slot:value>
                            <x-slot:sub-value>
                                {{ $iten->name }}
                            </x-slot:sub-value>
                            <x-slot:actions>
                                <x-mary-toggle :id="'permission_' . $iten->id" wire:model="role_permissions" :value="$iten->id" />
                            </x-slot:actions>
                        </x-mary-list-item>
                    @endforeach
                </x-mary-card>
            @endforeach
        </div>
        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.cancel()" />
            @can('setting.roles.edit')
                <x-mary-button label="Salvar" class="btn-primary" type="submit" spinner="save" />
            @endcan
        </x-slot:actions>
    </x-mary-form>
</div>
