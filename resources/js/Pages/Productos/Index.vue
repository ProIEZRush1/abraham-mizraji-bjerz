<script setup>
import { computed, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    productos: { type: Object, required: true },
    categorias: { type: Array, default: () => [] },
    filtros: { type: Object, default: () => ({}) },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? null);
const flashError = computed(() => page.props.flash?.error ?? null);

const buscar = ref(props.filtros?.buscar ?? '');
const categoriaId = ref(props.filtros?.categoria_id ?? '');
const soloBajoStock = ref((props.filtros?.stock ?? '') === 'bajo');

const aplicarFiltros = () => {
    router.get(
        route('productos.index'),
        {
            buscar: buscar.value || undefined,
            categoria_id: categoriaId.value || undefined,
            stock: soloBajoStock.value ? 'bajo' : undefined,
        },
        { preserveState: true, replace: true },
    );
};

let debounceTimer = null;
watch(buscar, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(aplicarFiltros, 350);
});
watch(categoriaId, aplicarFiltros);
watch(soloBajoStock, aplicarFiltros);

const money = new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    maximumFractionDigits: 0,
});

const confirmDelete = ref({ show: false, producto: null });

const askDelete = (producto) => (confirmDelete.value = { show: true, producto });
const closeConfirm = () => (confirmDelete.value = { show: false, producto: null });

const eliminar = () => {
    const producto = confirmDelete.value.producto;
    if (!producto) return;
    router.delete(route('productos.destroy', producto.id), {
        preserveScroll: true,
        onFinish: closeConfirm,
    });
};
</script>

<template>
    <Head title="Productos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-bold tracking-tight text-slate-800">Productos</h2>
                <Link :href="route('productos.create')">
                    <PrimaryButton>Nuevo producto</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
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

            <!-- Filtros -->
            <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center">
                    <input
                        v-model="buscar"
                        type="text"
                        placeholder="Buscar por nombre…"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:max-w-xs"
                    />
                    <select
                        v-model="categoriaId"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:w-auto"
                    >
                        <option value="">Todas las categorías</option>
                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                            {{ categoria.nombre }}
                        </option>
                    </select>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input
                            v-model="soloBajoStock"
                            type="checkbox"
                            class="rounded border-gray-300 text-orange-600 shadow-sm focus:ring-orange-500"
                        />
                        Solo bajo stock
                    </label>
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Precio</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="producto in productos.data" :key="producto.id">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img
                                            v-if="producto.imagen_principal"
                                            :src="producto.imagen_principal"
                                            :alt="producto.nombre"
                                            class="h-12 w-12 rounded-lg object-cover shadow-sm"
                                        />
                                        <div
                                            v-else
                                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-xl"
                                        >
                                            📦
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-800">{{ producto.nombre }}</p>
                                            <p class="truncate text-sm text-slate-400">
                                                {{ producto.categoria ?? 'Sin categoría' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <template v-if="producto.precio_oferta">
                                        <span class="text-sm text-slate-400 line-through">{{ money.format(producto.precio) }}</span>
                                        <span class="ml-2 font-semibold text-orange-600">{{ money.format(producto.precio_oferta) }}</span>
                                    </template>
                                    <template v-else>
                                        <span class="font-semibold text-slate-700">{{ money.format(producto.precio) }}</span>
                                    </template>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span :class="producto.bajo_stock ? 'font-semibold text-red-600' : 'text-slate-700'">
                                            {{ producto.stock }}
                                        </span>
                                        <span
                                            v-if="producto.bajo_stock"
                                            class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700"
                                        >
                                            ⚠️ Stock bajo
                                        </span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span
                                            :class="[
                                                'rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                producto.activo
                                                    ? 'bg-emerald-50 text-emerald-700'
                                                    : 'bg-slate-100 text-slate-500',
                                            ]"
                                        >
                                            {{ producto.activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                        <span
                                            v-if="producto.destacado"
                                            class="rounded-full bg-gradient-to-r from-[#92400e] to-[#d97706] px-2.5 py-0.5 text-xs font-medium text-white"
                                        >
                                            Destacado
                                        </span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('productos.edit', producto.id)">
                                            <SecondaryButton type="button">Editar</SecondaryButton>
                                        </Link>
                                        <DangerButton type="button" @click="askDelete(producto)">Eliminar</DangerButton>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!productos.data.length">
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-400">
                                    No hay productos que coincidan con la búsqueda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="productos.links && productos.links.length > 3"
                    class="flex flex-wrap items-center justify-center gap-1 border-t border-slate-100 px-6 py-4"
                >
                    <template v-for="(link, index) in productos.links" :key="index">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            preserve-state
                            :class="[
                                'rounded-lg px-3 py-1.5 text-sm font-medium transition',
                                link.active
                                    ? 'bg-gradient-to-r from-[#92400e] to-[#d97706] text-white shadow-sm'
                                    : 'text-slate-500 hover:bg-slate-100',
                            ]"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="cursor-not-allowed rounded-lg px-3 py-1.5 text-sm font-medium text-slate-300"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <Modal :show="confirmDelete.show" max-width="md" @close="closeConfirm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-800">Confirmar eliminación</h2>
                <p class="mt-2 text-sm text-slate-500">
                    ¿Seguro que deseas eliminar este producto? Esta acción no se puede deshacer.
                </p>
                <p v-if="confirmDelete.producto" class="mt-3 font-semibold text-slate-700">
                    {{ confirmDelete.producto.nombre }}
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeConfirm">Cancelar</SecondaryButton>
                    <DangerButton @click="eliminar">Eliminar</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
