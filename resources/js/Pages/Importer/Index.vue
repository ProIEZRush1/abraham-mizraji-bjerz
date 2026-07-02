<script setup>
import { computed, reactive, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    targets: { type: Array, default: () => [] },
    logs: { type: Array, default: () => [] },
    analysis: { type: Object, default: null },
    result: { type: Object, default: null },
});

const hasTargets = computed(() => props.targets.length > 0);

/* ---------------------------------------------------------------------------
 * Paso 1 — Subir archivo y elegir destino
 * ------------------------------------------------------------------------- */
const uploadForm = useForm({
    target: props.targets[0]?.key ?? '',
    has_header: true,
    file: null,
});

const fileInput = ref(null);

const onFileChange = (event) => {
    uploadForm.file = event.target.files[0] ?? null;
};

const submitUpload = () => {
    uploadForm.post(route('importer.analyze'), {
        forceFormData: true,
        preserveScroll: true,
        onError: () => {},
    });
};

/* ---------------------------------------------------------------------------
 * Paso 2 — Mapear columnas
 * ------------------------------------------------------------------------- */
const currentTarget = computed(() => {
    if (!props.analysis) return null;
    return props.targets.find((t) => t.key === props.analysis.target) ?? null;
});

// Opciones de columnas detectadas en el CSV.
const columnOptions = computed(() => {
    if (!props.analysis) return [];
    return props.analysis.headers.map((label, index) => ({ index, label }));
});

const normalize = (value) =>
    String(value ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]/g, '');

const mapping = reactive({});

// Auto-asigna columnas a campos comparando nombres/etiquetas.
const buildAutoMapping = () => {
    Object.keys(mapping).forEach((k) => delete mapping[k]);
    if (!props.analysis || !currentTarget.value) return;

    currentTarget.value.fields.forEach((field) => {
        const fieldKeys = [normalize(field.name), normalize(field.label)];
        const match = columnOptions.value.find((col) =>
            fieldKeys.includes(normalize(col.label)),
        );
        mapping[field.name] = match ? String(match.index) : '';
    });
};

watch(
    () => props.analysis,
    () => buildAutoMapping(),
    { immediate: true },
);

const importForm = useForm({
    token: '',
    target: '',
    has_header: true,
    mapping: {},
});

const submitImport = () => {
    importForm.token = props.analysis.token;
    importForm.target = props.analysis.target;
    importForm.has_header = props.analysis.has_header;
    importForm.mapping = { ...mapping };

    importForm.post(route('importer.import'), {
        preserveScroll: true,
        onError: () => {},
    });
};

const cancelMapping = () => {
    router.get(route('importer.index'));
};

const startOver = () => {
    uploadForm.reset('file');
    if (fileInput.value) fileInput.value.value = '';
    router.get(route('importer.index'));
};
</script>

