<?php

use App\Http\Controllers\DreController;
use App\Http\Controllers\HomeConfereController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomeTestController;
use App\Http\Controllers\JobcardController;
use App\Livewire\DashboardIndex;
use App\Livewire\Settings\PermissionIndex;
use App\Livewire\Settings\RoleIndex;
use App\Livewire\Settings\RolePermissionsEdit;
use App\Livewire\Settings\UserRolesIndex;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    /* Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard'); */

    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    Route::get('/setting/roles', RoleIndex::class)->name('setting.roles.index');
    Route::get('/setting/permissions', PermissionIndex::class)->name('setting.permissions.index');
    Route::get('/setting/role-permissions/{role}/edit', RolePermissionsEdit::class)->name('setting.role-permissions');
    Route::get('/setting/user-has-roles', UserRolesIndex::class)->name('setting.user-roles.index');
});


// #######   ROTAS DE TESTES
Route::get('/test1', [HomeController::class, 'test1']);
Route::get('/test2', [HomeController::class, 'test2']);

Route::get('/test-date', [HomeController::class, 'testDate']);

// TEST: A PAGAR/RECEBER ==> 1-a_pagar e 2-a_receber
Route::get('/prev/{tipo_id}/', [HomeController::class, 'previsao'])->name('previsao');
// TEST: PAGO/RECEBIDO ==> 3-pago / 4-recebido / 5-tarifa / 6-movimento
Route::get('/real/{tipo_id}/', [HomeController::class, 'real'])->name('real');
// TEST: ITENS DE DOCS
Route::get('/doc-item/{tipo_id}/', [HomeController::class, 'docItem'])->name('doc.item');


// TEST: DRE - docs pagos por categoria contábil / separar por cta. ==> 1-especie 2-santander 3-itau
Route::get('/dre-item/{cta_id}/', [HomeController::class, 'dreItems'])->name('dre.items');
Route::get('/dre-resumo', [HomeController::class, 'dreResumo'])->name('dre.resumo');
Route::get('/dre-resumo2', [HomeController::class, 'dreResumo2'])->name('dre.resumo');
Route::get('/dre-resumo3', [HomeController::class, 'dreResumo3'])->name('dre.resumo');

Route::get('/dre-1', [DreController::class, 'dreAnual']);
Route::get('/dre-2', [DreController::class, 'anual'])->name('dre.anual2');
Route::get('/dre-3', [DreController::class, 'anualSum'])->name('dre.anual.sum');

Route::get('/dre-resumo-mensal', [HomeTestController::class, 'dreResumoMensal'])->name('dre.resumo-mensal');
Route::get('/dre-resumo-anual', [HomeTestController::class, 'dreResumoAnual'])->name('dre.resumo-anual');

// CONFERÊNCIAS
Route::get('/conf-baixas', [HomeTestController::class, 'confBaixas']);
Route::get('/conf-docs', [HomeTestController::class, 'confDocs']);
Route::get('/conf-all', [HomeConfereController::class, 'confAll'])->name('conf.all');


Route::get('/doc/{tipo_id}/{classe_id?}', [HomeController::class, 'doc'])->name('doc');

Route::get('/home', [HomeController::class, 'home'])->name('home');
Route::get('/jobcards', [JobcardController::class, 'index'])->name('jobcards');




//Route::get('/doc-item', [HomeController::class, 'docItem'])->name('doc.item');

//Route::get('/cria-doc', [HomeController::class, 'criaDoc']);
//Route::get('/cria-doc-item', [HomeController::class, 'criaDocItem']);
//Route::get('/cria-doc-baixa', [HomeController::class, 'criaDocBaixa']);