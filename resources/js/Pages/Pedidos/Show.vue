<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    pedido: { type: Object, required: true },
    estados: { type: Array, default: () => [] },
});

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

const nuevoEstado = ref(props.pedido.estado);
const guardando = ref(false);

const guardarEstado = () => {
    guardando.value = true;
    router.patch(
        route('pedidos.estado', props.pedido.id),
        { estado: nuevoEstado.value },
        {
            preserveScroll: true,
            onFinish: () => {
                guardando.value = false;
            },
        },
    );
};

const direccionCompleta = computed(() => {
    const e = props.pedido.envio ?? {};
    const calle = [e.calle, e.numero].filter(Boolean).join(' ');
    return { calle, pisoDepto: e.piso_depto, ciudadProvincia: [e.ciudad, e.provincia].filter(Boolean).join(', ') };
});
</script>

<template>
    <Head :title="`Pedido ${pedido.numero_pedido}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold tracking-tight text-slate-800">
                    Pedido {{ pedido.numero_pedido }}
                </h2>
                <span
                    :class="[
                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset',
                        estadoBadge(pedido.estado),
                    ]"
                >
                    {{ estadoLabel(pedido.estado) }}
                </span>
            </div>
        </template>

        <div class="mx-auto max-w-6xl space-y-6">
            <div class="flex items-center justify-between">
                <Link
                    :href="route('pedidos.index')"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-700"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a pedidos
                </Link>
                <p class="text-sm text-slate-500">Creado el {{ pedido.created_at }}</p>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Columna principal -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Items -->
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-4">
                            <h3 class="text-base font-bold text-slate-800">Productos</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        <th class="px-6 py-3">Producto</th>
                                        <th class="px-6 py-3 text-right">Precio</th>
                                        <th class="px-6 py-3 text-right">Cantidad</th>
                                        <th class="px-6 py-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="(item, index) in pedido.items" :key="index">
                                        <td class="px-6 py-3">
                                            <p class="font-semibold text-slate-800">{{ item.nombre_producto }}</p>
                                            <p v-if="item.variante_nombre" class="text-xs text-slate-400">
                                                {{ item.variante_nombre }}
                                            </p>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-3 text-right text-slate-600">
                                            {{ formatMoney(item.precio_unitario) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-3 text-right text-slate-600">
                                            {{ item.cantidad }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-3 text-right font-semibold text-slate-800">
                                            {{ formatMoney(item.subtotal) }}
                                        </td>
                                    </tr>
                                    <tr v-if="!pedido.items || pedido.items.length === 0">
                                        <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-400">
                                            Este pedido no tiene productos cargados.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Resumen -->
                        <div class="border-t border-slate-100 px-6 py-5">
                            <div class="ml-auto max-w-xs space-y-2 text-sm">
                                <div class="flex justify-between text-slate-600">
                                    <span>Subtotal</span>
                                    <span>{{ formatMoney(pedido.subtotal) }}</span>
                                </div>
                                <div v-if="pedido.descuento > 0" class="flex justify-between text-emerald-600">
                                    <span>Descuento</span>
                                    <span>-{{ formatMoney(pedido.descuento) }}</span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Envío</span>
                                    <span>{{ formatMoney(pedido.costo_envio) }}</span>
                                </div>
                                <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-bold text-slate-900">
                                    <span>Total</span>
                                    <span>{{ formatMoney(pedido.total) }}</span>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                    Pago: {{ pedido.metodo_pago }}
                                </span>
                                <span
                                    v-if="pedido.cupon_codigo"
                                    class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700"
                                >
                                    Cupón: {{ pedido.cupon_codigo }}
                                </span>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Columna lateral -->
                <div class="space-y-6">
                    <!-- Cliente -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-bold text-slate-800">Cliente</h3>
                        <p class="mt-3 text-sm font-semibold text-slate-700">
                            {{ pedido.cliente?.nombre ?? 'Invitado' }}
                        </p>
                        <p class="text-sm text-slate-500">{{ pedido.cliente?.email ?? '—' }}</p>
                    </section>

                    <!-- Envío -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-bold text-slate-800">Dirección de envío</h3>
                        <div class="mt-3 space-y-1 text-sm text-slate-600">
                            <p class="font-semibold text-slate-700">{{ pedido.envio?.destinatario }}</p>
                            <p>{{ direccionCompleta.calle }}</p>
                            <p v-if="direccionCompleta.pisoDepto">{{ direccionCompleta.pisoDepto }}</p>
                            <p>{{ direccionCompleta.ciudadProvincia }}</p>
                            <p v-if="pedido.envio?.codigo_postal">CP {{ pedido.envio.codigo_postal }}</p>
                            <p v-if="pedido.envio?.zona" class="pt-2 text-slate-500">
                                Zona: {{ pedido.envio.zona }}
                            </p>
                            <p v-if="pedido.envio?.telefono" class="text-slate-500">
                                Tel: {{ pedido.envio.telefono }}
                            </p>
                        </div>
                    </section>

                    <!-- Cambiar estado -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-bold text-slate-800">Estado del pedido</h3>
                        <span
                            :class="[
                                'mt-3 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset',
                                estadoBadge(pedido.estado),
                            ]"
                        >
                            {{ estadoLabel(pedido.estado) }}
                        </span>

                        <div class="mt-4">
                            <label class="mb-1 block text-xs font-semibold text-slate-500">
                                Cambiar a
                            </label>
                            <select
                                v-model="nuevoEstado"
                                class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-[#92400e] focus:ring-[#92400e]"
                            >
                                <option v-for="estado in estados" :key="estado" :value="estado">
                                    {{ estadoLabel(estado) }}
                                </option>
                            </select>
                        </div>

                        <PrimaryButton
                            class="mt-4 w-full justify-center"
                            :disabled="guardando || nuevoEstado === pedido.estado"
                            @click="guardarEstado"
                        >
                            {{ guardando ? 'Guardando…' : 'Guardar' }}
                        </PrimaryButton>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
