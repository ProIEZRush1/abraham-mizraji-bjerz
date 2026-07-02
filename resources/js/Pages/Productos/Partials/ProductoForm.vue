<script setup>
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    form: { type: Object, required: true },
    categorias: { type: Array, default: () => [] },
    submitLabel: { type: String, default: 'Guardar' },
});

const emit = defineEmits(['submit']);

const subiendoPrincipal = ref(false);
const subiendoGaleria = ref(false);
const errorSubida = ref('');

const subirImagen = async (file) => {
    const formData = new FormData();
    formData.append('imagen', file);
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const res = await fetch(route('productos.imagenes'), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        body: formData,
        credentials: 'same-origin',
    });
    if (!res.ok) throw new Error('upload failed');
    const data = await res.json();
    return data.url;
};

const principalInput = ref(null);
const galeriaInput = ref(null);

const abrirSelectorPrincipal = () => principalInput.value?.click();
const abrirSelectorGaleria = () => galeriaInput.value?.click();

const onSeleccionPrincipal = async (event) => {
    const file = event.target.files?.[0];
    if (!file) return;
    errorSubida.value = '';
    subiendoPrincipal.value = true;
    try {
        props.form.imagen_principal = await subirImagen(file);
    } catch (e) {
        errorSubida.value = 'No se pudo subir la imagen. Inténtalo de nuevo.';
    } finally {
        subiendoPrincipal.value = false;
        if (principalInput.value) principalInput.value.value = '';
    }
};

const quitarPrincipal = () => {
    props.form.imagen_principal = null;
};

const onSeleccionGaleria = async (event) => {
    const files = Array.from(event.target.files || []);
    if (!files.length) return;
    errorSubida.value = '';
    subiendoGaleria.value = true;
    try {
        for (const file of files) {
            const url = await subirImagen(file);
            props.form.imagenes.push(url);
        }
    } catch (e) {
        errorSubida.value = 'No se pudo subir alguna imagen. Inténtalo de nuevo.';
    } finally {
        subiendoGaleria.value = false;
        if (galeriaInput.value) galeriaInput.value.value = '';
    }
};

const quitarDeGaleria = (index) => {
    props.form.imagenes.splice(index, 1);
};

const agregarVariante = () => {
    props.form.variantes.push({
        nombre: '',
        sku: '',
        precio_extra: 0,
        stock: 0,
        activo: true,
    });
};

const quitarVariante = (index) => {
    props.form.variantes.splice(index, 1);
};
</script>

