<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimrhSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('roles')->insertOrIgnore([
            ['id' => 1, 'name' => 'Administrador',  'is_administrator' => true, 'description' => 'Acceso total al sistema.',                    'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'is_administrator' => false, 'name' => 'Mecánico',       'description' => 'Opera ordenes de trabajo e inventario.',      'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'is_administrator' => false, 'name' => 'Recepción y ventas',  'description' => 'Gestiona clientes, motos, órdenes y facturas; consulta repuestos.',  'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'is_administrator' => false, 'name' => 'Auditoría',      'description' => 'Solo consulta operacion y reportes.',         'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $modules = [
            ['roles',        'Roles y permisos',        'ti ti-shield-lock'],
            ['users',     'Usuarios',                'ti ti-users'],
            ['admin_users',  'Administracion de usuarios', 'ti ti-user-cog'],
            ['customers',     'Clientes',                'ti ti-address-book'],
            ['vehicles',    'Vehiculos',               'ti ti-car'],
            ['orders',      'Ordenes de trabajo',      'ti ti-clipboard-list'],
            ['inventory',   'Inventario de repuestos', 'ti ti-package'],
            ['billing',  'Facturacion',             'ti ti-receipt'],
            ['reports',     'Reportes',                'ti ti-chart-bar'],
            ['about',    'Acerca de',               'ti ti-info-circle'],
            ['help',        'Ayuda',                   'ti ti-help'],
        ];

        foreach ($modules as $i => [$slug, $name, $icon]) {
            DB::table('modules')->insertOrIgnore([
                'id' => $i + 1, 'name' => $name, 'slug' => $slug, 'icon' => $icon,
                'sort_order' => ($i + 1) * 10, 'active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // Matriz de roles del entregable 4.
        // [ver, crear, editar, eliminar] por modulo, para cada rol.
        $matrix = [
            1 => ['roles' => [1, 1, 1, 1], 'users' => [1, 1, 1, 1], 'admin_users' => [1, 1, 1, 1], 'customers' => [1, 1, 1, 1], 'vehicles' => [1, 1, 1, 1], 'orders' => [1, 1, 1, 1], 'inventory' => [1, 1, 1, 1], 'billing' => [1, 1, 1, 1], 'reports' => [1, 0, 0, 0], 'about' => [1, 0, 0, 0], 'help' => [1, 0, 0, 0]],
            2 => ['customers' => [1, 1, 0, 0], 'vehicles' => [1, 1, 1, 0], 'orders' => [1, 1, 1, 0], 'inventory' => [1, 0, 1, 0], 'about' => [1, 0, 0, 0], 'help' => [1, 0, 0, 0]],
            3 => ['customers' => [1, 1, 1, 0], 'vehicles' => [1, 1, 1, 0], 'orders' => [1, 1, 1, 0], 'billing' => [1, 1, 1, 0], 'inventory' => [1, 0, 0, 0], 'about' => [1, 0, 0, 0], 'help' => [1, 0, 0, 0]],
            4 => ['customers' => [1, 0, 0, 0], 'vehicles' => [1, 0, 0, 0], 'orders' => [1, 0, 0, 0], 'inventory' => [1, 0, 0, 0], 'billing' => [1, 0, 0, 0], 'reports' => [1, 0, 0, 0], 'about' => [1, 0, 0, 0], 'help' => [1, 0, 0, 0]],
        ];

        $moduleIds = collect($modules)->mapWithKeys(fn ($m, $i) => [$m[0] => $i + 1]);

        foreach ($matrix as $roleId => $permissions) {
            foreach ($permissions as $slug => $p) {
                DB::table('module_role')->insertOrIgnore([
                    'module_id' => $moduleIds[$slug], 'role_id' => $roleId,
                    'can_view' => (bool) $p[0], 'can_create' => (bool) $p[1],
                    'can_edit' => (bool) $p[2], 'can_delete' => (bool) $p[3],
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        DB::table('order_statuses')->insertOrIgnore([
            ['name' => 'Recibido',           'color_class' => 'bg-est-recibido',    'sort_order' => 10, 'is_final' => false, 'allows_invoicing' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'En diagnostico',     'color_class' => 'bg-est-diagnostico', 'sort_order' => 20, 'is_final' => false, 'allows_invoicing' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'En reparacion',      'color_class' => 'bg-est-reparacion',  'sort_order' => 30, 'is_final' => false, 'allows_invoicing' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Esperando repuesto', 'color_class' => 'bg-est-espera',      'sort_order' => 40, 'is_final' => false, 'allows_invoicing' => false, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Listo',              'color_class' => 'bg-est-listo',       'sort_order' => 50, 'is_final' => false, 'allows_invoicing' => true,  'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Entregado',          'color_class' => 'bg-est-entregado',   'sort_order' => 60, 'is_final' => true,  'allows_invoicing' => true,  'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        foreach (['Honda', 'Suzuki', 'Yamaha', 'Kawasaki', 'Bajaj', 'TVS', 'Hero', 'KTM', 'Ducati', 'BMW', 'Harley-Davidson', 'Triumph', 'Royal Enfield', 'Benelli', 'CFMoto', 'Haojue', 'Vento', 'Freedom', 'Serpento', 'Italika'] as $m) {
            DB::table('vehicle_brands')->insertOrIgnore(['name' => $m, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        foreach (['Carro', 'Motocicleta', 'Pick-up'] as $t) {
            DB::table('vehicle_types')->insertOrIgnore(['name' => $t, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        foreach (['Generico', 'Bosch', 'Denso', 'NGK', 'Monroe', 'Gates'] as $m) {
            DB::table('spare_part_brands')->insertOrIgnore(['name' => $m, 'active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

    }
}
