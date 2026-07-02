<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductoForm from './Partials/ProductoForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    producto: { type: Object, required: true },
    categorias: { type: Array, default: () => [] },
});

const form = useForm({
    categoria_id: props.producto.categoria_id,
    nombre: props.producto.nombre,
    descripcion: props.producto.descripcion,
    precio: props.producto.precio,
    precio_oferta: props.producto.precio_oferta,
    sku: props.producto.sku,
    stock: props.producto.stock,
    stock_minimo: props.producto.stock_minimo,
    imagen_principal: props.producto.imagen_principal,
    imagenes: [...(props.producto.imagenes ?? [])],
    activo: props.producto.activo,
    destacado: props.producto.destacado,
    variantes: (props.producto.variantes ?? []).map((variante) => ({ ...variante })),
});

const submit = () => {
    form.put(route('productos.update', props.producto.id));
};
</script>

<template>
    <Head title="Editar producto" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-bold tracking-tight text-slate-800">Editar producto</h2>
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
                submit-label="Guardar cambios"
                @submit="submit"
            />
        </div>
    </AuthenticatedLayout>
</template>
