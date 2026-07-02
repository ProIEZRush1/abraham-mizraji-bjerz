<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    pedido: { type: Object, required: true },
});

const formatoPrecio = (valor) =>
    new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(valor);
</script>

<template>
    <Head title="Pedido confirmado" />

    <PublicLayout>
        <div class="mx-auto max-w-lg px-4 py-20 text-center sm:px-6">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-3xl">✅</div>
            <h1 class="mt-6 text-2xl font-extrabold text-slate-900">¡Gracias por tu compra!</h1>
            <p class="mt-2 text-sm text-slate-500">
                Tu pedido <span class="font-semibold text-slate-700">{{ pedido.numero_pedido }}</span> fue registrado
                por un total de {{ formatoPrecio(pedido.total) }}.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <Link
                    href="/mis-pedidos"
                    class="rounded-xl bg-gradient-to-r from-[#92400e] to-[#d97706] px-6 py-3 text-sm font-bold text-white shadow-md transition hover:opacity-90"
                >
                    Ver mis pedidos
                </Link>
                <Link
                    href="/tienda"
                    class="rounded-xl border border-slate-300 px-6 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50"
                >
                    Seguir comprando
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
