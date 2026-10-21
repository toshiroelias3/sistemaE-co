<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Size;
use App\Models\Color;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InitialStoreSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Usuario Administrador de Prueba
        // 'role' no es asignable masivamente, por eso se usa forceFill
        User::firstOrNew(['email' => 'admin@tezan.com'])->forceFill([
            'name' => 'Admin Tezan',
            'password' => Hash::make('admin1234'), // Contraseña segura temporal
            'role' => 'admin',
        ])->save();

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
            ['name' => 'Off-White', 'hex' => '#F8F9FA']
        ];
        foreach ($colors as $color) {
            Color::firstOrCreate(['name' => $color['name']], ['hex_code' => $color['hex']]);
        }

        // 4. Categorías Base
        foreach (['Polos', 'Colecciones'] as $name) {
            Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }
}
