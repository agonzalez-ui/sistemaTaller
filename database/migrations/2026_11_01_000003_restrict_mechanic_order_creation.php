<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleId = DB::table('roles')->where('name', 'Mecánico')->value('id');
        $moduleId = DB::table('modules')->where('slug', 'orders')->value('id');

        if ($roleId && $moduleId) {
            DB::table('module_role')
                ->where('role_id', $roleId)
                ->where('module_id', $moduleId)
                ->update(['can_create' => false, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('name', 'Mecánico')->value('id');
        $moduleId = DB::table('modules')->where('slug', 'orders')->value('id');

        if ($roleId && $moduleId) {
            DB::table('module_role')
                ->where('role_id', $roleId)
                ->where('module_id', $moduleId)
                ->update(['can_create' => true, 'updated_at' => now()]);
        }
    }
};
