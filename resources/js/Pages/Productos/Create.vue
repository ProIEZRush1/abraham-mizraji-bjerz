<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductoForm from './Partials/ProductoForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    categorias: { type: Array, default: () => [] },
});

const form = useForm({
    categoria_id: null,
    nombre: '',
    descripcion: '',
    precio: '',
    precio_oferta: '',
    sku: '',
    stock: 0,
    stock_minimo: 5,
    imagen_principal: null,
    imagenes: [],
    activo: true,
    destacado: false,
    variantes: [],
});

const submit = () => {
    form.post(route('productos.store'));
};
</script>

<template>
    <Head title="Nuevo producto" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-bold tracking-tight text-slate-800">Nuevo producto</h2>
                <Link
                    :href="route('productos.index')"
                    class="text-sm font-semibold text-orange-600 hover:text-orange-500"
                >
                    ← Volver
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-4xl space-y-6">
            <ProductoForm
                :form="form"
                :categorias="categorias"
                submit-label="Crear producto"
                @submit="submit"
            />
        </div>
    </AuthenticatedLayout>
</template>
