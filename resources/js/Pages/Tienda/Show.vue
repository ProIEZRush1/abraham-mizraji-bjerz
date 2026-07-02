<script setup>
import { computed, ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    producto: { type: Object, required: true },
    relacionados: { type: Array, default: () => [] },
});

const varianteId = ref(props.producto.variantes?.[0]?.id ?? null);
const cantidad = ref(1);

const varianteSeleccionada = computed(() =>
    props.producto.variantes?.find((v) => v.id === varianteId.value) ?? null,
);

const stockDisponible = computed(() =>
    varianteSeleccionada.value ? varianteSeleccionada.value.stock : props.producto.stock,
);

const precioMostrado = computed(
    () => props.producto.precio_final + (varianteSeleccionada.value?.precio_extra ?? 0),
);

const agregarAlCarrito = () => {
    router.post(
        route('carrito.store'),
        {
            producto_id: props.producto.id,
            producto_variante_id: varianteId.value,
            cantidad: cantidad.value,
        },
        { preserveScroll: true },
    );
};

const formatoPrecio = (valor) =>
    new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(valor);
</script>

<template>
    <Head :title="producto.nombre" />

    <PublicLayout>
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
            <nav class="mb-6 text-sm text-slate-500">
                <Link href="/tienda" class="hover:text-[#92400e]">Tienda</Link>
                <span v-if="producto.categoria"> / {{ producto.categoria.nombre }}</span>
                <span> / {{ producto.nombre }}</span>
            </nav>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                <div class="aspect-square overflow-hidden rounded-2xl bg-slate-100">
                    <img
                        v-if="producto.imagen_principal"
                        :src="producto.imagen_principal"
                        :alt="producto.nombre"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center text-6xl text-slate-300">🛍️</div>
                </div>

                <div>
                    <p v-if="producto.categoria" class="text-xs font-semibold uppercase tracking-wide text-[#92400e]">
                        {{ producto.categoria.nombre }}
                    </p>
                    <h1 class="mt-2 text-3xl font-extrabold text-slate-900">{{ producto.nombre }}</h1>

                    <div class="mt-4 flex items-baseline gap-3">
                        <span class="text-3xl font-bold text-slate-900">{{ formatoPrecio(precioMostrado) }}</span>
                        <span v-if="producto.precio_oferta" class="text-lg text-slate-400 line-through">
                            {{ formatoPrecio(producto.precio) }}
                        </span>
                    </div>

                    <p class="mt-6 whitespace-pre-line text-sm leading-relaxed text-slate-600">
                        {{ producto.descripcion || 'Sin descripción disponible.' }}
                    </p>

                    <div v-if="producto.variantes?.length" class="mt-6">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Opciones</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button
                                v-for="variante in producto.variantes"
                                :key="variante.id"
                                type="button"
                                @click="varianteId = variante.id"
                                :class="[
                                    'rounded-xl border px-4 py-2 text-sm font-semibold transition',
                                    varianteId === variante.id
                                        ? 'border-[#92400e] bg-[#92400e] text-white'
                                        : 'border-slate-300 text-slate-600 hover:border-[#d97706]',
                                ]"
                            >
                                {{ variante.nombre }}
                            </button>
                        </div>
                    </div>

                    <p class="mt-4 text-sm" :class="stockDisponible > 0 ? 'text-emerald-600' : 'text-red-600'">
                        {{ stockDisponible > 0 ? `${stockDisponible} disponibles` : 'Sin stock' }}
                    </p>

                    <div class="mt-6 flex items-center gap-4">
                        <input
                            v-model.number="cantidad"
                            type="number"
                            min="1"
                            :max="Math.max(stockDisponible, 1)"
                            class="w-20 rounded-lg border-slate-300 text-center text-sm focus:border-[#d97706] focus:ring-[#d97706]"
                        />
                        <button
                            type="button"
                            :disabled="stockDisponible < 1"
                            @click="agregarAlCarrito"
                            class="flex-1 rounded-xl bg-gradient-to-r from-[#92400e] to-[#d97706] py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            {{ stockDisponible > 0 ? 'Agregar al carrito' : 'Sin stock' }}
                        </button>
                    </div>
                </div>
            </div>

            <section v-if="relacionados.length" class="mt-16">
                <h2 class="text-xl font-bold text-slate-900">También te puede interesar</h2>
                <div class="mt-6 grid grid-cols-2 gap-5 sm:grid-cols-4">
                    <Link
                        v-for="rel in relacionados"
                        :key="rel.id"
                        :href="`/tienda/${rel.slug}`"
                        class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div class="aspect-square overflow-hidden bg-slate-100">
                            <img
                                v-if="rel.imagen_principal"
                                :src="rel.imagen_principal"
                                :alt="rel.nombre"
                                class="h-full w-full object-cover transition group-hover:scale-105"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-3xl text-slate-300">🛍️</div>
                        </div>
                        <div class="p-3">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ rel.nombre }}</p>
                            <p class="text-sm font-bold text-slate-900">{{ formatoPrecio(rel.precio_final) }}</p>
                        </div>
                    </Link>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
