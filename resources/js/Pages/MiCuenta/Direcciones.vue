<script setup>
import { computed, ref, watch } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    direcciones: { type: Array, default: () => [] },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? null);
const flashError = computed(() => page.props.flash?.error ?? null);

const PROVINCIAS = [
    'Buenos Aires', 'CABA', 'Catamarca', 'Chaco', 'Chubut', 'Córdoba', 'Corrientes',
    'Entre Ríos', 'Formosa', 'Jujuy', 'La Pampa', 'La Rioja', 'Mendoza', 'Misiones',
    'Neuquén', 'Río Negro', 'Salta', 'San Juan', 'San Luis', 'Santa Cruz', 'Santa Fe',
    'Santiago del Estero', 'Tierra del Fuego', 'Tucumán',
];

const formModal = ref({ show: false, direccion: null });
const confirmModal = ref({ show: false, direccion: null });

const isEdit = computed(() => !!formModal.value.direccion);

const form = useForm({
    etiqueta: '',
    destinatario: '',
    calle: '',
    numero: '',
    piso_depto: '',
    ciudad: '',
    provincia: '',
    codigo_postal: '',
    telefono: '',
    predeterminada: false,
});

watch(
    () => formModal.value.show,
    (open) => {
        if (!open) return;
        form.clearErrors();
        const d = formModal.value.direccion;
        form.etiqueta = d?.etiqueta ?? '';
        form.destinatario = d?.destinatario ?? '';
        form.calle = d?.calle ?? '';
        form.numero = d?.numero ?? '';
        form.piso_depto = d?.piso_depto ?? '';
        form.ciudad = d?.ciudad ?? '';
        form.provincia = d?.provincia ?? '';
        form.codigo_postal = d?.codigo_postal ?? '';
        form.telefono = d?.telefono ?? '';
        form.predeterminada = d?.predeterminada ?? false;
    },
);

const openCreate = () => (formModal.value = { show: true, direccion: null });
const openEdit = (direccion) => (formModal.value = { show: true, direccion });
const closeForm = () => (formModal.value.show = false);

const submit = () => {
    if (isEdit.value) {
        form.put(route('direcciones.update', formModal.value.direccion.id), {
            preserveScroll: true,
            onSuccess: () => closeForm(),
        });
    } else {
        form.post(route('direcciones.store'), {
            preserveScroll: true,
            onSuccess: () => closeForm(),
        });
    }
};

const askDelete = (direccion) => (confirmModal.value = { show: true, direccion });
const closeConfirm = () => (confirmModal.value = { show: false, direccion: null });

const confirmDelete = () => {
    router.delete(route('direcciones.destroy', confirmModal.value.direccion.id), {
        preserveScroll: true,
        onFinish: closeConfirm,
    });
};

const direccionCompleta = (d) => {
    const piso = d.piso_depto ? `, ${d.piso_depto}` : '';
    return `${d.calle} ${d.numero}${piso}, ${d.ciudad}, ${d.provincia} (${d.codigo_postal})`;
};
</script>

<template>
    <Head title="Mis direcciones" />

    <PublicLayout>
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900">Mis direcciones</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Guarda tus direcciones para agilizar tus próximas compras.
                    </p>
                </div>
                <PrimaryButton @click="openCreate">Agregar dirección</PrimaryButton>
            </div>

            <div
                v-if="flashSuccess"
                class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-medium text-emerald-800"
            >
                {{ flashSuccess }}
            </div>
            <div
                v-if="flashError"
                class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-medium text-red-700"
            >
                {{ flashError }}
            </div>

            <div v-if="direcciones.length" class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div
                    v-for="direccion in direcciones"
                    :key="direccion.id"
                    class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-3">
                        <p class="font-bold text-slate-800">{{ direccion.etiqueta }}</p>
                        <span
                            v-if="direccion.predeterminada"
                            class="rounded-full bg-gradient-to-r from-[#92400e] to-[#d97706] px-2.5 py-0.5 text-xs font-semibold text-white"
                        >
                            Predeterminada
                        </span>
                    </div>
                    <p class="mt-2 text-sm font-medium text-slate-700">{{ direccion.destinatario }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ direccionCompleta(direccion) }}</p>
                    <p v-if="direccion.telefono" class="mt-1 text-sm text-slate-500">Tel: {{ direccion.telefono }}</p>

                    <div class="mt-4 flex items-center gap-2">
                        <SecondaryButton @click="openEdit(direccion)">Editar</SecondaryButton>
                        <DangerButton @click="askDelete(direccion)">Eliminar</DangerButton>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="mt-10 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center"
            >
                <span class="text-4xl">📍</span>
                <p class="mt-4 text-base font-semibold text-slate-700">
                    Todavía no tienes direcciones guardadas.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Agrega una para agilizar tus próximas compras.
                </p>
                <PrimaryButton class="mt-6" @click="openCreate">Agregar dirección</PrimaryButton>
            </div>
        </div>

        <Modal :show="formModal.show" max-width="lg" @close="closeForm">
            <form @submit.prevent="submit" class="p-6">
                <h2 class="text-lg font-bold text-slate-800">
                    {{ isEdit ? 'Editar dirección' : 'Nueva dirección' }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Completa los datos de la dirección de entrega.
                </p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Etiqueta" />
                        <TextInput v-model="form.etiqueta" type="text" class="mt-1 block w-full" placeholder="Casa, Trabajo…" autofocus />
                        <InputError :message="form.errors.etiqueta" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Destinatario" />
                        <TextInput v-model="form.destinatario" type="text" class="mt-1 block w-full" placeholder="Nombre y apellido" />
                        <InputError :message="form.errors.destinatario" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Calle" />
                        <TextInput v-model="form.calle" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.calle" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Número" />
                        <TextInput v-model="form.numero" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.numero" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Piso / Depto (opcional)" />
                        <TextInput v-model="form.piso_depto" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.piso_depto" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Ciudad" />
                        <TextInput v-model="form.ciudad" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.ciudad" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Provincia" />
                        <select
                            v-model="form.provincia"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#d97706] focus:ring-[#d97706]"
                        >
                            <option value="" disabled>Selecciona una provincia</option>
                            <option v-for="prov in PROVINCIAS" :key="prov" :value="prov">{{ prov }}</option>
                        </select>
                        <InputError :message="form.errors.provincia" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Código postal" />
                        <TextInput v-model="form.codigo_postal" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.codigo_postal" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Teléfono (opcional)" />
                        <TextInput v-model="form.telefono" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.telefono" class="mt-1" />
                    </div>
                </div>

                <label class="mt-4 flex cursor-pointer items-center gap-3">
                    <Checkbox v-model:checked="form.predeterminada" />
                    <span class="text-sm text-slate-700">Usar como dirección predeterminada</span>
                </label>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeForm">Cancelar</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">
                        {{ isEdit ? 'Guardar cambios' : 'Guardar dirección' }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <Modal :show="confirmModal.show" max-width="md" @close="closeConfirm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-800">Eliminar dirección</h2>
                <p class="mt-2 text-sm text-slate-500">
                    ¿Seguro que deseas eliminar esta dirección? Esta acción no se puede deshacer.
                </p>
                <p v-if="confirmModal.direccion" class="mt-3 font-semibold text-slate-700">
                    {{ confirmModal.direccion.etiqueta }}
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeConfirm">Cancelar</SecondaryButton>
                    <DangerButton @click="confirmDelete">Eliminar</DangerButton>
                </div>
            </div>
        </Modal>
    </PublicLayout>
</template>
