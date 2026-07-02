<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    payments: { type: Array, default: () => [] },
    stripeEnabled: { type: Boolean, default: false },
    currency: { type: String, default: 'MXN' },
});

const form = useForm({
    description: '',
    customer_name: '',
    customer_email: '',
    amount: '',
});

const submit = () => {
    form.post(route('pagos.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const formatMoney = (amountInCents, currency) => {
    const value = (Number(amountInCents) || 0) / 100;
    try {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: currency || props.currency || 'MXN',
        }).format(value);
    } catch {
        return value.toFixed(2) + ' ' + (currency || props.currency);
    }
};

const formatDate = (iso) => {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleDateString('es-MX', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return iso;
    }
};

const statusStyles = {
    pagado: 'bg-emerald-100 text-emerald-700',
    pendiente: 'bg-amber-100 text-amber-700',
    cancelado: 'bg-slate-200 text-slate-600',
};

const statusLabel = (status) => {
    const map = { pagado: 'Pagado', pendiente: 'Pendiente', cancelado: 'Cancelado' };
    return map[status] ?? status;
};

const copyLink = async (url) => {
    if (!url) return;
    try {
        await navigator.clipboard.writeText(url);
    } catch {
        window.prompt('Copia el enlace de pago:', url);
    }
};

const hasPayments = computed(() => props.payments.length > 0);
</script>

<template>
    <Head title="Pagos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Pagos</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-8">
            <!-- Aviso: Stripe no configurado -->
            <div
                v-if="!stripeEnabled"
                class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5"
            >
                <span class="text-2xl leading-none">⚠️</span>
                <div>
                    <p class="text-sm font-semibold text-amber-800">
                        Los pagos en línea están desactivados
                    </p>
                    <p class="mt-1 text-sm text-amber-700">
                        Puedes registrar cobros, pero no se generarán enlaces de pago hasta
                        configurar tu cuenta de Stripe (variable
                        <code class="rounded bg-amber-100 px-1 py-0.5 font-mono text-xs">STRIPE_SECRET</code>).
                    </p>
                </div>
            </div>

            <!-- Hero + formulario -->
            <section
                class="grid grid-cols-1 gap-6 lg:grid-cols-5"
            >
                <!-- Resumen -->
                <div
                    class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#92400e] to-[#d97706] p-8 text-white shadow-xl shadow-orange-500/20 lg:col-span-2"
                >
                    <div
                        class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"
                    ></div>
                    <div class="relative">
                        <p class="text-sm font-medium uppercase tracking-widest text-white/70">
                            Cobros en línea
                        </p>
                        <h1 class="mt-3 text-2xl font-extrabold leading-tight">
                            Genera un enlace de pago
                        </h1>
                        <p class="mt-3 text-sm text-white/85">
                            Crea un cobro, comparte el enlace con tu cliente y recibe el pago de
                            forma segura con Stripe. El estado se actualiza automáticamente cuando
                            el cliente paga.
                        </p>
                    </div>
                </div>

                <!-- Formulario -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-3 sm:p-8"
                >
                    <h3 class="text-lg font-bold text-slate-800">Nuevo cobro</h3>
                    <form @submit.prevent="submit" class="mt-5 space-y-5">
                        <div>
                            <InputLabel for="description" value="Concepto" />
                            <TextInput
                                id="description"
                                v-model="form.description"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Ej. Diseño de logotipo"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <InputLabel for="customer_name" value="Nombre del cliente (opcional)" />
                                <TextInput
                                    id="customer_name"
                                    v-model="form.customer_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="Ej. María López"
                                />
                                <InputError class="mt-2" :message="form.errors.customer_name" />
                            </div>

                            <div>
                                <InputLabel for="customer_email" value="Correo del cliente (opcional)" />
                                <TextInput
                                    id="customer_email"
                                    v-model="form.customer_email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    placeholder="cliente@correo.com"
                                />
                                <InputError class="mt-2" :message="form.errors.customer_email" />
                            </div>
                        </div>

                        <div>
                            <InputLabel :for="'amount'" :value="`Monto (${currency})`" />
                            <TextInput
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                min="1"
                                class="mt-1 block w-full"
                                placeholder="0.00"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.amount" />
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton
                                class="!bg-gradient-to-r !from-[#92400e] !to-[#d97706]"
                                :class="{ 'opacity-50': form.processing }"
                                :disabled="form.processing"
                            >
                                {{ stripeEnabled ? 'Generar enlace de pago' : 'Registrar cobro' }}
                            </PrimaryButton>
                            <transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-if="form.recentlySuccessful"
                                    class="text-sm font-medium text-emerald-600"
                                >
                                    Cobro guardado.
                                </p>
                            </transition>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Listado de cobros -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h3 class="text-lg font-bold text-slate-800">Cobros recientes</h3>
                    <p class="mt-0.5 text-xs text-slate-400">
                        Últimos cobros registrados y su estado de pago.
                    </p>
                </div>

                <div v-if="hasPayments" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-6 py-3">Concepto</th>
                                <th class="px-6 py-3">Cliente</th>
                                <th class="px-6 py-3">Monto</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">Fecha</th>
                                <th class="px-6 py-3 text-right">Enlace</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr
                                v-for="payment in payments"
                                :key="payment.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ payment.description }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <div>{{ payment.customer_name || '—' }}</div>
                                    <div v-if="payment.customer_email" class="text-xs text-slate-400">
                                        {{ payment.customer_email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ formatMoney(payment.amount, payment.currency) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                            statusStyles[payment.status] || 'bg-slate-200 text-slate-600',
                                        ]"
                                    >
                                        {{ statusLabel(payment.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ formatDate(payment.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        v-if="payment.checkout_url"
                                        class="inline-flex items-center gap-2"
                                    >
                                        <a
                                            :href="payment.checkout_url"
                                            target="_blank"
                                            rel="noopener"
                                            class="text-sm font-semibold text-[#92400e] hover:underline"
                                        >
                                            Abrir
                                        </a>
                                        <button
                                            type="button"
                                            @click="copyLink(payment.checkout_url)"
                                            class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                        >
                                            Copiar
                                        </button>
                                    </div>
                                    <span v-else class="text-xs text-slate-400">Sin enlace</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-6 py-16 text-center">
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[#92400e] to-[#d97706] text-2xl text-white shadow-lg shadow-orange-500/20"
                    >
                        💳
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-700">
                        Aún no hay cobros
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        Crea tu primer cobro con el formulario de arriba.
                    </p>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
