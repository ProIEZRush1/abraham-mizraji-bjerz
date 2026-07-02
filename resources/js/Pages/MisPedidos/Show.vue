<script setup>
import { computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    pedido: { type: Object, required: true },
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

const estadoInfo = computed(() => ESTADOS[props.pedido.estado] ?? { label: props.pedido.estado, classes: 'bg-slate-100 text-slate-600' });

const formatoPrecio = (valor) =>
    new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(valor);

const direccionCompleta = computed(() => {
    const e = props.pedido.envio ?? {};
    const piso = e.piso_depto ? `, ${e.piso_depto}` : '';
    return `${e.calle ?? ''} ${e.numero ?? ''}${piso}, ${e.ciudad ?? ''}, ${e.provincia ?? ''} (${e.codigo_postal ?? ''})`;
});
</script>

<template>
    <Head title="Detalle de pedido" />

    <PublicLayout>
        <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
            <Link
                :href="route('mis-pedidos.index')"
                class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-[#92400e]"
            >
                ← Volver a mis pedidos
            </Link>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ pedido.numero_pedido }}</h1>
                <span
                    class="rounded-full px-3 py-1 text-xs font-semibold"
                    :class="estadoInfo.classes"
                >
                    {{ estadoInfo.label }}
                </span>
                <span
                    v-if="pedido.cupon_codigo"
                    class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700"
                >
                    Cupón aplicado: {{ pedido.cupon_codigo }}
                </span>
            </div>
            <p class="mt-1 text-sm text-slate-500">Realizado el {{ pedido.created_at }}</p>

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

            <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-4">
                            <h2 class="text-base font-bold text-slate-800">Productos</h2>
                        </div>
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-6 py-3">Producto</th>
                                    <th class="px-6 py-3 text-center">Cantidad</th>
                                    <th class="px-6 py-3 text-right">Precio unit.</th>
                                    <th class="px-6 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(item, idx) in pedido.items" :key="idx">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-800">{{ item.nombre_producto }}</p>
                                        <p v-if="item.variante_nombre" class="text-xs text-slate-500">
                                            {{ item.variante_nombre }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-center text-slate-600">{{ item.cantidad }}</td>
                                    <td class="px-6 py-4 text-right text-slate-600">{{ formatoPrecio(item.precio_unitario) }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-slate-800">{{ formatoPrecio(item.subtotal) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-base font-bold text-slate-800">Dirección de envío</h2>
                        <div class="mt-3 text-sm text-slate-600">
                            <p class="font-semibold text-slate-800">{{ pedido.envio?.destinatario }}</p>
                            <p>{{ direccionCompleta }}</p>
                            <p v-if="pedido.envio?.telefono" class="mt-1">Tel: {{ pedido.envio.telefono }}</p>
                            <p v-if="pedido.envio?.zona" class="mt-1 text-slate-500">Zona de envío: {{ pedido.envio.zona }}</p>
                        </div>
                    </div>
                </div>

                <div class="h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-bold text-slate-800">Resumen</h2>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Subtotal</dt>
                            <dd class="font-medium text-slate-700">{{ formatoPrecio(pedido.subtotal) }}</dd>
                        </div>
                        <div v-if="pedido.descuento > 0" class="flex justify-between">
                            <dt class="text-slate-500">Descuento</dt>
                            <dd class="font-medium text-emerald-600">-{{ formatoPrecio(pedido.descuento) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Envío</dt>
                            <dd class="font-medium text-slate-700">{{ formatoPrecio(pedido.costo_envio) }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-3">
                            <dt class="text-base font-bold text-slate-900">Total</dt>
                            <dd class="text-base font-bold text-slate-900">{{ formatoPrecio(pedido.total) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