<template>
    <Head title="Importar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">
                Importación de datos
            </h2>
        </template>

        <div class="mx-auto max-w-5xl space-y-8">
            <!-- Encabezado -->
            <section
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#92400e] to-[#d97706] p-8 text-white shadow-xl shadow-orange-500/20"
            >
                <div
                    class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"
                ></div>
                <div class="relative">
                    <p class="text-sm font-medium uppercase tracking-widest text-white/70">
                        Importar CSV
                    </p>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight">
                        Carga masiva de información 📤
                    </h1>
                    <p class="mt-3 max-w-2xl text-base text-white/85">
                        Sube un archivo CSV, asigna cada columna al campo correcto y
                        guarda cientos de registros en segundos.
                    </p>
                </div>
            </section>

            <!-- Resultado de la última importación -->
            <section
                v-if="result"
                class="rounded-2xl border p-6 shadow-sm"
                :class="
                    result.failed > 0
                        ? 'border-amber-200 bg-amber-50'
                        : 'border-emerald-200 bg-emerald-50'
                "
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">
                            {{ result.failed > 0 ? 'Importación completada con avisos' : 'Importación completada' }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-600">
                            Destino:
                            <span class="font-semibold">{{ result.target_label }}</span>
                        </p>
                        <div class="mt-4 flex flex-wrap gap-6">
                            <div>
                                <p class="text-2xl font-extrabold text-slate-800">{{ result.total }}</p>
                                <p class="text-xs text-slate-500">Filas procesadas</p>
                            </div>
                            <div>
                                <p class="text-2xl font-extrabold text-emerald-600">{{ result.imported }}</p>
                                <p class="text-xs text-slate-500">Importadas</p>
                            </div>
                            <div>
                                <p class="text-2xl font-extrabold text-rose-600">{{ result.failed }}</p>
                                <p class="text-xs text-slate-500">Con errores</p>
                            </div>
                        </div>
                        <ul
                            v-if="result.errors && result.errors.length"
                            class="mt-4 max-h-40 space-y-1 overflow-y-auto rounded-lg bg-white/70 p-3 text-xs text-slate-600"
                        >
                            <li v-for="(err, i) in result.errors" :key="i">• {{ err }}</li>
                        </ul>
                    </div>
                    <PrimaryButton type="button" @click="startOver">
                        Nueva importación
                    </PrimaryButton>
                </div>
            </section>

            <!-- Sin destinos configurados -->
            <section
                v-if="!hasTargets"
                class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm"
            >
                <p class="text-4xl">🗂️</p>
                <h3 class="mt-3 text-lg font-bold text-slate-800">
                    Aún no hay destinos de importación
                </h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                    Configura los destinos importables en
                    <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">config/importer.php</code>
                    para empezar a cargar datos desde archivos CSV.
                </p>
            </section>

            <!-- Paso 1: subir archivo -->
            <section
                v-else-if="!analysis"
                class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                <h3 class="text-lg font-bold text-slate-800">1. Elige el destino y sube tu CSV</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Selecciona dónde se guardarán los datos y carga tu archivo.
                </p>

                <form class="mt-6 space-y-6" @submit.prevent="submitUpload">
                    <div>
                        <InputLabel for="target" value="Destino" />
                        <select
                            id="target"
                            v-model="uploadForm.target"
                            class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-[#92400e] focus:ring-[#92400e]"
                        >
                            <option v-for="t in targets" :key="t.key" :value="t.key">
                                {{ t.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="uploadForm.errors.target" />
                    </div>

                    <div>
                        <InputLabel for="file" value="Archivo CSV" />
                        <input
                            id="file"
                            ref="fileInput"
                            type="file"
                            accept=".csv,text/csv"
                            class="mt-1 block w-full rounded-xl border border-slate-300 text-sm text-slate-600 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-gradient-to-r file:from-amber-600 file:to-orange-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:from-amber-500 hover:file:to-orange-500"
                            @change="onFileChange"
                        />
                        <InputError class="mt-2" :message="uploadForm.errors.file" />
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input
                            v-model="uploadForm.has_header"
                            type="checkbox"
                            class="rounded border-slate-300 text-[#92400e] focus:ring-[#92400e]"
                        />
                        La primera fila contiene los nombres de las columnas
                    </label>

                    <div class="flex justify-end">
                        <PrimaryButton :disabled="uploadForm.processing || !uploadForm.file">
                            Previsualizar
                        </PrimaryButton>
                    </div>
                </form>
            </section>

            <!-- Paso 2: mapear columnas -->
            <section
                v-else
                class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">2. Asigna las columnas</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Archivo
                            <span class="font-semibold text-slate-700">{{ analysis.filename }}</span>
                            · {{ analysis.total }} fila(s) detectada(s) ·
                            destino
                            <span class="font-semibold text-slate-700">{{ currentTarget?.label }}</span>
                        </p>
                    </div>
                    <SecondaryButton type="button" @click="cancelMapping">Cancelar</SecondaryButton>
                </div>

                <InputError class="mt-3" :message="importForm.errors.mapping" />

                <!-- Mapeo -->
                <div class="mt-6 space-y-4">
                    <div
                        v-for="field in currentTarget?.fields ?? []"
                        :key="field.name"
                        class="grid grid-cols-1 items-center gap-3 sm:grid-cols-2"
                    >
                        <div>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ field.label }}
                                <span v-if="field.required" class="text-rose-500">*</span>
                            </p>
                            <p class="text-xs text-slate-400">{{ field.name }}</p>
                        </div>
                        <select
                            v-model="mapping[field.name]"
                            class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-[#92400e] focus:ring-[#92400e]"
                        >
                            <option value="">— Sin asignar —</option>
                            <option
                                v-for="col in columnOptions"
                                :key="col.index"
                                :value="String(col.index)"
                            >
                                {{ col.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Previsualización de muestra -->
                <div v-if="analysis.sample.length" class="mt-8">
                    <p class="mb-2 text-sm font-semibold text-slate-700">Muestra del archivo</p>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        v-for="(h, i) in analysis.headers"
                                        :key="i"
                                        class="px-3 py-2 text-left font-semibold text-slate-600"
                                    >
                                        {{ h }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(row, ri) in analysis.sample" :key="ri">
                                    <td
                                        v-for="(cell, ci) in row"
                                        :key="ci"
                                        class="whitespace-nowrap px-3 py-2 text-slate-600"
                                    >
                                        {{ cell }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="cancelMapping">Cancelar</SecondaryButton>
                    <PrimaryButton :disabled="importForm.processing" @click="submitImport">
                        Importar {{ analysis.total }} fila(s)
                    </PrimaryButton>
                </div>
            </section>

            <!-- Historial -->
            <section
                v-if="logs.length"
                class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                <h3 class="text-lg font-bold text-slate-800">Importaciones recientes</h3>
                <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-slate-600">Fecha</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-600">Destino</th>
                                <th class="px-4 py-2 text-right font-semibold text-slate-600">Total</th>
                                <th class="px-4 py-2 text-right font-semibold text-slate-600">Importadas</th>
                                <th class="px-4 py-2 text-right font-semibold text-slate-600">Errores</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="log in logs" :key="log.id">
                                <td class="whitespace-nowrap px-4 py-2 text-slate-500">{{ log.created_at }}</td>
                                <td class="px-4 py-2 font-medium text-slate-700">{{ log.target }}</td>
                                <td class="px-4 py-2 text-right text-slate-600">{{ log.total }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-emerald-600">{{ log.imported }}</td>
                                <td class="px-4 py-2 text-right font-semibold" :class="log.failed > 0 ? 'text-rose-600' : 'text-slate-400'">
                                    {{ log.failed }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
