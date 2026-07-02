<script setup>
import { reactive } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    productos: { type: Object, required: true },
    categorias: { type: Array, default: () => [] },
    filtros: { type: Object, default: () => ({}) },
});

const filtros = reactive({
    buscar: props.filtros.buscar ?? '',
    categoria: props.filtros.categoria ?? '',
    precio_min: props.filtros.precio_min ?? '',
    precio_max: props.filtros.precio_max ?? '',
    disponibilidad: props.filtros.disponibilidad ?? '',
    orden: props.filtros.orden ?? '',
});

const aplicarFiltros = () => {
    router.get(route('tienda.index'), { ...filtros }, { preserveState: true, replace: true });
};

const agregarAlCarrito = (producto) => {
    router.post(route('carrito.store'), { producto_id: producto.id, cantidad: 1 }, { preserveScroll: true });
};

const formatoPrecio = (valor) =>
    new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(valor);
</script>

<template>
    <Head title="Tienda" />

    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold text-slate-900">Nuestra tienda</h1>

            <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-4">
                <!-- Filtros -->
                <aside class="space-y-6 lg:col-span-1">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Buscar</label>
                        <input
                            v-model="filtros.buscar"
                            type="text"
                            placeholder="Nombre del producto"
                            @keyup.enter="aplicarFiltros"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]"
                        />
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Categoría</label>
                        <select
                            v-model="filtros.categoria"
                            @change="aplicarFiltros"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]"
                        >
                            <option value="">Todas</option>
                            <option v-for="c in categorias" :key="c.id" :value="c.slug">{{ c.nombre }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Precio</label>
                        <div class="mt-1 flex gap-2">
                            <input
                                v-model="filtros.precio_min"
                                type="number"
                                min="0"
                                placeholder="Mín"
                                @keyup.enter="aplicarFiltros"
                                class="w-1/2 rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]"
                            />
                            <input
                                v-model="filtros.precio_max"
                                type="number"
                                min="0"
                                placeholder="Máx"
                                @keyup.enter="aplicarFiltros"
                                class="w-1/2 rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input
                                type="checkbox"
                                :checked="filtros.disponibilidad === 'en_stock'"
                                @change="filtros.disponibilidad = $event.target.checked ? 'en_stock' : ''; aplicarFiltros()"
                                class="rounded border-slate-300 text-[#92400e] focus:ring-[#d97706]"
                            />
                            Solo disponibles
                        </label>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ordenar por</label>
                        <select
                            v-model="filtros.orden"
                            @change="aplicarFiltros"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]"
                        >
                            <option value="">Más recientes</option>
                            <option value="precio_asc">Precio: menor a mayor</option>
                            <option value="precio_desc">Precio: mayor a menor</option>
                        </select>
                    </div>

                    <button
                        type="button"
                        @click="aplicarFiltros"
                        class="w-full rounded-xl bg-gradient-to-r from-[#92400e] to-[#d97706] py-2.5 text-sm font-bold text-white shadow-md transition hover:opacity-90"
                    >
                        Aplicar filtros
                    </button>
                </aside>

                <!-- Resultados -->
                <div class="lg:col-span-3">
                    <p class="mb-4 text-sm text-slate-500">{{ productos.total }} productos encontrados</p>

                    <div v-if="productos.data.length" class="grid grid-cols-2 gap-5 sm:grid-cols-3">
                        <div
                            v-for="producto in productos.data"
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
                    <p v-else class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                        No encontramos productos con esos filtros.
                    </p>

                    <div v-if="productos.links?.length > 3" class="mt-8 flex flex-wrap gap-2">
                        <Link
                            v-for="link in productos.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            preserve-scroll
                            :class="[
                                'rounded-lg px-3 py-1.5 text-sm',
                                link.active ? 'bg-gradient-to-r from-[#92400e] to-[#d97706] text-white' : 'bg-slate-100 text-slate-600',
                                !link.url && 'pointer-events-none opacity-40',
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
