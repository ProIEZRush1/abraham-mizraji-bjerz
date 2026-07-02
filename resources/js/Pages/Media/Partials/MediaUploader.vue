<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    // Nombre de la ruta a la que se envía el archivo.
    routeName: {
        type: String,
        default: 'media.store',
    },
});

const emit = defineEmits(['uploaded']);

const fileInput = ref(null);
const isDragging = ref(false);
const uploading = ref(false);
const progress = ref(0);
const errorMessage = ref('');

const openPicker = () => {
    fileInput.value?.click();
};

const upload = (file) => {
    if (!file) return;

    errorMessage.value = '';
    uploading.value = true;
    progress.value = 0;

    router.post(
        route(props.routeName),
        { file },
        {
            forceFormData: true,
            preserveScroll: true,
            onProgress: (event) => {
                if (event?.percentage != null) {
                    progress.value = Math.round(event.percentage);
                }
            },
            onSuccess: () => {
                emit('uploaded');
            },
            onError: (errors) => {
                errorMessage.value =
                    errors?.file || 'No se pudo subir el archivo. Inténtalo de nuevo.';
            },
            onFinish: () => {
                uploading.value = false;
                progress.value = 0;
                if (fileInput.value) fileInput.value.value = '';
            },
        },
    );
};

const onFileChange = (event) => {
    const file = event.target.files?.[0];
    upload(file);
};

const onDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer?.files?.[0];
    upload(file);
};
</script>

<template>
    <div>
        <div
            role="button"
            tabindex="0"
            :class="[
                'flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed px-6 py-10 text-center transition',
                isDragging
                    ? 'border-orange-400 bg-orange-50'
                    : 'border-slate-300 bg-white hover:border-orange-300 hover:bg-slate-50',
            ]"
            @click="openPicker"
            @keydown.enter.prevent="openPicker"
            @keydown.space.prevent="openPicker"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
        >
            <span
                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[#92400e] to-[#d97706] text-white shadow-md"
            >
                <svg
                    class="h-7 w-7"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3-3m0 0l3 3m-3-3v12"
                    />
                </svg>
            </span>

            <p class="mt-4 text-sm font-semibold text-slate-700">
                Arrastra un archivo aquí o
                <span class="text-orange-600">haz clic para seleccionar</span>
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Imágenes, documentos, audio o video — hasta 25 MB
            </p>

            <input
                ref="fileInput"
                type="file"
                class="hidden"
                @change="onFileChange"
            />
        </div>

        <!-- Progreso -->
        <div v-if="uploading" class="mt-4">
            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                <div
                    class="h-full rounded-full bg-gradient-to-r from-[#92400e] to-[#d97706] transition-all"
                    :style="{ width: progress + '%' }"
                ></div>
            </div>
            <p class="mt-2 text-xs text-slate-500">Subiendo… {{ progress }}%</p>
        </div>

        <!-- Error -->
        <p v-if="errorMessage" class="mt-3 text-sm font-medium text-rose-600">
            {{ errorMessage }}
        </p>
    </div>
</template>
