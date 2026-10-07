<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Docente Demo',
            'email' => 'demo@armonialab.test',
            'password' => 'password',
        ]);

        $user->courses()->create([
            'title' => 'Fundamentos de armonía',
            'description' => 'Curso introductorio sobre notas, intervalos, escalas y acordes.',
            'level' => 'Principiante',
            'status' => 'Publicado',
        ]);
    }
}
