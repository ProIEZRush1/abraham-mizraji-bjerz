<script setup>
import { computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    pedidos: { type: Array, default: () => [] },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? null);
const flashError = computed(() => page.props.flash?.error ?? null);

const ESTADOS = {
    pendiente: { label: 'Pendiente', classes: 'bg-slate-100 text-slate-600' },
    pagado: { label: 'Pagado', classes: 'bg-emerald-50 text-emerald-700' },
    en_preparacion: { label: 'En preparación', classes: 'bg-amber-50 text-amber-700' },
    enviado: { label: 'Enviado', classes: 'bg-blue-50 text-blue-700' },
    entregado: { label: 'Entregado', classes: 'bg-emerald-100 text-emerald-800' },
    cancelado: { label: 'Cancelado', classes: 'bg-red-50 text-red-700' },
};

const estadoInfo = (estado) => ESTADOS[estado] ?? { label: estado, classes: 'bg-slate-100 text-slate-600' };

const formatoPrecio = (valor) =>
    new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(valor);
</script>

<template>
    <Head title="Mis pedidos" />

    <PublicLayout>
        <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold text-slate-900">Mis pedidos</h1>
            <p class="mt-1 text-sm text-slate-500">
                Consulta el estado y el detalle de todas tus compras en Abraham Mizraji.
            </p>

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

            <div v-if="pedidos.length" class="mt-8 space-y-4">
                <div
                    v-for="pedido in pedidos"
                    :key="pedido.id"
                    class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="min-w-0">
                        <p class="font-bold text-slate-800">{{ pedido.numero_pedido }}</p>
                        <p class="mt-0.5 text-sm text-slate-500">{{ pedido.created_at }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 sm:gap-6">
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="estadoInfo(pedido.estado).classes"
                        >
                            {{ estadoInfo(pedido.estado).label }}
                        </span>
                        <span class="text-base font-bold text-slate-900">
                            {{ formatoPrecio(pedido.total) }}
                        </span>
                        <Link
                            :href="route('mis-pedidos.show', pedido.id)"
                            class="text-sm font-semibold text-[#92400e] hover:text-[#d97706]"
                        >
                            Ver detalle →
                        </Link>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="mt-10 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center"
            >
                <span class="text-4xl">🛍️</span>
                <p class="mt-4 text-base font-semibold text-slate-700">
                    Todavía no tienes pedidos realizados.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Cuando hagas tu primera compra, la vas a ver reflejada acá.
                </p>
                <Link
                    href="/tienda"
                    class="mt-6 inline-flex items-center rounded-xl bg-gradient-to-r from-[#92400e] to-[#d97706] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-orange-500/20 transition hover:opacity-90"
                >
                    Ir a la tienda
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
