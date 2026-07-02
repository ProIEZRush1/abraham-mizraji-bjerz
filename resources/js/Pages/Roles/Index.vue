<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import UserFormModal from './Partials/UserFormModal.vue';
import RoleFormModal from './Partials/RoleFormModal.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    usuarios: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    permisos: { type: Array, default: () => [] },
    authUserId: { type: Number, default: null },
    flash: { type: Object, default: () => ({}) },
});

const tab = ref('usuarios');

const flashSuccess = computed(() => props.flash?.success ?? null);
const flashError = computed(() => props.flash?.error ?? null);
const tempPassword = computed(() => props.flash?.temp_password ?? null);
const tempEmail = computed(() => props.flash?.temp_email ?? null);

const userModal = ref({ show: false, user: null });
const roleModal = ref({ show: false, role: null });
const confirm = ref({ show: false, kind: null, item: null });

const openInviteUser = () => (userModal.value = { show: true, user: null });
const openEditUser = (user) => (userModal.value = { show: true, user });
const openNewRole = () => (roleModal.value = { show: true, role: null });
const openEditRole = (role) => (roleModal.value = { show: true, role });

const askDelete = (kind, item) => (confirm.value = { show: true, kind, item });
const closeConfirm = () => (confirm.value = { show: false, kind: null, item: null });

const confirmDelete = () => {
    const { kind, item } = confirm.value;
    const url =
        kind === 'user'
            ? route('roles.users.destroy', item.id)
            : route('roles.destroy', item.id);
    router.delete(url, { preserveScroll: true, onFinish: closeConfirm });
};

const copyTempPassword = () => {
    if (tempPassword.value && navigator.clipboard) {
        navigator.clipboard.writeText(tempPassword.value);
    }
};
</script>

<template>
    <Head title="Usuarios y roles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">
                Usuarios y roles
            </h2>
        </template>

        <div class="mx-auto max-w-6xl space-y-6">
            <!-- Flash: contraseña temporal -->
            <div
                v-if="tempPassword"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5"
            >
                <p class="text-sm font-semibold text-emerald-800">
                    Usuario creado. Comparte estos accesos de forma segura:
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                    <span class="text-emerald-700">{{ tempEmail }}</span>
                    <code class="rounded-lg bg-white px-3 py-1 font-mono text-emerald-900 shadow-sm">
                        {{ tempPassword }}
                    </code>
                    <button
                        type="button"
                        @click="copyTempPassword"
                        class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-semibold text-white transition hover:bg-emerald-500"
                    >
                        Copiar
                    </button>
                </div>
            </div>

            <div
                v-else-if="flashSuccess"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-medium text-emerald-800"
            >
                {{ flashSuccess }}
            </div>

            <div
                v-if="flashError"
                class="rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-medium text-red-700"
            >
                {{ flashError }}
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 rounded-xl bg-slate-100 p-1">
                <button
                    type="button"
                    @click="tab = 'usuarios'"
                    :class="[
                        'flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition',
                        tab === 'usuarios'
                            ? 'bg-white text-slate-900 shadow'
                            : 'text-slate-500 hover:text-slate-700',
                    ]"
                >
                    Usuarios
                </button>
                <button
                    type="button"
                    @click="tab = 'roles'"
                    :class="[
                        'flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition',
                        tab === 'roles'
                            ? 'bg-white text-slate-900 shadow'
                            : 'text-slate-500 hover:text-slate-700',
                    ]"
                >
                    Roles
                </button>
            </div>

            <!-- Usuarios -->
            <section
                v-show="tab === 'usuarios'"
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Equipo</h3>
                        <p class="text-sm text-slate-500">
                            Invita a tu personal y asigna sus roles.
                        </p>
                    </div>
                    <PrimaryButton @click="openInviteUser">Invitar usuario</PrimaryButton>
                </div>

                <div class="divide-y divide-slate-100">
                    <div
                        v-for="user in usuarios"
                        :key="user.id"
                        class="flex flex-wrap items-center justify-between gap-4 px-6 py-4"
                    >
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800">
                                {{ user.name }}
                                <span
                                    v-if="user.id === authUserId"
                                    class="ml-1 text-xs font-medium text-slate-400"
                                    >(tú)</span
                                >
                            </p>
                            <p class="truncate text-sm text-slate-500">{{ user.email }}</p>
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                <span
                                    v-for="label in user.roles"
                                    :key="label"
                                    class="rounded-full bg-gradient-to-r from-[#92400e] to-[#d97706] px-2.5 py-0.5 text-xs font-medium text-white"
                                >
                                    {{ label }}
                                </span>
                                <span
                                    v-if="!user.roles.length"
                                    class="text-xs text-slate-400"
                                    >Sin roles</span
                                >
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <SecondaryButton @click="openEditUser(user)">Editar</SecondaryButton>
                            <DangerButton
                                v-if="user.id !== authUserId"
                                @click="askDelete('user', user)"
                                >Eliminar</DangerButton
                            >
                        </div>
                    </div>
                </div>
            </section>

            <!-- Roles -->
            <section
                v-show="tab === 'roles'"
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Roles</h3>
                        <p class="text-sm text-slate-500">
                            Define qué puede hacer cada tipo de usuario.
                        </p>
                    </div>
                    <PrimaryButton @click="openNewRole">Nuevo rol</PrimaryButton>
                </div>

                <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
                    <div
                        v-for="role in roles"
                        :key="role.id"
                        class="flex flex-col rounded-2xl border border-slate-200 p-5"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-800">{{ role.label }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ role.users_count }}
                                    {{ role.users_count === 1 ? 'usuario' : 'usuarios' }}
                                </p>
                            </div>
                            <span
                                v-if="role.protected"
                                class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500"
                                >Sistema</span
                            >
                        </div>
                        <p v-if="role.description" class="mt-2 text-sm text-slate-500">
                            {{ role.description }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span
                                v-for="label in role.permissions"
                                :key="label"
                                class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700"
                            >
                                {{ label }}
                            </span>
                            <span
                                v-if="!role.permissions.length"
                                class="text-xs text-slate-400"
                                >Sin permisos</span
                            >
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <SecondaryButton @click="openEditRole(role)">Editar</SecondaryButton>
                            <DangerButton
                                v-if="!role.protected"
                                @click="askDelete('role', role)"
                                >Eliminar</DangerButton
                            >
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <UserFormModal
            :show="userModal.show"
            :user="userModal.user"
            :roles="roles"
            @close="userModal.show = false"
        />

        <RoleFormModal
            :show="roleModal.show"
            :role="roleModal.role"
            :permissions="permisos"
            @close="roleModal.show = false"
        />

        <Modal :show="confirm.show" max-width="md" @close="closeConfirm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-800">Confirmar eliminación</h2>
                <p class="mt-2 text-sm text-slate-500">
                    {{
                        confirm.kind === 'user'
                            ? '¿Seguro que deseas eliminar a este usuario? Esta acción no se puede deshacer.'
                            : '¿Seguro que deseas eliminar este rol? Los usuarios perderán los permisos que otorga.'
                    }}
                </p>
                <p v-if="confirm.item" class="mt-3 font-semibold text-slate-700">
                    {{ confirm.item.name ?? confirm.item.label }}
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeConfirm">Cancelar</SecondaryButton>
                    <DangerButton @click="confirmDelete">Eliminar</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
