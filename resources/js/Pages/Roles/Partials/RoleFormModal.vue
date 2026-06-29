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
    role: { type: Object, default: null },
    permissions: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

const isEdit = computed(() => !!props.role);

const form = useForm({
    label: '',
    description: '',
    permission_ids: [],
});

watch(
    () => props.show,
    (open) => {
        if (!open) return;
        form.clearErrors();
        form.label = props.role?.label ?? '';
        form.description = props.role?.description ?? '';
        form.permission_ids = [...(props.role?.permission_ids ?? [])];
    },
);

const togglePermission = (id) => {
    const idx = form.permission_ids.indexOf(id);
    if (idx === -1) form.permission_ids.push(id);
    else form.permission_ids.splice(idx, 1);
};

const close = () => emit('close');

const submit = () => {
    if (isEdit.value) {
        form.put(route('roles.update', props.role.id), {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    } else {
        form.post(route('roles.store'), {
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
                {{ isEdit ? 'Editar rol' : 'Nuevo rol' }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Define un rol y los permisos que otorga dentro del panel.
            </p>

            <div class="mt-5 space-y-4">
                <div>
                    <InputLabel value="Nombre del rol" />
                    <TextInput v-model="form.label" type="text" class="mt-1 block w-full" autofocus />
                    <InputError :message="form.errors.label" class="mt-1" />
                </div>

                <div>
                    <InputLabel value="Descripción (opcional)" />
                    <TextInput v-model="form.description" type="text" class="mt-1 block w-full" />
                    <InputError :message="form.errors.description" class="mt-1" />
                </div>

                <div>
                    <InputLabel value="Permisos" />
                    <p v-if="!permissions.length" class="mt-1 text-sm text-slate-400">
                        No hay permisos definidos.
                    </p>
                    <div class="mt-2 space-y-2">
                        <label
                            v-for="permission in permissions"
                            :key="permission.id"
                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 transition hover:border-slate-300"
                        >
                            <input
                                type="checkbox"
                                :checked="form.permission_ids.includes(permission.id)"
                                @change="togglePermission(permission.id)"
                                class="rounded border-slate-300 text-[#7c3aed] focus:ring-[#7c3aed]"
                            />
                            <span class="text-sm text-slate-700">{{ permission.label }}</span>
                        </label>
                    </div>
                    <InputError :message="form.errors.permission_ids" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
                <PrimaryButton :disabled="form.processing">
                    {{ isEdit ? 'Guardar cambios' : 'Crear rol' }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
