<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import CuponFormModal from './Partials/CuponFormModal.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    cupones: { type: Array, default: () => [] },
    flash: { type: Object, default: () => ({}) },
});

const flashSuccess = computed(() => props.flash?.success ?? null);
const flashError = computed(() => props.flash?.error ?? null);

const currency = new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    maximumFractionDigits: 0,
});

const formatValor = (cupon) => (cupon.tipo === 'porcentaje' ? `${cupon.valor}%` : currency.format(cupon.valor));

const formatFecha = (fecha) => {
    if (!fecha) return null;
    const [y, m, d] = fecha.split('-');
    return `${d}/${m}/${y}`;
};

const vigencia = (cupon) => {
    if (!cupon.fecha_inicio && !cupon.fecha_vencimiento) return 'Sin vencimiento';
    const inicio = formatFecha(cupon.fecha_inicio) ?? '—';
    const fin = formatFecha(cupon.fecha_vencimiento) ?? 'Sin vencimiento';
    return `${inicio} – ${fin}`;
};

const isVencido = (cupon) => {
    if (!cupon.fecha_vencimiento) return false;
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    return new Date(`${cupon.fecha_vencimiento}T00:00:00`) < hoy;
};

const cuponModal = ref({ show: false, cupon: null });
const confirm = ref({ show: false, item: null });

const openNew = () => (cuponModal.value = { show: true, cupon: null });
const openEdit = (cupon) => (cuponModal.value = { show: true, cupon });

const askDelete = (item) => (confirm.value = { show: true, item });
const closeConfirm = () => (confirm.value = { show: false, item: null });

const confirmDelete = () => {
    router.delete(route('cupones.destroy', confirm.value.item.id), {
        preserveScroll: true,
        onFinish: closeConfirm,
    });
};
</script>

<template>
    <Head title="Cupones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Cupones</h2>
        </template>

        <div class="mx-auto max-w-6xl space-y-6">
            <div
                v-if="flashSuccess"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-medium text-emerald-800"
            >
                {{ flashSuccess }}
            </div>

            <div
                v-if="flashError"
                class="rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-medium text-red-700"
            >
                {{ flashError }}
            </div>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Cupones de descuento</h3>
                        <p class="text-sm text-slate-500">
                            Incentivá las compras con códigos de descuento por porcentaje o monto fijo.
                        </p>
                    </div>
                    <PrimaryButton @click="openNew">Nuevo cupón</PrimaryButton>
                </div>

                <div v-if="cupones.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Código</th>
                                <th class="px-6 py-3">Tipo</th>
                                <th class="px-6 py-3">Valor</th>
                                <th class="px-6 py-3">Vigencia</th>
                                <th class="px-6 py-3">Usos</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="cupon in cupones" :key="cupon.id">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <code class="rounded-lg bg-amber-50 px-2.5 py-1 font-mono text-xs font-semibold text-amber-800">
                                        {{ cupon.codigo }}
                                    </code>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                    {{ cupon.tipo === 'porcentaje' ? 'Porcentaje' : 'Monto fijo' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-800">
                                    {{ formatValor(cupon) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ vigencia(cupon) }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                    {{ cupon.usos_actuales }} / {{ cupon.uso_maximo ?? '∞' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span
                                            :class="[
                                                'rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                cupon.activo
                                                    ? 'bg-emerald-50 text-emerald-700'
                                                    : 'bg-slate-100 text-slate-500',
                                            ]"
                                        >
                                            {{ cupon.activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                        <span
                                            v-if="isVencido(cupon)"
                                            class="rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-600"
                                        >
                                            Vencido
                                        </span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <SecondaryButton @click="openEdit(cupon)">Editar</SecondaryButton>
                                        <DangerButton @click="askDelete(cupon)">Eliminar</DangerButton>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                    <span class="text-4xl">🏷️</span>
                    <p class="text-sm font-semibold text-slate-700">Todavía no creaste ningún cupón</p>
                    <p class="max-w-sm text-sm text-slate-500">
                        Los cupones son una forma efectiva de premiar a tus clientes e impulsar nuevas ventas.
                    </p>
                    <PrimaryButton @click="openNew">Crear el primer cupón</PrimaryButton>
                </div>
            </section>
        </div>

        <CuponFormModal
            :show="cuponModal.show"
            :cupon="cuponModal.cupon"
            @close="cuponModal.show = false"
        />

        <Modal :show="confirm.show" max-width="md" @close="closeConfirm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-800">Confirmar eliminación</h2>
                <p class="mt-2 text-sm text-slate-500">
                    ¿Seguro que deseas eliminar este cupón? Esta acción no se puede deshacer.
                </p>
                <p v-if="confirm.item" class="mt-3 font-semibold text-slate-700">
                    {{ confirm.item.codigo }}
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeConfirm">Cancelar</SecondaryButton>
                    <DangerButton @click="confirmDelete">Eliminar</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
