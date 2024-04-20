<?php

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
