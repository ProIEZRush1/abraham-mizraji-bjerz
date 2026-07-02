<script setup>
import { computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: { type: Boolean, default: false },
    user: { type: Object, default: null },
    roles: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

const isEdit = computed(() => !!props.user);

const form = useForm({
    name: '',
    email: '',
    role_ids: [],
});

watch(
    () => props.show,
    (open) => {
        if (!open) return;
        form.clearErrors();
        form.name = props.user?.name ?? '';
        form.email = props.user?.email ?? '';
        form.role_ids = [...(props.user?.role_ids ?? [])];
    },
);

const toggleRole = (id) => {
    const idx = form.role_ids.indexOf(id);
    if (idx === -1) form.role_ids.push(id);
    else form.role_ids.splice(idx, 1);
};

const close = () => emit('close');

const submit = () => {
    if (isEdit.value) {
        form.put(route('roles.users.update', props.user.id), {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    } else {
        form.post(route('roles.users.store'), {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    }
};
</script>

<template>
    <Modal :show="show" @close="close">
        <form @submit.prevent="submit" class="p-6">
            <h2 class="text-lg font-bold text-slate-800">
                {{ isEdit ? 'Editar usuario' : 'Invitar usuario' }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                {{
                    isEdit
                        ? 'Actualiza los datos y los roles asignados.'
                        : 'Se generará una contraseña temporal que podrás compartir con la persona.'
                }}
            </p>

            <div class="mt-5 space-y-4">
                <div>
                    <InputLabel value="Nombre" />
                    <TextInput v-model="form.name" type="text" class="mt-1 block w-full" autofocus />
                    <InputError :message="form.errors.name" class="mt-1" />
                </div>

                <div>
                    <InputLabel value="Correo electrónico" />
                    <TextInput v-model="form.email" type="email" class="mt-1 block w-full" />
                    <InputError :message="form.errors.email" class="mt-1" />
                </div>

                <div>
                    <InputLabel value="Roles" />
                    <p v-if="!roles.length" class="mt-1 text-sm text-slate-400">
                        Aún no hay roles disponibles.
                    </p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="role in roles"
                            :key="role.id"
                            type="button"
                            @click="toggleRole(role.id)"
                            :class="[
                                'rounded-full border px-3 py-1.5 text-sm font-medium transition',
                                form.role_ids.includes(role.id)
                                    ? 'border-transparent bg-gradient-to-r from-[#92400e] to-[#d97706] text-white shadow'
                                    : 'border-slate-300 bg-white text-slate-600 hover:border-slate-400',
                            ]"
                        >
                            {{ role.label }}
                        </button>
                    </div>
                    <InputError :message="form.errors.role_ids" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
                <PrimaryButton :disabled="form.processing">
                    {{ isEdit ? 'Guardar cambios' : 'Crear usuario' }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
