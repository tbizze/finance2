<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * ### CRIAR USUÁRIOS ########################
         * Além de criar o usuário, lhe atribui a função ADMIN.
        */
        $userAdmin = User::factory()->create([
            'name' => 'Tiago Bizze',
            'email' => 'admin@test',
            'email_verified_at' => now(),
            'password' => bcrypt('123'), 
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            //'current_team_id' => null,
        ]);
        //Associa o usuário criado ao Role/Função Admin.
        $userAdmin->assignRole('Admin');

        /**
         * ### CRIAR USUÁRIOS ########################
         * Além de criar o usuário, lhe atribui a função MEDIO.
        */
        $userMedio = User::factory()->create([
            'name' => 'User Um',
            'email' => 'user1@test',
            'email_verified_at' => now(),
            'password' => bcrypt('123'), 
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'current_team_id' => null,
        ]);
        //Associa o usuário criado ao Role/Função Medio.
        $userMedio->assignRole('Geral');

        /**
         * ### CRIAR USUÁRIOS ########################
         * Além de criar o usuário, lhe atribui a função BASICO.
        */
        $userBasico = User::factory()->create([
            'name' => 'User Dois',
            'email' => 'user2@test',
            'email_verified_at' => now(),
            'password' => bcrypt('123'), 
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'current_team_id' => null,
        ]);
        //Associa o usuário criado ao Role/Função Basico.
        $userBasico->assignRole('Basico');
    }
}
