<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ZonaEnvioFormModal from './Partials/ZonaEnvioFormModal.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    zonas: { type: Array, default: () => [] },
    flash: { type: Object, default: () => ({}) },
});

const flashSuccess = computed(() => props.flash?.success ?? null);
const flashError = computed(() => props.flash?.error ?? null);

const currency = new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    maximumFractionDigits: 0,
});

const zonaModal = ref({ show: false, zona: null });
const confirm = ref({ show: false, item: null });

const openNew = () => (zonaModal.value = { show: true, zona: null });
const openEdit = (zona) => (zonaModal.value = { show: true, zona });

const askDelete = (item) => (confirm.value = { show: true, item });
const closeConfirm = () => (confirm.value = { show: false, item: null });

const confirmDelete = () => {
    router.delete(route('envios.destroy', confirm.value.item.id), {
        preserveScroll: true,
        onFinish: closeConfirm,
    });
};
</script>

<template>
    <Head title="Zonas de envío" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Zonas de envío</h2>
        </template>

        <div class="mx-auto max-w-6xl space-y-6">
            <div
                v-if="flashSuccess"
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

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Zonas y costos de envío</h3>
                        <p class="text-sm text-slate-500">
                            Definí a qué provincias llegás, cuánto cuesta y en cuánto tiempo.
                        </p>
                    </div>
                    <PrimaryButton @click="openNew">Nueva zona</PrimaryButton>
                </div>

                <div v-if="zonas.length" class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
                    <div
                        v-for="zona in zonas"
                        :key="zona.id"
                        class="flex flex-col rounded-2xl border border-slate-200 p-5"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800">{{ zona.nombre }}</p>
                                <p class="text-sm font-semibold text-amber-700">{{ currency.format(zona.costo) }}</p>
                            </div>
                            <span
                                :class="[
                                    'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    zona.activo
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-slate-100 text-slate-500',
                                ]"
                            >
                                {{ zona.activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ zona.tiempo_estimado ?? 'Tiempo de entrega no especificado' }}
                        </p>

                        <div v-if="zona.provincias.length" class="mt-3 flex flex-wrap gap-1.5">
                            <template v-if="zona.provincias.length <= 6">
                                <span
                                    v-for="provincia in zona.provincias"
                                    :key="provincia"
                                    class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700"
                                >
                                    {{ provincia }}
                                </span>
                            </template>
                            <span
                                v-else
                                class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700"
                            >
                                {{ zona.provincias.length }} provincias
                            </span>
                        </div>
                        <p v-else class="mt-3 text-xs text-slate-400">Sin provincias asignadas.</p>

                        <div class="mt-4 flex items-center gap-2">
                            <SecondaryButton @click="openEdit(zona)">Editar</SecondaryButton>
                            <DangerButton @click="askDelete(zona)">Eliminar</DangerButton>
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                    <span class="text-4xl">🚚</span>
                    <p class="text-sm font-semibold text-slate-700">Todavía no configuraste zonas de envío</p>
                    <p class="max-w-sm text-sm text-slate-500">
                        Creá las zonas a las que llegás para que el costo de envío se calcule automáticamente en el checkout.
                    </p>
                    <PrimaryButton @click="openNew">Crear la primera zona</PrimaryButton>
                </div>
            </section>
        </div>

        <ZonaEnvioFormModal
            :show="zonaModal.show"
            :zona="zonaModal.zona"
            @close="zonaModal.show = false"
        />

        <Modal :show="confirm.show" max-width="md" @close="closeConfirm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-800">Confirmar eliminación</h2>
                <p class="mt-2 text-sm text-slate-500">
                    ¿Seguro que deseas eliminar esta zona de envío? Esta acción no se puede deshacer.
                </p>
                <p v-if="confirm.item" class="mt-3 font-semibold text-slate-700">
                    {{ confirm.item.nombre }}
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeConfirm">Cancelar</SecondaryButton>
                    <DangerButton @click="confirmDelete">Eliminar</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
