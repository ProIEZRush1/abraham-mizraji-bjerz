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
    zona: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const isEdit = computed(() => !!props.zona);

const PROVINCIAS = [
    'Buenos Aires', 'CABA', 'Catamarca', 'Chaco', 'Chubut', 'Córdoba',
    'Corrientes', 'Entre Ríos', 'Formosa', 'Jujuy', 'La Pampa', 'La Rioja',
    'Mendoza', 'Misiones', 'Neuquén', 'Río Negro', 'Salta', 'San Juan',
    'San Luis', 'Santa Cruz', 'Santa Fe', 'Santiago del Estero',
    'Tierra del Fuego', 'Tucumán',
];

const form = useForm({
    nombre: '',
    provincias: [],
    costo: '',
    tiempo_estimado: '',
    activo: true,
});

watch(
    () => props.show,
    (open) => {
        if (!open) return;
        form.clearErrors();
        form.nombre = props.zona?.nombre ?? '';
        form.provincias = [...(props.zona?.provincias ?? [])];
        form.costo = props.zona?.costo ?? '';
        form.tiempo_estimado = props.zona?.tiempo_estimado ?? '';
        form.activo = props.zona?.activo ?? true;
    },
);

const toggleProvincia = (nombre) => {
    const idx = form.provincias.indexOf(nombre);
    if (idx === -1) form.provincias.push(nombre);
    else form.provincias.splice(idx, 1);
};

const close = () => emit('close');

const submit = () => {
    if (isEdit.value) {
        form.put(route('envios.update', props.zona.id), {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    } else {
        form.post(route('envios.store'), {
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
                {{ isEdit ? 'Editar zona de envío' : 'Nueva zona de envío' }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Definí las provincias, el costo y el tiempo estimado de entrega.
            </p>

            <div class="mt-5 space-y-4">
                <div>
                    <InputLabel value="Nombre de la zona" />
                    <TextInput
                        v-model="form.nombre"
                        type="text"
                        class="mt-1 block w-full"
                        autofocus
                        placeholder="CABA y GBA"
                    />
                    <InputError :message="form.errors.nombre" class="mt-1" />
                </div>

                <div>
                    <InputLabel value="Provincias" />
                    <div class="mt-2 grid max-h-56 grid-cols-2 gap-2 overflow-y-auto rounded-xl border border-slate-200 p-3 sm:grid-cols-3">
                        <label
                            v-for="provincia in PROVINCIAS"
                            :key="provincia"
                            class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1 text-sm transition hover:bg-slate-50"
                        >
                            <input
                                type="checkbox"
                                :checked="form.provincias.includes(provincia)"
                                @change="toggleProvincia(provincia)"
                                class="rounded border-slate-300 text-[#92400e] focus:ring-[#92400e]"
                            />
                            <span class="text-slate-700">{{ provincia }}</span>
                        </label>
                    </div>
                    <InputError :message="form.errors.provincias" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Costo de envío" />
                        <TextInput v-model="form.costo" type="number" min="0" step="0.01" class="mt-1 block w-full" />
                        <InputError :message="form.errors.costo" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Tiempo estimado (opcional)" />
                        <TextInput
                            v-model="form.tiempo_estimado"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="3-5 días hábiles"
                        />
                        <InputError :message="form.errors.tiempo_estimado" class="mt-1" />
                    </div>
                </div>

                <div>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 transition hover:border-slate-300">
                        <input
                            type="checkbox"
                            v-model="form.activo"
                            class="rounded border-slate-300 text-[#92400e] focus:ring-[#92400e]"
                        />
                        <span class="text-sm text-slate-700">Zona activa</span>
                    </label>
                    <InputError :message="form.errors.activo" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
                <PrimaryButton :disabled="form.processing">
                    {{ isEdit ? 'Guardar cambios' : 'Crear zona' }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
