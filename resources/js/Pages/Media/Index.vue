<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MediaUploader from '@/Pages/Media/Partials/MediaUploader.vue';
import { Head, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    files: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const flash = computed(() => page.props.flash?.success ?? '');

const copiedId = ref(null);
const confirmingId = ref(null);

const hasFiles = computed(() => props.files.length > 0);

const refresh = () => {
    router.reload({ only: ['files'] });
};

const copyUrl = async (file) => {
    try {
        await navigator.clipboard.writeText(file.url);
    } catch (e) {
        // Fallback para navegadores sin permiso de portapapeles.
        const input = document.createElement('input');
        input.value = file.url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
    }
    copiedId.value = file.id;
    setTimeout(() => {
        if (copiedId.value === file.id) copiedId.value = null;
    }, 1800);
};

const destroy = (file) => {
    router.delete(route('media.destroy', file.id), {
        preserveScroll: true,
        onFinish: () => {
            confirmingId.value = null;
        },
    });
};

const extensionLabel = (file) =>
    (file.extension || file.name.split('.').pop() || 'archivo').toUpperCase();
</script>

<template>
    <Head title="Archivos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">
                Archivos
            </h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-8">
            <!-- Flash -->
            <div
                v-if="flash"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
            >
                {{ flash }}
            </div>

            <!-- Encabezado + uploader -->
            <section
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
            >
                <div class="mb-6">
                    <h1 class="text-lg font-bold text-slate-800">
                        Biblioteca de archivos
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Sube imágenes y documentos, copia su enlace público y
                        gestiónalos desde un solo lugar.
                    </p>
                </div>

                <MediaUploader @uploaded="refresh" />
            </section>

            <!-- Cuadrícula -->
            <section>
                <div
                    v-if="hasFiles"
                    class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <div
                        v-for="file in files"
                        :key="file.id"
                        class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <!-- Vista previa -->
                        <div
                            class="relative flex h-36 items-center justify-center overflow-hidden bg-slate-100"
                        >
                            <img
                                v-if="file.is_image"
                                :src="file.url"
                                :alt="file.name"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex flex-col items-center gap-2 text-slate-400"
                            >
                                <svg
                                    class="h-10 w-10"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                    />
                                </svg>
                                <span
                                    class="rounded-md bg-slate-200 px-2 py-0.5 text-[10px] font-bold tracking-wide text-slate-600"
                                >
                                    {{ extensionLabel(file) }}
                                </span>
                            </div>
                        </div>

                        <!-- Datos -->
                        <div class="flex flex-1 flex-col p-4">
                            <p
                                class="truncate text-sm font-semibold text-slate-800"
                                :title="file.name"
                            >
                                {{ file.name }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ file.size_human }}
                            </p>

                            <div class="mt-4 flex items-center gap-2">
                                <button
                                    type="button"
                                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-gradient-to-r from-[#92400e] to-[#d97706] px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:opacity-90"
                                    @click="copyUrl(file)"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                        />
                                    </svg>
                                    {{ copiedId === file.id ? '¡Copiado!' : 'Copiar URL' }}
                                </button>

                                <button
                                    v-if="confirmingId !== file.id"
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-2.5 py-2 text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
                                    title="Eliminar"
                                    @click="confirmingId = file.id"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>

                                <button
                                    v-else
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-2.5 py-2 text-xs font-semibold text-white transition hover:bg-rose-700"
                                    title="Confirmar eliminación"
                                    @click="destroy(file)"
                                >
                                    Sí, eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado vacío -->
                <div
                    v-else
                    class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"
                    >
                        <svg
                            class="h-7 w-7"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"
                            />
                        </svg>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-700">
                        Aún no tienes archivos
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        Sube tu primer archivo usando el área de arriba.
                    </p>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