<template>
    <form class="space-y-8" @submit.prevent="emit('submit')">
        <!-- Datos generales -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-800">Datos generales</h3>
            <p class="mt-1 text-sm text-slate-500">Información principal del producto.</p>

            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <InputLabel for="nombre" value="Nombre" />
                    <TextInput
                        id="nombre"
                        v-model="form.nombre"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.nombre" />
                </div>

                <div class="sm:col-span-2">
                    <InputLabel for="descripcion" value="Descripción" />
                    <textarea
                        id="descripcion"
                        v-model="form.descripcion"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.descripcion" />
                </div>

                <div>
                    <InputLabel for="categoria_id" value="Categoría" />
                    <select
                        id="categoria_id"
                        v-model="form.categoria_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option :value="null">Sin categoría</option>
                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                            {{ categoria.nombre }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.categoria_id" />
                </div>

                <div>
                    <InputLabel for="sku" value="SKU (opcional)" />
                    <TextInput id="sku" v-model="form.sku" type="text" class="mt-1 block w-full" />
                    <InputError class="mt-2" :message="form.errors.sku" />
                </div>
            </div>
        </section>

        <!-- Precio y stock -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-800">Precio e inventario</h3>
            <p class="mt-1 text-sm text-slate-500">
                Si el producto tiene variantes, el stock se gestiona por variante.
            </p>

            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <InputLabel for="precio" value="Precio (ARS)" />
                    <TextInput
                        id="precio"
                        v-model="form.precio"
                        type="number"
                        min="0"
                        step="0.01"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.precio" />
                </div>

                <div>
                    <InputLabel for="precio_oferta" value="Precio de oferta (opcional)" />
                    <TextInput
                        id="precio_oferta"
                        v-model="form.precio_oferta"
                        type="number"
                        min="0"
                        step="0.01"
                        class="mt-1 block w-full"
                    />
                    <p class="mt-1 text-xs text-slate-400">Debe ser menor al precio normal.</p>
                    <InputError class="mt-2" :message="form.errors.precio_oferta" />
                </div>

                <div>
                    <InputLabel for="stock" value="Stock" />
                    <TextInput
                        id="stock"
                        v-model="form.stock"
                        type="number"
                        min="0"
                        step="1"
                        class="mt-1 block w-full"
                        required
                    />
                    <p class="mt-1 text-xs text-slate-400">
                        Si el producto tiene variantes, el stock se gestiona por variante.
                    </p>
                    <InputError class="mt-2" :message="form.errors.stock" />
                </div>

                <div>
                    <InputLabel for="stock_minimo" value="Alertar cuando el stock sea igual o menor a" />
                    <TextInput
                        id="stock_minimo"
                        v-model="form.stock_minimo"
                        type="number"
                        min="0"
                        step="1"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.stock_minimo" />
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-8">
                <label class="flex items-center gap-2">
                    <Checkbox v-model:checked="form.activo" />
                    <span class="text-sm text-slate-700">Producto activo</span>
                </label>
                <label class="flex items-center gap-2">
                    <Checkbox v-model:checked="form.destacado" />
                    <span class="text-sm text-slate-700">Destacar en la portada de la tienda</span>
                </label>
            </div>
        </section>

        <!-- Imágenes -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-800">Imágenes</h3>
            <p class="mt-1 text-sm text-slate-500">Imagen principal y galería de fotos del producto.</p>

            <div class="mt-6 grid grid-cols-1 gap-8 sm:grid-cols-2">
                <!-- Imagen principal -->
                <div>
                    <InputLabel value="Imagen principal" />
                    <div v-if="form.imagen_principal" class="mt-2 flex items-start gap-4">
                        <img
                            :src="form.imagen_principal"
                            alt="Imagen principal"
                            class="h-28 w-28 rounded-xl object-cover shadow-sm"
                        />
                        <div class="flex flex-col gap-2">
                            <SecondaryButton type="button" @click="abrirSelectorPrincipal">
                                Reemplazar
                            </SecondaryButton>
                            <button
                                type="button"
                                class="text-xs font-semibold text-red-600 hover:text-red-500"
                                @click="quitarPrincipal"
                            >
                                Quitar imagen
                            </button>
                        </div>
                    </div>
                    <div
                        v-else
                        role="button"
                        tabindex="0"
                        class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white px-6 py-8 text-center transition hover:border-orange-300 hover:bg-slate-50"
                        @click="abrirSelectorPrincipal"
                        @keydown.enter.prevent="abrirSelectorPrincipal"
                    >
                        <span
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#92400e] to-[#d97706] text-white shadow-md"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3-3m0 0l3 3m-3-3v12"
                                />
                            </svg>
                        </span>
                        <p class="mt-3 text-sm font-semibold text-slate-700">
                            {{ subiendoPrincipal ? 'Subiendo…' : 'Haz clic para seleccionar una imagen' }}
                        </p>
                    </div>
                    <input
                        ref="principalInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="onSeleccionPrincipal"
                    />
                    <InputError class="mt-2" :message="form.errors.imagen_principal" />
                </div>

                <!-- Galería -->
                <div>
                    <InputLabel value="Galería" />
                    <div class="mt-2 grid grid-cols-3 gap-3">
                        <div
                            v-for="(url, index) in form.imagenes"
                            :key="url + index"
                            class="group relative"
                        >
                            <img :src="url" alt="Imagen de galería" class="h-20 w-20 rounded-xl object-cover shadow-sm" />
                            <button
                                type="button"
                                class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white shadow-md transition hover:bg-red-500"
                                @click="quitarDeGaleria(index)"
                            >
                                ×
                            </button>
                        </div>
                        <div
                            role="button"
                            tabindex="0"
                            class="flex h-20 w-20 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-white text-center transition hover:border-orange-300 hover:bg-slate-50"
                            @click="abrirSelectorGaleria"
                            @keydown.enter.prevent="abrirSelectorGaleria"
                        >
                            <span class="text-xl text-orange-600">+</span>
                            <span class="text-[10px] text-slate-400">{{ subiendoGaleria ? '...' : 'Agregar' }}</span>
                        </div>
                    </div>
                    <input
                        ref="galeriaInput"
                        type="file"
                        accept="image/*"
                        multiple
                        class="hidden"
                        @change="onSeleccionGaleria"
                    />
                </div>
            </div>

            <p v-if="errorSubida" class="mt-4 text-sm font-medium text-rose-600">{{ errorSubida }}</p>
        </section>

        <!-- Variantes -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Variantes</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Talles, colores u otras opciones con su propio stock y costo adicional.
                    </p>
                </div>
                <SecondaryButton type="button" @click="agregarVariante">+ Agregar variante</SecondaryButton>
            </div>

            <div v-if="form.variantes.length" class="mt-6 space-y-4">
                <div
                    v-for="(variante, index) in form.variantes"
                    :key="variante.id ?? `nueva-${index}`"
                    class="grid grid-cols-1 gap-4 rounded-xl border border-slate-200 p-4 sm:grid-cols-12 sm:items-end"
                >
                    <div class="sm:col-span-3">
                        <InputLabel :for="`variante-nombre-${index}`" value="Nombre" />
                        <TextInput
                            :id="`variante-nombre-${index}`"
                            v-model="variante.nombre"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Talle M"
                        />
                    </div>
                    <div class="sm:col-span-3">
                        <InputLabel :for="`variante-sku-${index}`" value="SKU (opcional)" />
                        <TextInput
                            :id="`variante-sku-${index}`"
                            v-model="variante.sku"
                            type="text"
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel :for="`variante-precio-${index}`" value="Costo adicional" />
                        <TextInput
                            :id="`variante-precio-${index}`"
                            v-model="variante.precio_extra"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel :for="`variante-stock-${index}`" value="Stock" />
                        <TextInput
                            :id="`variante-stock-${index}`"
                            v-model="variante.stock"
                            type="number"
                            min="0"
                            step="1"
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div class="flex items-center gap-2 sm:col-span-1">
                        <Checkbox v-model:checked="variante.activo" />
                        <span class="text-xs text-slate-600">Activa</span>
                    </div>
                    <div class="flex justify-end sm:col-span-1">
                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-lg font-bold text-red-600 transition hover:bg-red-50"
                            @click="quitarVariante(index)"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>
            <p v-else class="mt-4 text-sm text-slate-400">
                Este producto no tiene variantes. Se gestiona con el stock general.
            </p>
        </section>

        <div class="flex items-center justify-end gap-3">
            <PrimaryButton type="submit" :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                {{ form.processing ? 'Guardando…' : submitLabel }}
            </PrimaryButton>
        </div>
    </form>
</template>
