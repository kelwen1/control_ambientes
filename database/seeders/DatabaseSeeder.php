<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear un usuario administrador de ejemplo para desarrollo
        User::factory()->create([
            'id_cedula' => '1234567890',
            'nombre' => 'Administrador',
            'apellido' => 'Sistema',
            'correo' => 'admin@example.com',
            'telefono' => '3000000000',
            'user' => 'admin',
            'password' => Hash::make('Admin123@'),
            'id_rol' => 1,
        ]);
    }
}
