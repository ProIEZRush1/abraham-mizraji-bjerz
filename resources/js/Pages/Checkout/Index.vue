<script setup>
import { computed, ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    items: { type: Array, default: () => [] },
    subtotal: { type: Number, default: 0 },
    direcciones: { type: Array, default: () => [] },
    zonas: { type: Array, default: () => [] },
    mercadoPagoDisponible: { type: Boolean, default: false },
});

const page = usePage();
const flashError = computed(() => page.props.flash?.error ?? null);
const trialLocked = computed(() => page.props.trialLocked === true);

const usarNueva = ref(props.direcciones.length === 0);

const form = useForm({
    direccion_id: props.direcciones.find((d) => d.predeterminada)?.id ?? props.direcciones[0]?.id ?? null,
    direccion_nueva: {
        destinatario: '',
        calle: '',
        numero: '',
        piso_depto: '',
        ciudad: '',
        provincia: '',
        codigo_postal: '',
        telefono: '',
    },
    zona_envio_id: props.zonas[0]?.id ?? null,
    cupon_codigo: '',
});

const zonaSeleccionada = computed(() => props.zonas.find((z) => z.id === form.zona_envio_id));
const costoEnvio = computed(() => zonaSeleccionada.value?.costo ?? 0);
const total = computed(() => props.subtotal + costoEnvio.value);

const formatoPrecio = (valor) =>
    new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(valor);

const enviar = () => {
    if (usarNueva.value) {
        form.transform((data) => ({ ...data, direccion_id: null })).post(route('checkout.store'));
    } else {
        form.transform((data) => ({ ...data, direccion_nueva: null })).post(route('checkout.store'));
    }
};
</script>

