<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tokens: {
        type: Array,
        default: () => [],
    },
    plainTextToken: {
        type: String,
        default: null,
    },
});

const form = useForm({
    name: '',
    abilities: '',
});

const justCreated = ref(!!props.plainTextToken);
const copied = ref(false);

const baseUrl = computed(() =>
    typeof window !== 'undefined' ? window.location.origin : 'https://tu-dominio',
);

const curlSnippet = computed(
    () =>
        `curl -H "Authorization: Bearer ${props.plainTextToken ?? 'TU_TOKEN'}" \\\n     -H "Accept: application/json" \\\n     ${baseUrl.value}/api/v1/user`,
);

const createToken = () => {
    form.post(route('api.tokens.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            justCreated.value = true;
            copied.value = false;
        },
    });
};

const copyToken = async () => {
    if (!props.plainTextToken) return;
    try {
        await navigator.clipboard.writeText(props.plainTextToken);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch (e) {
        copied.value = false;
    }
};

const dismissToken = () => {
    justCreated.value = false;
};

const tokenToRevoke = ref(null);

const confirmRevoke = (token) => {
    tokenToRevoke.value = token;
};

const closeRevokeModal = () => {
    tokenToRevoke.value = null;
};

const revokeToken = () => {
    if (!tokenToRevoke.value) return;
    router.delete(route('api.tokens.destroy', tokenToRevoke.value.id), {
        preserveScroll: true,
        onFinish: () => closeRevokeModal(),
    });
};

const formatDate = (iso) => {
    if (!iso) return 'Nunca';
    return new Date(iso).toLocaleString('es-MX', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};
</script>

<template>
    <Head title="API" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">
                API y tokens de acceso
            </h2>
        </template>

        <div class="mx-auto max-w-5xl space-y-8">
            <!-- Intro -->
            <section
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#92400e] to-[#d97706] p-8 text-white shadow-xl shadow-orange-500/20"
            >
                <div
                    class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"
                ></div>
                <div class="relative">
                    <p class="text-sm font-medium uppercase tracking-widest text-white/70">
                        Integraciones
                    </p>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight">
                        Conecta tus aplicaciones con la API
                    </h1>
                    <p class="mt-3 max-w-2xl text-base text-white/85">
                        Crea tokens de acceso personal para autenticar tus llamadas a la
                        API REST. Cada token funciona como una contraseña: guárdalo en un
                        lugar seguro y revócalo si dejas de usarlo.
                    </p>
                </div>
            </section>

            <!-- Token recién creado (se muestra una sola vez) -->
            <section
                v-if="justCreated && plainTextToken"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-emerald-900">
                            Tu nuevo token está listo
                        </h3>
                        <p class="mt-1 text-sm text-emerald-700">
                            Cópialo ahora. Por seguridad no volveremos a mostrarlo.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 text-emerald-500 transition hover:text-emerald-700"
                        @click="dismissToken"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <code
                        class="block flex-1 overflow-x-auto rounded-xl border border-emerald-200 bg-white px-4 py-3 font-mono text-sm text-slate-800"
                    >{{ plainTextToken }}</code>
                    <PrimaryButton type="button" @click="copyToken">
                        {{ copied ? '¡Copiado!' : 'Copiar' }}
                    </PrimaryButton>
                </div>
            </section>

            <!-- Crear token -->
            <section class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800">Crear un token</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Asigna un nombre que te ayude a identificar dónde se usará el token.
                </p>

                <form class="mt-6 space-y-5" @submit.prevent="createToken">
                    <div>
                        <InputLabel for="name" value="Nombre del token" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej. Integración con mi tienda"
                            autocomplete="off"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="abilities" value="Permisos (opcional)" />
                        <TextInput
                            id="abilities"
                            v-model="form.abilities"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Déjalo vacío para acceso total, o ej. read,write"
                            autocomplete="off"
                        />
                        <p class="mt-1 text-xs text-slate-400">
                            Separa los permisos con comas. Vacío equivale a acceso total (*).
                        </p>
                        <InputError class="mt-2" :message="form.errors.abilities" />
                    </div>

                    <div class="flex items-center gap-3">
                        <PrimaryButton :disabled="form.processing">
                            Generar token
                        </PrimaryButton>
                        <span v-if="form.processing" class="text-sm text-slate-400">
                            Generando…
                        </span>
                    </div>
                </form>
            </section>

            <!-- Lista de tokens -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-8 py-5">
                    <h3 class="text-lg font-bold text-slate-800">Tokens activos</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Estos tokens tienen acceso a tu cuenta mediante la API.
                    </p>
                </div>

                <div v-if="tokens.length === 0" class="px-8 py-12 text-center">
                    <p class="text-sm text-slate-400">
                        Aún no has creado ningún token.
                    </p>
                </div>

                <ul v-else class="divide-y divide-slate-100">
                    <li
                        v-for="token in tokens"
                        :key="token.id"
                        class="flex flex-col gap-3 px-8 py-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">
                                {{ token.name }}
                            </p>
                            <div class="mt-1 flex flex-wrap gap-1.5">
                                <span
                                    v-for="ability in token.abilities"
                                    :key="ability"
                                    class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700"
                                >
                                    {{ ability }}
                                </span>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-400">
                                Creado: {{ formatDate(token.created_at) }} · Último uso:
                                {{ formatDate(token.last_used_at) }}
                            </p>
                        </div>
                        <DangerButton type="button" @click="confirmRevoke(token)">
                            Revocar
                        </DangerButton>
                    </li>
                </ul>
            </section>

            <!-- Documentación rápida -->
            <section class="rounded-2xl border border-slate-200 bg-slate-900 p-8 text-slate-100 shadow-sm">
                <h3 class="text-lg font-bold">Cómo usar tu token</h3>
                <p class="mt-2 text-sm text-slate-300">
                    Envía el token en la cabecera <code class="rounded bg-slate-800 px-1.5 py-0.5 text-xs">Authorization</code>
                    en cada petición a la API versionada (<code class="rounded bg-slate-800 px-1.5 py-0.5 text-xs">/api/v1</code>).
                </p>
                <pre class="mt-4 overflow-x-auto rounded-xl bg-black/40 p-4 text-xs leading-relaxed text-emerald-200"><code>{{ curlSnippet }}</code></pre>
                <div class="mt-5 space-y-1 text-sm text-slate-300">
                    <p class="font-semibold text-slate-200">Endpoints de ejemplo:</p>
                    <ul class="ml-4 list-disc space-y-1 text-slate-400">
                        <li><span class="font-mono text-slate-200">GET</span> /api/v1/user — usuario autenticado</li>
                        <li><span class="font-mono text-slate-200">GET</span> /api/v1/example — listado</li>
                        <li><span class="font-mono text-slate-200">POST</span> /api/v1/example — crear</li>
                        <li><span class="font-mono text-slate-200">GET</span> /api/v1/example/&#123;id&#125; — detalle</li>
                        <li><span class="font-mono text-slate-200">PUT</span> /api/v1/example/&#123;id&#125; — actualizar</li>
                        <li><span class="font-mono text-slate-200">DELETE</span> /api/v1/example/&#123;id&#125; — eliminar</li>
                    </ul>
                </div>
                <p class="mt-4 text-xs text-slate-500">
                    Respuestas en formato <code class="rounded bg-slate-800 px-1.5 py-0.5">&#123; "data": …, "error": null &#125;</code>.
                </p>
            </section>
        </div>

        <!-- Modal de confirmación de revocación -->
        <Modal :show="tokenToRevoke !== null" @close="closeRevokeModal">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-800">
                    ¿Revocar este token?
                </h2>
                <p class="mt-2 text-sm text-slate-500">
                    El token
                    <span class="font-semibold text-slate-700">{{ tokenToRevoke?.name }}</span>
                    dejará de funcionar de inmediato. Las aplicaciones que lo usen
                    perderán el acceso. Esta acción no se puede deshacer.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeRevokeModal">
                        Cancelar
                    </SecondaryButton>
                    <DangerButton @click="revokeToken">
                        Sí, revocar
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
