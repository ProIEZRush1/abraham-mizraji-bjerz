<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    resumen: { type: Array, default: () => [] },
    serie: { type: Array, default: () => [] },
    categorias: { type: Array, default: () => [] },
    productosMasVendidos: { type: Array, default: () => [] },
    generadoEl: { type: String, default: '' },
});

const cardIcons = [
    'M9 17v-6h13M9 17H4m5 0V5m0 0H4m5 0h11', // barras
    'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', // dinero
    'M7 7h.01M7 3h5a2 2 0 012 2v5l-7 7-7-7V5a2 2 0 012-2z', // etiqueta
    'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z', // promedio / pie
];

const maxValor = computed(() =>
    props.serie.reduce((max, p) => Math.max(max, Number(p.total) || 0), 0),
);

const tieneDatos = computed(() => maxValor.value > 0);

// Altura de cada barra (0–100%) relativa al valor máximo de la serie.
const alturaBarra = (valor) => {
    if (maxValor.value <= 0) return 0;
    return Math.max(2, Math.round((Number(valor) / maxValor.value) * 100));
};

const maxCategoria = computed(() =>
    props.categorias.reduce((max, c) => Math.max(max, Number(c.total) || 0), 0),
);

const anchoCategoria = (total) => {
    if (maxCategoria.value <= 0) return 0;
    return Math.max(3, Math.round((Number(total) / maxCategoria.value) * 100));
};

const formatoMoneda = (valor) =>
    '$' +
    Number(valor || 0).toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

const maxProducto = computed(() =>
    props.productosMasVendidos.reduce((max, p) => Math.max(max, Number(p.unidades) || 0), 0),
);

const anchoProducto = (unidades) => {
    if (maxProducto.value <= 0) return 0;
    return Math.max(3, Math.round((Number(unidades) / maxProducto.value) * 100));
};

const exportCsvUrl = computed(() => route('reportes.export.csv'));
const exportPdfUrl = computed(() => route('reportes.export.pdf'));
</script>

<template>
    <Head title="Reportes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Reportes</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-8">
            <!-- Encabezado + exportaciones -->
            <section
                class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-lg font-bold text-slate-800">Resumen de ventas</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Generado el {{ generadoEl }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a
                        :href="exportCsvUrl"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                        </svg>
                        Exportar CSV
                    </a>
                    <a
                        :href="exportPdfUrl"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#92400e] to-[#d97706] px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:from-[#78350f] hover:to-[#78350f]"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar PDF
                    </a>
                </div>
            </section>

            <!-- Tarjetas de resumen -->
            <section>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="(tarjeta, i) in resumen"
                        :key="tarjeta.label"
                        class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <span
                            :class="[
                                'flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-md',
                                tarjeta.gradient,
                            ]"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="cardIcons[i % cardIcons.length]" />
                            </svg>
                        </span>
                        <p class="mt-4 text-2xl font-extrabold text-slate-800">{{ tarjeta.valor }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-600">{{ tarjeta.label }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ tarjeta.hint }}</p>
                    </div>
                </div>
            </section>

            <!-- Gráfica de barras (SVG ligero) -->
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <h3 class="text-lg font-bold text-slate-800">Ingresos (últimos {{ serie.length }} días)</h3>
                <p class="mt-1 text-sm text-slate-500">Total vendido por día.</p>

                <div v-if="tieneDatos" class="mt-6">
                    <div class="flex h-56 items-end gap-1.5 sm:gap-2">
                        <div
                            v-for="punto in serie"
                            :key="punto.fecha"
                            class="group/bar flex flex-1 flex-col items-center justify-end"
                        >
                            <div class="relative flex w-full justify-center">
                                <span
                                    class="pointer-events-none absolute -top-7 rounded-md bg-slate-800 px-2 py-1 text-[10px] font-semibold text-white opacity-0 transition group-hover/bar:opacity-100"
                                >
                                    {{ formatoMoneda(punto.total) }}
                                </span>
                            </div>
                            <div
                                class="w-full rounded-t-md bg-gradient-to-t from-[#92400e] to-[#d97706] transition-all duration-300 hover:opacity-80"
                                :style="{ height: alturaBarra(punto.total) + '%' }"
                            ></div>
                        </div>
                    </div>
                    <div class="mt-2 flex gap-1.5 sm:gap-2">
                        <span
                            v-for="punto in serie"
                            :key="punto.fecha + '-lbl'"
                            class="flex-1 truncate text-center text-[10px] text-slate-400"
                        >
                            {{ punto.etiqueta }}
                        </span>
                    </div>
                </div>

                <div
                    v-else
                    class="mt-6 flex h-40 flex-col items-center justify-center rounded-xl bg-slate-50 text-center"
                >
                    <p class="text-sm font-semibold text-slate-500">Aún no hay datos para graficar</p>
                    <p class="mt-1 text-xs text-slate-400">
                        Las métricas aparecerán aquí en cuanto se registren.
                    </p>
                </div>
            </section>

            <!-- Desglose por categoría -->
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <h3 class="text-lg font-bold text-slate-800">Ingresos por categoría</h3>

                <div v-if="categorias.length > 0" class="mt-6 space-y-4">
                    <div v-for="fila in categorias" :key="fila.categoria">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-700">{{ fila.categoria }}</span>
                            <span class="text-slate-500">
                                {{ formatoMoneda(fila.total) }}
                                <span class="text-xs text-slate-400">({{ fila.registros }} artículos)</span>
                            </span>
                        </div>
                        <div class="mt-1.5 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-[#92400e] to-[#d97706]"
                                :style="{ width: anchoCategoria(fila.total) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>

                <p v-else class="mt-6 text-sm text-slate-400">
                    Aún no hay ventas registradas por categoría.
                </p>
            </section>

            <!-- Productos más vendidos -->
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <h3 class="text-lg font-bold text-slate-800">Productos más vendidos</h3>

                <div v-if="productosMasVendidos.length > 0" class="mt-6 space-y-4">
                    <div v-for="fila in productosMasVendidos" :key="fila.nombre">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-700">{{ fila.nombre }}</span>
                            <span class="text-slate-500">
                                {{ fila.unidades }} unidades
                                <span class="text-xs text-slate-400">({{ formatoMoneda(fila.ingresos) }})</span>
                            </span>
                        </div>
                        <div class="mt-1.5 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-[#78350f] to-[#f59e0b]"
                                :style="{ width: anchoProducto(fila.unidades) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>

                <p v-else class="mt-6 text-sm text-slate-400">
                    Aún no hay productos vendidos.
                </p>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