<template>
    <Head title="Checkout" />

    <PublicLayout>
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold text-slate-900">Finalizar compra</h1>

            <div
                v-if="trialLocked"
                class="mt-6 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5"
            >
                <span class="text-2xl">🔒</span>
                <div>
                    <p class="text-sm font-bold text-amber-800">Versión de prueba</p>
                    <p class="mt-1 text-sm text-amber-700">
                        La confirmación de compra se activa al confirmar tu proyecto con el anticipo. Mientras tanto podés
                        explorar todo el flujo de checkout con normalidad.
                    </p>
                </div>
            </div>

            <div v-if="flashError && !trialLocked" class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-medium text-red-700">
                {{ flashError }}
            </div>

            <form @submit.prevent="enviar" class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <!-- Dirección -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-base font-bold text-slate-800">Dirección de envío</h2>

                        <div v-if="direcciones.length" class="mt-4 space-y-2">
                            <label
                                v-for="direccion in direcciones"
                                :key="direccion.id"
                                class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 text-sm has-[:checked]:border-[#d97706] has-[:checked]:bg-orange-50"
                            >
                                <input
                                    type="radio"
                                    :value="direccion.id"
                                    v-model="form.direccion_id"
                                    @change="usarNueva = false"
                                    class="mt-0.5 text-[#92400e] focus:ring-[#d97706]"
                                />
                                <span>
                                    <span class="font-semibold text-slate-800">{{ direccion.etiqueta }} — {{ direccion.destinatario }}</span><br />
                                    {{ direccion.calle }} {{ direccion.numero }}{{ direccion.piso_depto ? `, ${direccion.piso_depto}` : '' }},
                                    {{ direccion.ciudad }}, {{ direccion.provincia }} ({{ direccion.codigo_postal }})
                                </span>
                            </label>
                            <button
                                type="button"
                                @click="usarNueva = !usarNueva"
                                class="text-xs font-semibold text-[#92400e] hover:underline"
                            >
                                {{ usarNueva ? 'Usar una dirección guardada' : '+ Usar una dirección nueva' }}
                            </button>
                        </div>

                        <div v-if="usarNueva" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <input v-model="form.direccion_nueva.destinatario" placeholder="Nombre y apellido" class="sm:col-span-2 rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]" />
                            <input v-model="form.direccion_nueva.calle" placeholder="Calle" class="rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]" />
                            <input v-model="form.direccion_nueva.numero" placeholder="Número" class="rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]" />
                            <input v-model="form.direccion_nueva.piso_depto" placeholder="Piso / Depto (opcional)" class="rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]" />
                            <input v-model="form.direccion_nueva.telefono" placeholder="Teléfono" class="rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]" />
                            <input v-model="form.direccion_nueva.ciudad" placeholder="Ciudad" class="rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]" />
                            <input v-model="form.direccion_nueva.provincia" placeholder="Provincia" class="rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]" />
                            <input v-model="form.direccion_nueva.codigo_postal" placeholder="Código postal" class="rounded-lg border-slate-300 text-sm focus:border-[#d97706] focus:ring-[#d97706]" />
                        </div>
                    </div>

                    <!-- Envío -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-base font-bold text-slate-800">Método de envío</h2>
                        <div class="mt-4 space-y-2">
                            <label
                                v-for="zona in zonas"
                                :key="zona.id"
                                class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 p-3 text-sm has-[:checked]:border-[#d97706] has-[:checked]:bg-orange-50"
                            >
                                <span class="flex items-center gap-3">
                                    <input type="radio" :value="zona.id" v-model="form.zona_envio_id" class="text-[#92400e] focus:ring-[#d97706]" />
                                    <span>
                                        <span class="font-semibold text-slate-800">{{ zona.nombre }}</span>
                                        <span v-if="zona.tiempo_estimado" class="text-slate-400"> · {{ zona.tiempo_estimado }}</span>
                                    </span>
                                </span>
                                <span class="font-semibold text-slate-700">{{ formatoPrecio(zona.costo) }}</span>
                            </label>
                            <p v-if="!zonas.length" class="text-sm text-slate-400">No hay zonas de envío configuradas todavía.</p>
                        </div>
                    </div>

                    <!-- Cupón -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-base font-bold text-slate-800">Cupón de descuento</h2>
                        <input
                            v-model="form.cupon_codigo"
                            placeholder="Código de cupón (opcional)"
                            class="mt-3 w-full rounded-lg border-slate-300 text-sm uppercase focus:border-[#d97706] focus:ring-[#d97706]"
                        />
                    </div>
                </div>

                <!-- Resumen -->
                <div class="h-fit rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-800">Resumen</h2>
                    <ul class="mt-4 space-y-1 text-sm text-slate-600">
                        <li v-for="(item, i) in items" :key="i" class="flex justify-between">
                            <span class="truncate">{{ item.nombre }} x{{ item.cantidad }}</span>
                            <span>{{ formatoPrecio(item.subtotal) }}</span>
                        </li>
                    </ul>
                    <div class="mt-4 space-y-2 border-t border-slate-200 pt-4 text-sm">
                        <div class="flex justify-between text-slate-600"><span>Subtotal</span><span>{{ formatoPrecio(subtotal) }}</span></div>
                        <div class="flex justify-between text-slate-600"><span>Envío</span><span>{{ formatoPrecio(costoEnvio) }}</span></div>
                        <div class="flex justify-between text-lg font-bold text-slate-900"><span>Total</span><span>{{ formatoPrecio(total) }}</span></div>
                    </div>

                    <p class="mt-4 text-xs text-slate-400">
                        {{ mercadoPagoDisponible ? 'Pagás de forma segura con Mercado Pago.' : 'El pago con Mercado Pago se activará junto con tu cuenta comercial.' }}
                    </p>

                    <button
                        type="submit"
                        :disabled="form.processing || !zonas.length"
                        class="mt-6 w-full rounded-xl bg-gradient-to-r from-[#92400e] to-[#d97706] py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Confirmar pedido
                    </button>
                </div>
            </form>
        </div>
    </PublicLayout>
</template>
