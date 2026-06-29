<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RolesController extends Controller
{
    /**
     * Roles del sistema que no pueden eliminarse ni renombrarse: son la base
     * sobre la que se construye el control de acceso del panel.
     */
    private const PROTECTED_ROLES = ['admin'];

    public function index(): Response
    {
        return Inertia::render('Roles/Index', [
            'usuarios' => User::with('roles:id,name,label')->orderBy('name')->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_ids' => $user->roles->pluck('id'),
                    'roles' => $user->roles->pluck('label'),
                    'is_admin' => $user->roles->contains('name', 'admin'),
                ]),
            'roles' => Role::with('permissions:id,name,label')->orderBy('label')->get()
                ->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'label' => $role->label,
                    'description' => $role->description,
                    'permission_ids' => $role->permissions->pluck('id'),
                    'permissions' => $role->permissions->pluck('label'),
                    'users_count' => $role->users()->count(),
                    'protected' => in_array($role->name, self::PROTECTED_ROLES, true),
                ]),
            'permisos' => Permission::orderBy('label')->get(['id', 'name', 'label']),
            'authUserId' => auth()->id(),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'temp_password' => session('temp_password'),
                'temp_email' => session('temp_email'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $this->uniqueSlug($data['label']),
            'label' => $data['label'],
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permission_ids'] ?? []);

        return back()->with('success', 'Rol creado correctamente.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        // El identificador (name) de los roles protegidos se mantiene estable;
        // solo se actualiza la etiqueta visible, la descripción y sus permisos.
        $role->update([
            'label' => $data['label'],
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permission_ids'] ?? []);

        return back()->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return back()->with('error', 'Este rol es del sistema y no se puede eliminar.');
        }

        $role->delete();

        return back()->with('success', 'Rol eliminado correctamente.');
    }

    private function uniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: 'rol';
        $slug = $base;
        $i = 2;

        while (Role::where('name', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
