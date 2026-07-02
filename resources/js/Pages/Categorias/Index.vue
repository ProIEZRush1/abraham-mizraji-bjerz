<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import CategoriaFormModal from './Partials/CategoriaFormModal.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    categorias: { type: Array, default: () => [] },
    flash: { type: Object, default: () => ({}) },
});

const flashSuccess = computed(() => props.flash?.success ?? null);
const flashError = computed(() => props.flash?.error ?? null);

const categoriaModal = ref({ show: false, categoria: null });
const confirm = ref({ show: false, item: null });

const openNew = () => (categoriaModal.value = { show: true, categoria: null });
const openEdit = (categoria) => (categoriaModal.value = { show: true, categoria });

const askDelete = (item) => (confirm.value = { show: true, item });
const closeConfirm = () => (confirm.value = { show: false, item: null });

const confirmDelete = () => {
    router.delete(route('categorias.destroy', confirm.value.item.id), {
        preserveScroll: true,
        onFinish: closeConfirm,
    });
};
</script>

<template>
    <Head title="Categorías" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Categorías</h2>
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
                        <h3 class="text-base font-bold text-slate-800">Categorías del catálogo</h3>
                        <p class="text-sm text-slate-500">
                            Organizá tus productos para que los clientes los encuentren fácil.
                        </p>
                    </div>
                    <PrimaryButton @click="openNew">Nueva categoría</PrimaryButton>
                </div>

                <div v-if="categorias.length" class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
                    <div
                        v-for="categoria in categorias"
                        :key="categoria.id"
                        class="flex flex-col rounded-2xl border border-slate-200 p-5"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800">{{ categoria.nombre }}</p>
                                <p class="truncate text-xs text-slate-400">{{ categoria.slug }}</p>
                            </div>
                            <span
                                :class="[
                                    'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    categoria.activo
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-slate-100 text-slate-500',
                                ]"
                            >
                                {{ categoria.activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                        <p v-if="categoria.descripcion" class="mt-2 line-clamp-2 text-sm text-slate-500">
                            {{ categoria.descripcion }}
                        </p>
                        <p v-else class="mt-2 text-sm text-slate-400">Sin descripción.</p>
                        <div class="mt-3">
                            <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                                {{ categoria.productos_count }}
                                {{ categoria.productos_count === 1 ? 'producto' : 'productos' }}
                            </span>
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <SecondaryButton @click="openEdit(categoria)">Editar</SecondaryButton>
                            <DangerButton @click="askDelete(categoria)">Eliminar</DangerButton>
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                    <span class="text-4xl">🗂️</span>
                    <p class="text-sm font-semibold text-slate-700">Todavía no creaste ninguna categoría</p>
                    <p class="max-w-sm text-sm text-slate-500">
                        Las categorías te ayudan a ordenar el catálogo y a que los clientes filtren por tipo de producto.
                    </p>
                    <PrimaryButton @click="openNew">Crear la primera categoría</PrimaryButton>
                </div>
            </section>
        </div>

        <CategoriaFormModal
            :show="categoriaModal.show"
            :categoria="categoriaModal.categoria"
            @close="categoriaModal.show = false"
        />

        <Modal :show="confirm.show" max-width="md" @close="closeConfirm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-800">Confirmar eliminación</h2>
                <p class="mt-2 text-sm text-slate-500">
                    ¿Seguro que deseas eliminar esta categoría? Esta acción no se puede deshacer.
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
