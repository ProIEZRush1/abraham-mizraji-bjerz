<script setup>
import { computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    destacados: { type: Array, default: () => [] },
    categorias: { type: Array, default: () => [] },
});

const page = usePage();
const businessName = computed(() => page.props.name ?? 'Abraham Mizraji');

const agregarAlCarrito = (producto) => {
    router.post(
        route('carrito.store'),
        { producto_id: producto.id, cantidad: 1 },
        { preserveScroll: true },
    );
};

const formatoPrecio = (valor) =>
    new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(valor);
</script>

<template>
    <Head :title="`${businessName} — Tienda en línea`" />

    <PublicLayout>
        <!-- Hero -->
        <section class="relative overflow-hidden bg-slate-950">
            <div class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-[#92400e] opacity-30 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -right-16 h-96 w-96 rounded-full bg-[#d97706] opacity-20 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8">
                <p class="text-sm font-semibold uppercase tracking-widest text-[#f0b45a]">Tienda en línea</p>
                <h1 class="mt-4 max-w-2xl text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Bienvenido a {{ businessName }}
                </h1>
                <p class="mt-5 max-w-xl text-lg text-slate-300">
                    Descubrí una selección de productos pensada para vos, con envíos a todo el país y
                    pago simple con Mercado Pago.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <Link
                        href="/tienda"
                        class="rounded-xl bg-gradient-to-r from-[#92400e] to-[#d97706] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:opacity-90"
                    >
                        Ver catálogo
                    </Link>
                </div>
            </div>
        </section>

        <!-- Categorías -->
        <section v-if="categorias.length" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-slate-900">Comprá por categoría</h2>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <Link
                    v-for="categoria in categorias"
                    :key="categoria.id"
                    :href="`/tienda?categoria=${categoria.slug}`"
                    class="group flex flex-col items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center transition hover:-translate-y-0.5 hover:border-[#d97706]/40 hover:shadow-md"
                >
                    <span
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-[#92400e] to-[#d97706] text-lg font-bold text-white"
                    >
                        {{ categoria.nombre.charAt(0) }}
                    </span>
                    <span class="text-sm font-semibold text-slate-700 group-hover:text-[#92400e]">{{ categoria.nombre }}</span>
                    <span class="text-xs text-slate-400">{{ categoria.productos_count }} productos</span>
                </Link>
            </div>
        </section>

        <!-- Destacados -->
        <section class="bg-slate-50 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-slate-900">Productos destacados</h2>
                    <Link href="/tienda" class="text-sm font-semibold text-[#92400e] hover:underline">Ver todo</Link>
                </div>

                <div v-if="destacados.length" class="mt-8 grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4">
                    <div
                        v-for="producto in destacados"
                        :key="producto.id"
                        class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <Link :href="`/tienda/${producto.slug}`" class="block aspect-square overflow-hidden bg-slate-100">
                            <img
                                v-if="producto.imagen_principal"
                                :src="producto.imagen_principal"
                                :alt="producto.nombre"
                                class="h-full w-full object-cover transition group-hover:scale-105"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-4xl text-slate-300">🛍️</div>
                        </Link>
                        <div class="flex flex-1 flex-col p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ producto.categoria }}</p>
                            <Link :href="`/tienda/${producto.slug}`" class="mt-1 text-sm font-semibold text-slate-800 hover:text-[#92400e]">
                                {{ producto.nombre }}
                            </Link>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-base font-bold text-slate-900">{{ formatoPrecio(producto.precio_final) }}</span>
                                <span v-if="producto.precio_oferta" class="text-xs text-slate-400 line-through">
                                    {{ formatoPrecio(producto.precio) }}
                                </span>
                            </div>
                            <button
                                type="button"
                                :disabled="!producto.en_stock"
                                @click="agregarAlCarrito(producto)"
                                class="mt-3 w-full rounded-xl border border-[#d97706]/30 bg-[#92400e]/5 py-2 text-xs font-bold text-[#92400e] transition hover:bg-gradient-to-r hover:from-[#92400e] hover:to-[#d97706] hover:text-white disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                {{ producto.en_stock ? 'Agregar al carrito' : 'Sin stock' }}
                            </button>
                        </div>
                    </div>
                </div>
                <p v-else class="mt-8 text-sm text-slate-500">Todavía no hay productos destacados.</p>
            </div>
        </section>
    </PublicLayout>
</template>
