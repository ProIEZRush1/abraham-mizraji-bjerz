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
    categoria: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const isEdit = computed(() => !!props.categoria);

const form = useForm({
    nombre: '',
    descripcion: '',
    imagen: '',
    activo: true,
});

watch(
    () => props.show,
    (open) => {
        if (!open) return;
        form.clearErrors();
        form.nombre = props.categoria?.nombre ?? '';
        form.descripcion = props.categoria?.descripcion ?? '';
        form.imagen = props.categoria?.imagen ?? '';
        form.activo = props.categoria?.activo ?? true;
    },
);

const close = () => emit('close');

const submit = () => {
    if (isEdit.value) {
        form.put(route('categorias.update', props.categoria.id), {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    } else {
        form.post(route('categorias.store'), {
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
                {{ isEdit ? 'Editar categoría' : 'Nueva categoría' }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Organizá tu catálogo agrupando los productos por categoría.
            </p>

            <div class="mt-5 space-y-4">
                <div>
                    <InputLabel value="Nombre" />
                    <TextInput v-model="form.nombre" type="text" class="mt-1 block w-full" autofocus />
                    <InputError :message="form.errors.nombre" class="mt-1" />
                </div>

                <div>
                    <InputLabel value="Descripción (opcional)" />
                    <textarea
                        v-model="form.descripcion"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    ></textarea>
                    <InputError :message="form.errors.descripcion" class="mt-1" />
                </div>

                <div>
                    <InputLabel value="Imagen (URL, opcional)" />
                    <TextInput v-model="form.imagen" type="text" class="mt-1 block w-full" placeholder="https://..." />
                    <InputError :message="form.errors.imagen" class="mt-1" />
                </div>

                <div>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 transition hover:border-slate-300">
                        <input
                            type="checkbox"
                            v-model="form.activo"
                            class="rounded border-slate-300 text-[#92400e] focus:ring-[#92400e]"
                        />
                        <span class="text-sm text-slate-700">Categoría activa (visible en la tienda)</span>
                    </label>
                    <InputError :message="form.errors.activo" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
                <PrimaryButton :disabled="form.processing">
                    {{ isEdit ? 'Guardar cambios' : 'Crear categoría' }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
