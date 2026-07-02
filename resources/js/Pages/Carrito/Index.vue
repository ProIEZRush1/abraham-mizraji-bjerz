<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    items: { type: Array, default: () => [] },
    subtotal: { type: Number, default: 0 },
});

const formatoPrecio = (valor) =>
    new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(valor);

const actualizarCantidad = (item, cantidad) => {
    if (cantidad < 1) return;
    router.patch(route('carrito.update', item.id), { cantidad }, { preserveScroll: true });
};

const quitar = (item) => {
    router.delete(route('carrito.destroy', item.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Carrito" />

    <PublicLayout>
        <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold text-slate-900">Tu carrito</h1>

            <div v-if="items.length" class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-2">
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                            <img v-if="item.imagen" :src="item.imagen" :alt="item.nombre" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full items-center justify-center text-2xl text-slate-300">🛍️</div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <Link :href="`/tienda/${item.slug}`" class="truncate font-semibold text-slate-800 hover:text-[#92400e]">
                                {{ item.nombre }}
                            </Link>
                            <p v-if="item.variante" class="text-xs text-slate-400">{{ item.variante }}</p>
                            <p class="text-sm text-slate-500">{{ formatoPrecio(item.precio_unitario) }} c/u</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="actualizarCantidad(item, item.cantidad - 1)"
                                class="h-8 w-8 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50"
                            >
                                −
                            </button>
                            <span class="w-8 text-center text-sm font-semibold">{{ item.cantidad }}</span>
                            <button
                                type="button"
                                :disabled="item.cantidad >= item.stock_disponible"
                                @click="actualizarCantidad(item, item.cantidad + 1)"
                                class="h-8 w-8 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 disabled:opacity-30"
                            >
                                +
                            </button>
                        </div>
                        <p class="w-24 text-right font-bold text-slate-900">{{ formatoPrecio(item.subtotal) }}</p>
                        <button type="button" @click="quitar(item)" class="text-slate-400 hover:text-red-500" aria-label="Quitar">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-800">Resumen</h2>
                    <div class="mt-4 flex justify-between text-sm text-slate-600">
                        <span>Subtotal</span>
                        <span class="font-semibold text-slate-900">{{ formatoPrecio(subtotal) }}</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">El envío se calcula en el checkout.</p>
                    <Link
                        href="/checkout"
                        class="mt-6 block w-full rounded-xl bg-gradient-to-r from-[#92400e] to-[#d97706] py-3 text-center text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition hover:opacity-90"
                    >
                        Ir a pagar
                    </Link>
                </div>
            </div>

            <div v-else class="mt-12 rounded-2xl border border-dashed border-slate-300 p-16 text-center">
                <p class="text-4xl">🛒</p>
                <p class="mt-4 text-sm text-slate-500">Tu carrito está vacío.</p>
                <Link
                    href="/tienda"
                    class="mt-6 inline-block rounded-xl bg-gradient-to-r from-[#92400e] to-[#d97706] px-6 py-3 text-sm font-bold text-white shadow-md transition hover:opacity-90"
                >
                    Ir a la tienda
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
