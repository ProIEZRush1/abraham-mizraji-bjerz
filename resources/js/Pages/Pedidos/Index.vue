<script setup>
import { computed, reactive, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    pedidos: { type: Object, required: true },
    estados: { type: Array, default: () => [] },
    filtros: { type: Object, default: () => ({}) },
    flash: { type: Object, default: () => ({}) },
});

const flashSuccess = computed(() => props.flash?.success ?? null);
const flashError = computed(() => props.flash?.error ?? null);

const estadoLabels = {
    pendiente: 'Pendiente',
    pagado: 'Pagado',
    en_preparacion: 'En preparación',
    enviado: 'Enviado',
    entregado: 'Entregado',
    cancelado: 'Cancelado',
};

const estadoBadgeClass = {
    pendiente: 'bg-slate-100 text-slate-600 ring-slate-500/20',
    pagado: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    en_preparacion: 'bg-amber-50 text-amber-700 ring-amber-600/20',
    enviado: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    entregado: 'bg-emerald-100 text-emerald-800 ring-emerald-700/20',
    cancelado: 'bg-red-50 text-red-700 ring-red-600/20',
};

const estadoLabel = (estado) => estadoLabels[estado] ?? estado;
const estadoBadge = (estado) => estadoBadgeClass[estado] ?? 'bg-slate-100 text-slate-600 ring-slate-500/20';

const currency = new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    maximumFractionDigits: 0,
});
const formatMoney = (valor) => currency.format(valor ?? 0);

const form = reactive({
    buscar: props.filtros.buscar ?? '',
    estado: props.filtros.estado ?? '',
});

const applyFilters = () => {
    const query = Object.fromEntries(
        Object.entries(form).filter(([, value]) => value !== '' && value !== null),
    );

    router.get(route('pedidos.index'), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

let debounce = null;
watch(
    () => form.buscar,
    () => {
        clearTimeout(debounce);
        debounce = setTimeout(applyFilters, 350);
    },
);

watch(
    () => form.estado,
    () => applyFilters(),
);
</script>

<template>
    <Head title="Pedidos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Pedidos</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- Encabezado -->
            <section
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#92400e] to-[#d97706] p-7 text-white shadow-xl shadow-orange-500/20"
            >
                <div
                    class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"
                ></div>
                <div class="relative">
                    <p class="text-sm font-medium uppercase tracking-widest text-white/70">
                        Ventas
                    </p>
                    <h1 class="mt-2 text-2xl font-extrabold sm:text-3xl">Pedidos</h1>
                    <p class="mt-2 max-w-2xl text-sm text-white/85">
                        Seguí el estado de cada compra, desde que se genera hasta que llega a manos del cliente.
                    </p>
                </div>
            </section>

            <!-- Flash -->
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
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-500">
                            Buscar
                        </label>
                        <input
                            v-model="form.buscar"
                            type="text"
                            placeholder="Número de pedido o cliente…"
                            class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-[#92400e] focus:ring-[#92400e]"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">
                            Estado
                        </label>
                        <select
                            v-model="form.estado"
                            class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-[#92400e] focus:ring-[#92400e]"
                        >
                            <option value="">Todos</option>
                            <option v-for="estado in estados" :key="estado" :value="estado">
                                {{ estadoLabel(estado) }}
                            </option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Tabla -->
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3">Pedido</th>
                                <th class="px-5 py-3">Cliente</th>
                                <th class="px-5 py-3">Fecha</th>
                                <th class="px-5 py-3">Total</th>
                                <th class="px-5 py-3">Estado</th>
                                <th class="px-5 py-3 text-right">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="pedido in pedidos.data"
                                :key="pedido.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="whitespace-nowrap px-5 py-3">
                                    <span class="font-mono font-bold text-slate-800">
                                        {{ pedido.numero_pedido }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-600">
                                    {{ pedido.cliente ?? 'Invitado' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-slate-500">
                                    {{ pedido.created_at }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 font-semibold text-slate-800">
                                    {{ formatMoney(pedido.total) }}
                                </td>
                                <td class="px-5 py-3">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset',
                                            estadoBadge(pedido.estado),
                                        ]"
                                    >
                                        {{ estadoLabel(pedido.estado) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <Link
                                        :href="route('pedidos.show', pedido.id)"
                                        class="rounded-lg px-3 py-1.5 text-xs font-semibold text-[#92400e] transition hover:bg-orange-50"
                                    >
                                        Ver
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="pedidos.data.length === 0">
                                <td colspan="6" class="px-5 py-16 text-center">
                                    <p class="text-sm font-semibold text-slate-500">
                                        No hay pedidos que coincidan con tu búsqueda
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        Probá con otro término o cambiá el filtro de estado.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="pedidos.links && pedidos.links.length > 3"
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-4"
                >
                    <p class="text-xs text-slate-500">
                        Mostrando {{ pedidos.from ?? 0 }}–{{ pedidos.to ?? 0 }} de {{ pedidos.total }} pedidos
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, index) in pedidos.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                preserve-state
                                replace
                                :class="[
                                    'min-w-9 rounded-lg px-3 py-1.5 text-center text-xs font-semibold transition',
                                    link.active
                                        ? 'bg-gradient-to-r from-[#92400e] to-[#d97706] text-white shadow-sm'
                                        : 'text-slate-600 hover:bg-slate-100',
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="min-w-9 rounded-lg px-3 py-1.5 text-center text-xs font-semibold text-slate-300"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
