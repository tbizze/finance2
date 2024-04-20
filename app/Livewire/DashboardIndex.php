<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;

class DashboardIndex extends Component
{
    // Propriedades básicas da página.
    public $page_title = 'Dashboard';

    //Renderiza componente
    #[Title('Dashboard')]
    public function render()
    {
        return view('livewire.dashboard-index');
    }
}
