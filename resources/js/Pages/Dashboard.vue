<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    stats: { type: Object, required: true },
    productosBajoStock: { type: Array, default: () => [] },
    ultimosPedidos: { type: Array, default: () => [] },
});

const page = usePage();
const businessName = computed(() => page.props.name ?? 'Mi Negocio');
const userFirstName = computed(() => {
    const name = (page.props.auth?.user?.name ?? '').trim();
    return name ? name.split(/\s+/)[0] : '';
});

const formatoPrecio = (valor) =>
    new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(valor);

const tarjetas = computed(() => [
    {
        label: 'Productos',
        value: props.stats.productos,
        hint: `${props.stats.categorias} categorías`,
        href: '/productos',
        gradient: 'from-[#92400e] to-[#b45309]',
        icon: 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
    },
    {
        label: 'Pedidos del mes',
        value: props.stats.pedidos_mes,
        hint: `${props.stats.pedidos_pendientes} pendientes`,
        href: '/pedidos',
        gradient: 'from-[#78350f] to-[#d97706]',
        icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293A1 1 0 005.414 17H17M17 17a2 2 0 100 4 2 2 0 000-4zM9 19a2 2 0 11-4 0 2 2 0 014 0z',
    },
    {
        label: 'Ingresos del mes',
        value: formatoPrecio(props.stats.ingresos_mes),
        hint: 'Pedidos pagados o en curso',
        href: '/reportes',
        gradient: 'from-[#92400e] to-[#d97706]',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        label: 'Clientes',
        value: props.stats.clientes,
        hint: `${props.stats.cupones_activos} cupones activos`,
        href: '/usuarios',
        gradient: 'from-[#d97706] to-[#f59e0b]',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    },
]);

const estadoEtiqueta = {
    pendiente: 'Pendiente',
    pagado: 'Pagado',
    en_preparacion: 'En preparación',
    enviado: 'Enviado',
    entregado: 'Entregado',
    cancelado: 'Cancelado',
};

const estadoClase = {
    pendiente: 'bg-slate-100 text-slate-600',
    pagado: 'bg-emerald-100 text-emerald-700',
    en_preparacion: 'bg-amber-100 text-amber-700',
    enviado: 'bg-blue-100 text-blue-700',
    entregado: 'bg-emerald-100 text-emerald-800',
    cancelado: 'bg-red-100 text-red-700',
};
</script>

<template>
    <Head title="Inicio" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">
                Panel de control
            </h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-8">
            <!-- Hero -->
            <section
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#92400e] to-[#d97706] p-8 text-white shadow-xl shadow-orange-500/20 sm:p-10"
            >
                <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-10 h-56 w-56 rounded-full bg-orange-300/20 blur-2xl"></div>
                <div class="relative">
                    <p class="text-sm font-medium uppercase tracking-widest text-white/70">Bienvenido a tu tienda</p>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight sm:text-4xl">
                        Hola<span v-if="userFirstName">, {{ userFirstName }}</span> 👋
                    </h1>
                    <p class="mt-3 max-w-2xl text-base text-white/85">
                        Este es el panel de <span class="font-semibold">{{ businessName }}</span>. Gestioná tu catálogo,
                        tus pedidos y el crecimiento de tu tienda en línea desde acá.
                    </p>
                </div>
            </section>

            <!-- Stat cards -->
            <section>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <Link
                        v-for="stat in tarjetas"
                        :key="stat.label"
                        :href="stat.href"
                        class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <span
                            :class="['flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-md', stat.gradient]"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon" />
                            </svg>
                        </span>
                        <p class="mt-4 text-3xl font-extrabold text-slate-800">{{ stat.value }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-600">{{ stat.label }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ stat.hint }}</p>
                    </Link>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Últimos pedidos -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800">Últimos pedidos</h3>
                        <Link href="/pedidos" class="text-xs font-semibold text-[#92400e] hover:underline">Ver todos</Link>
                    </div>
                    <div v-if="ultimosPedidos.length" class="mt-4 divide-y divide-slate-100">
                        <Link
                            v-for="pedido in ultimosPedidos"
                            :key="pedido.id"
                            :href="`/pedidos/${pedido.id}`"
                            class="flex items-center justify-between gap-3 py-3 text-sm hover:bg-slate-50"
                        >
                            <div>
                                <p class="font-semibold text-slate-800">{{ pedido.numero_pedido }}</p>
                                <p class="text-xs text-slate-400">{{ pedido.cliente ?? 'Cliente' }}</p>
                            </div>
                            <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', estadoClase[pedido.estado]]">
                                {{ estadoEtiqueta[pedido.estado] ?? pedido.estado }}
                            </span>
                            <span class="w-24 text-right font-semibold text-slate-700">{{ formatoPrecio(pedido.total) }}</span>
                        </Link>
                    </div>
                    <p v-else class="mt-4 text-sm text-slate-400">Todavía no hay pedidos.</p>
                </div>

                <!-- Bajo stock -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800">⚠️ Bajo stock</h3>
                        <Link href="/productos?stock=bajo" class="text-xs font-semibold text-[#92400e] hover:underline">Ver todos</Link>
                    </div>
                    <div v-if="productosBajoStock.length" class="mt-4 space-y-3">
                        <Link
                            v-for="producto in productosBajoStock"
                            :key="producto.id"
                            :href="`/productos/${producto.id}/edit`"
                            class="flex items-center justify-between rounded-xl bg-red-50 px-3 py-2 text-sm hover:bg-red-100"
                        >
                            <span class="truncate font-medium text-red-800">{{ producto.nombre }}</span>
                            <span class="font-bold text-red-700">{{ producto.stock }}</span>
                        </Link>
                    </div>
                    <p v-else class="mt-4 text-sm text-emerald-600">Todo el stock está en buen nivel. ✅</p>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
