<script setup>
import { reactive, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    logs: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    options: { type: Object, default: () => ({ events: [], types: [], users: [] }) },
});

const form = reactive({
    event: props.filters.event ?? '',
    auditable_type: props.filters.auditable_type ?? '',
    user_id: props.filters.user_id ?? '',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
    search: props.filters.search ?? '',
});

let debounce = null;

// Aplica los filtros conservando el estado y sin recargar toda la página.
const applyFilters = () => {
    const query = Object.fromEntries(
        Object.entries(form).filter(([, value]) => value !== '' && value !== null),
    );

    router.get(route('auditoria.index'), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(
    () => ({ ...form }),
    () => {
        clearTimeout(debounce);
        debounce = setTimeout(applyFilters, 350);
    },
);

const resetFilters = () => {
    form.event = '';
    form.auditable_type = '';
    form.user_id = '';
    form.from = '';
    form.to = '';
    form.search = '';
};

const eventBadge = (event) => {
    switch (event) {
        case 'created':
            return 'bg-emerald-50 text-emerald-700 ring-emerald-600/20';
        case 'updated':
            return 'bg-amber-50 text-amber-700 ring-amber-600/20';
        case 'deleted':
            return 'bg-rose-50 text-rose-700 ring-rose-600/20';
        default:
            return 'bg-slate-100 text-slate-600 ring-slate-500/20';
    }
};

// Detalle de cambios (modal).
const showingDetail = ref(false);
const selected = ref(null);

const openDetail = (log) => {
    selected.value = log;
    showingDetail.value = true;
};

const closeDetail = () => {
    showingDetail.value = false;
    selected.value = null;
};

const formatValue = (value) => {
    if (value === null || value === undefined || value === '') return '—';
    if (typeof value === 'object') return JSON.stringify(value);
    return String(value);
};

// Normaliza el objeto de cambios a filas { field, before, after } para la tabla del modal.
const changeRows = (changes) => {
    if (!changes) return [];
    const before = changes.before ?? {};
    const after = changes.after ?? {};
    const keys = Array.from(new Set([...Object.keys(before), ...Object.keys(after)]));
    return keys.map((field) => ({
        field,
        before: before[field],
        after: after[field],
    }));
};
</script>

<template>
    <Head title="Bitácora" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Bitácora</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- Encabezado -->
            <section
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#7c3aed] to-[#c026d3] p-7 text-white shadow-xl shadow-fuchsia-500/20"
            >
                <div
                    class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"
                ></div>
                <div class="relative">
                    <p class="text-sm font-medium uppercase tracking-widest text-white/70">
                        Auditoría
                    </p>
                    <h1 class="mt-2 text-2xl font-extrabold sm:text-3xl">
                        Registro de actividad
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm text-white/85">
                        Aquí queda registrada cada creación, actualización y eliminación
                        del sistema, con el usuario responsable y los cambios aplicados.
                    </p>
                </div>
            </section>

            <!-- Filtros -->
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
                    <div class="xl:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-500">
                            Buscar
                        </label>
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Usuario, modelo o ID…"
                            class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">
                            Acción
                        </label>
                        <select
                            v-model="form.event"
                            class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]"
                        >
                            <option value="">Todas</option>
                            <option
                                v-for="opt in options.events"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">
                            Módulo
                        </label>
                        <select
                            v-model="form.auditable_type"
                            class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]"
                        >
                            <option value="">Todos</option>
                            <option
                                v-for="opt in options.types"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">
                            Usuario
                        </label>
                        <select
                            v-model="form.user_id"
                            class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]"
                        >
                            <option value="">Todos</option>
                            <option
                                v-for="opt in options.users"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:col-span-2 xl:col-span-1">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">
                                Desde
                            </label>
                            <input
                                v-model="form.from"
                                type="date"
                                class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">
                                Hasta
                            </label>
                            <input
                                v-model="form.to"
                                type="date"
                                class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]"
                            />
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        @click="resetFilters"
                        class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                    >
                        Limpiar filtros
                    </button>
                </div>
            </section>

            <!-- Tabla -->
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3">Fecha</th>
                                <th class="px-5 py-3">Acción</th>
                                <th class="px-5 py-3">Módulo</th>
                                <th class="px-5 py-3">Registro</th>
                                <th class="px-5 py-3">Usuario</th>
                                <th class="px-5 py-3 text-right">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="log in logs.data"
                                :key="log.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="whitespace-nowrap px-5 py-3 text-slate-600">
                                    <span class="font-medium text-slate-800">{{ log.created_at }}</span>
                                    <span class="block text-xs text-slate-400">{{ log.created_at_human }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset',
                                            eventBadge(log.event),
                                        ]"
                                    >
                                        {{ log.event_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-medium text-slate-700">
                                    {{ log.auditable_label }}
                                </td>
                                <td class="px-5 py-3 text-slate-500">
                                    #{{ log.auditable_id ?? '—' }}
                                </td>
                                <td class="px-5 py-3 text-slate-600">
                                    {{ log.user_name }}
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button
                                        type="button"
                                        @click="openDetail(log)"
                                        class="rounded-lg px-3 py-1.5 text-xs font-semibold text-[#7c3aed] transition hover:bg-fuchsia-50"
                                    >
                                        Ver cambios
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="px-5 py-16 text-center">
                                    <p class="text-sm font-semibold text-slate-500">
                                        No hay registros en la bitácora
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        Las acciones del sistema aparecerán aquí conforme ocurran.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="logs.links && logs.links.length > 3"
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-4"
                >
                    <p class="text-xs text-slate-500">
                        Mostrando {{ logs.from ?? 0 }}–{{ logs.to ?? 0 }} de {{ logs.total }} registros
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, index) in logs.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                preserve-state
                                replace
                                :class="[
                                    'min-w-9 rounded-lg px-3 py-1.5 text-center text-xs font-semibold transition',
                                    link.active
                                        ? 'bg-gradient-to-r from-[#7c3aed] to-[#c026d3] text-white shadow-sm'
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

        <!-- Modal: detalle de cambios -->
        <Modal :show="showingDetail" @close="closeDetail" max-width="2xl">
            <div v-if="selected" class="p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">
                            {{ selected.event_label }} · {{ selected.auditable_label }}
                            <span class="text-slate-400">#{{ selected.auditable_id ?? '—' }}</span>
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ selected.user_name }} · {{ selected.created_at }}
                            <span v-if="selected.ip_address"> · {{ selected.ip_address }}</span>
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="closeDetail"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-5 overflow-hidden rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-4 py-2.5">Campo</th>
                                <th class="px-4 py-2.5">Antes</th>
                                <th class="px-4 py-2.5">Después</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="row in changeRows(selected.changes)" :key="row.field">
                                <td class="px-4 py-2.5 font-semibold text-slate-700">{{ row.field }}</td>
                                <td class="px-4 py-2.5 text-rose-600">{{ formatValue(row.before) }}</td>
                                <td class="px-4 py-2.5 text-emerald-600">{{ formatValue(row.after) }}</td>
                            </tr>
                            <tr v-if="changeRows(selected.changes).length === 0">
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-400">
                                    Sin cambios detallados para esta acción.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        @click="closeDetail"
                        class="rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-5 py-2 text-sm font-semibold text-white shadow-md shadow-fuchsia-500/20 transition hover:opacity-95"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
