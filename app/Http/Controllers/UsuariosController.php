<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UsuariosController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'role_ids' => ['array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        // Generamos una contraseña temporal y la devolvemos al administrador
        // para que la comparta. Así la invitación funciona aunque el envío de
        // correos no esté configurado (degradación elegante, sin errores 500).
        $tempPassword = Str::password(12);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $tempPassword, // el cast 'hashed' del modelo la cifra
        ]);

        $user->roles()->sync($data['role_ids'] ?? []);

        return back()
            ->with('success', 'Usuario creado correctamente.')
            ->with('temp_password', $tempPassword)
            ->with('temp_email', $user->email);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user->id)],
            'role_ids' => ['array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        if (! $this->guardLastAdmin($user, $data['role_ids'] ?? [])) {
            return back()->with('error', 'Debe existir al menos un administrador activo.');
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $user->roles()->sync($data['role_ids'] ?? []);

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        if (! $this->guardLastAdmin($user, [])) {
            return back()->with('error', 'Debe existir al menos un administrador activo.');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * Evita que el último administrador pierda su rol o sea eliminado, lo que
     * dejaría al panel sin acceso a la gestión de usuarios.
     *
     * @param  array<int>  $newRoleIds  Roles que tendrá el usuario tras el cambio.
     */
    private function guardLastAdmin(User $user, array $newRoleIds): bool
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (! $adminRole) {
            return true;
        }

        $isCurrentlyAdmin = $user->roles()->where('name', 'admin')->exists();
        $willStayAdmin = in_array($adminRole->id, array_map('intval', $newRoleIds), true);

        if ($isCurrentlyAdmin && ! $willStayAdmin) {
            $otherAdmins = $adminRole->users()->where('users.id', '!=', $user->id)->count();

            return $otherAdmins > 0;
        }

        return true;
    }
}
