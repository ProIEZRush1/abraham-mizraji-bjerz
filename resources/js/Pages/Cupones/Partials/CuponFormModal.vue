<script setup>
import { computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: { type: Boolean, default: false },
    cupon: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const isEdit = computed(() => !!props.cupon);

const form = useForm({
    codigo: '',
    tipo: 'porcentaje',
    valor: '',
    monto_minimo: '',
    fecha_inicio: '',
    fecha_vencimiento: '',
    uso_maximo: '',
    activo: true,
});

watch(
    () => props.show,
    (open) => {
        if (!open) return;
        form.clearErrors();
        form.codigo = props.cupon?.codigo ?? '';
        form.tipo = props.cupon?.tipo ?? 'porcentaje';
        form.valor = props.cupon?.valor ?? '';
        form.monto_minimo = props.cupon?.monto_minimo ?? '';
        form.fecha_inicio = props.cupon?.fecha_inicio ?? '';
        form.fecha_vencimiento = props.cupon?.fecha_vencimiento ?? '';
        form.uso_maximo = props.cupon?.uso_maximo ?? '';
        form.activo = props.cupon?.activo ?? true;
    },
);

const valorHint = computed(() => (form.tipo === 'porcentaje' ? '%' : '$'));

const close = () => emit('close');

const submit = () => {
    if (isEdit.value) {
        form.put(route('cupones.update', props.cupon.id), {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    } else {
        form.post(route('cupones.store'), {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    }
};
</script>

<template>
    <Modal :show="show" @close="close">
        <form @submit.prevent="submit" class="p-6">
            <h2 class="text-lg font-bold text-slate-800">
                {{ isEdit ? 'Editar cupón' : 'Nuevo cupón' }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Creá códigos de descuento para incentivar las compras.
            </p>

            <div class="mt-5 space-y-4">
                <div>
                    <InputLabel value="Código" />
                    <TextInput
                        v-model="form.codigo"
                        type="text"
                        class="mt-1 block w-full uppercase"
                        autofocus
                        placeholder="VERANO10"
                    />
                    <InputError :message="form.errors.codigo" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Tipo" />
                        <select
                            v-model="form.tipo"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                        >
                            <option value="porcentaje">Porcentaje</option>
                            <option value="monto_fijo">Monto fijo</option>
                        </select>
                        <InputError :message="form.errors.tipo" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel :value="`Valor (${valorHint})`" />
                        <TextInput v-model="form.valor" type="number" min="0" step="0.01" class="mt-1 block w-full" />
                        <InputError :message="form.errors.valor" class="mt-1" />
                    </div>
                </div>

                <div>
                    <InputLabel value="Compra mínima para aplicar (opcional)" />
                    <TextInput v-model="form.monto_minimo" type="number" min="0" step="0.01" class="mt-1 block w-full" />
                    <InputError :message="form.errors.monto_minimo" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Vigencia desde (opcional)" />
                        <TextInput v-model="form.fecha_inicio" type="date" class="mt-1 block w-full" />
                        <InputError :message="form.errors.fecha_inicio" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Vence el (opcional)" />
                        <TextInput v-model="form.fecha_vencimiento" type="date" class="mt-1 block w-full" />
                        <InputError :message="form.errors.fecha_vencimiento" class="mt-1" />
                    </div>
                </div>

                <div>
                    <InputLabel value="Límite de usos (opcional)" />
                    <TextInput v-model="form.uso_maximo" type="number" min="1" step="1" class="mt-1 block w-full" />
                    <InputError :message="form.errors.uso_maximo" class="mt-1" />
                </div>

                <div>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 transition hover:border-slate-300">
                        <input
                            type="checkbox"
                            v-model="form.activo"
                            class="rounded border-slate-300 text-[#92400e] focus:ring-[#92400e]"
                        />
                        <span class="text-sm text-slate-700">Cupón activo</span>
                    </label>
                    <InputError :message="form.errors.activo" class="mt-1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton type="button" @click="close">Cancelar</SecondaryButton>
                <PrimaryButton :disabled="form.processing">
                    {{ isEdit ? 'Guardar cambios' : 'Crear cupón' }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
