<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Siembra los roles y permisos base. Asigna el rol "admin" a todos los
     * usuarios existentes para que el dueño del panel nunca quede bloqueado
     * fuera de la sección de Usuarios y roles tras instalar la capacidad.
     */
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'usuarios.gestionar', 'label' => 'Gestionar usuarios'],
            ['name' => 'roles.gestionar', 'label' => 'Gestionar roles'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                ['label' => $permission['label'], 'created_at' => $now, 'updated_at' => $now],
            );
        }

        $roles = [
            ['name' => 'admin', 'label' => 'Administrador', 'description' => 'Acceso total al panel y a la gestión de usuarios.'],
            ['name' => 'staff', 'label' => 'Personal', 'description' => 'Personal operativo con acceso al panel.'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                ['label' => $role['label'], 'description' => $role['description'], 'created_at' => $now, 'updated_at' => $now],
            );
        }

        $adminId = DB::table('roles')->where('name', 'admin')->value('id');
        $permissionIds = DB::table('permissions')->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->updateOrInsert(
                ['permission_id' => $permissionId, 'role_id' => $adminId],
                ['created_at' => $now, 'updated_at' => $now],
            );
        }

        foreach (DB::table('users')->pluck('id') as $userId) {
            DB::table('role_user')->updateOrInsert(
                ['role_id' => $adminId, 'user_id' => $userId],
                ['created_at' => $now, 'updated_at' => $now],
            );
        }
    }

    public function down(): void
    {
        $roleIds = DB::table('roles')->whereIn('name', ['admin', 'staff'])->pluck('id');
        DB::table('role_user')->whereIn('role_id', $roleIds)->delete();
        DB::table('permission_role')->whereIn('role_id', $roleIds)->delete();
        DB::table('roles')->whereIn('name', ['admin', 'staff'])->delete();
        DB::table('permissions')->whereIn('name', ['usuarios.gestionar', 'roles.gestionar'])->delete();
    }
};
