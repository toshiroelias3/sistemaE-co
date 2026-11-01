<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Size;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InitialStoreSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el administrador solo si no existe: nunca se pisa una clave ya cambiada
        $admin = User::firstOrNew(['email' => 'admin@tezan.com']);
        if (! $admin->exists) {
            $password = config('tienda.admin_password') ?: Str::password(20);

            // 'role' no es asignable masivamente, por eso se usa forceFill
            $admin->forceFill([
                'name' => 'Admin Tezan',
                'password' => Hash::make($password),
                'role' => 'admin',
            ])->save();

            $this->command?->warn("Admin creado: admin@tezan.com / {$password}");
        }

        // 2. Insertar Tallas Base
        $sizes = ['S', 'M', 'L', 'XL'];
        foreach ($sizes as $size) {
            Size::firstOrCreate(['name' => $size]);
        }

        // 3. Insertar Colores Premium Minimalistas
        $colors = [
            ['name' => 'Beige', 'hex' => '#F5F5DC'],
            ['name' => 'Navy Blue', 'hex' => '#000080'],
            ['name' => 'Military Green', 'hex' => '#4B5320'],
            ['name' => 'Black', 'hex' => '#000000'],
            ['name' => 'Off-White', 'hex' => '#F8F9FA'],
        ];
        foreach ($colors as $color) {
            Color::firstOrCreate(['name' => $color['name']], ['hex_code' => $color['hex']]);
        }

        // 4. Categorías del catálogo (secciones Varones / Mujeres)
        $this->call(CatalogCategoriesSeeder::class);
    }
}
