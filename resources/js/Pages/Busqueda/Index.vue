<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    q: { type: String, default: '' },
    groups: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
});

const term = ref(props.q ?? '');
const searching = ref(false);
let debounce = null;

const hasQuery = computed(() => (props.q ?? '').trim().length > 0);
const hasResults = computed(() => props.groups.length > 0);

const runSearch = (value, options = {}) => {
    searching.value = true;
    router.get(
        '/buscar',
        { q: value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => (searching.value = false),
            ...options,
        },
    );
};

watch(term, (value) => {
    if (debounce) clearTimeout(debounce);
    debounce = setTimeout(() => runSearch(value), 350);
});

const submit = () => {
    if (debounce) clearTimeout(debounce);
    runSearch(term.value, { replace: false });
};

const clear = () => {
    term.value = '';
};

onBeforeUnmount(() => {
    if (debounce) clearTimeout(debounce);
});
</script>

<template>
    <Head title="Búsqueda" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">
                Búsqueda global
            </h2>
        </template>

        <div class="mx-auto max-w-4xl space-y-8">
            <!-- Caja de búsqueda -->
            <section
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#7c3aed] to-[#c026d3] p-7 text-white shadow-xl shadow-fuchsia-500/20 sm:p-9"
            >
                <div
                    class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"
                ></div>
                <div class="relative">
                    <p class="text-sm font-medium uppercase tracking-widest text-white/70">
                        Encuentra lo que necesitas
                    </p>
                    <h1 class="mt-2 text-2xl font-extrabold leading-tight sm:text-3xl">
                        Busca en todo tu sistema 🔍
                    </h1>

                    <form class="mt-6" @submit.prevent="submit">
                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"
                                    />
                                </svg>
                            </span>
                            <input
                                v-model="term"
                                type="search"
                                autofocus
                                placeholder="Escribe para buscar…"
                                class="w-full rounded-xl border-0 bg-white py-3.5 pl-12 pr-28 text-base text-slate-800 shadow-lg shadow-fuchsia-900/10 placeholder:text-slate-400 focus:ring-2 focus:ring-white/70"
                            />
                            <button
                                type="submit"
                                class="absolute inset-y-0 right-0 m-1.5 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800"
                            >
                                Buscar
                            </button>
                        </div>
                    </form>

                    <p class="mt-3 h-5 text-sm text-white/80">
                        <span v-if="searching">Buscando…</span>
                        <span v-else-if="hasQuery">
                            {{ total }}
                            {{ total === 1 ? 'resultado' : 'resultados' }} para
                            “{{ props.q }}”
                        </span>
                    </p>
                </div>
            </section>

            <!-- Resultados -->
            <section v-if="hasResults" class="space-y-6">
                <div
                    v-for="group in groups"
                    :key="group.key"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <header
                        class="flex items-center justify-between border-b border-slate-100 px-6 py-4"
                    >
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800">
                            <span class="text-lg">{{ group.icon }}</span>
                            {{ group.label }}
                        </h3>
                        <span
                            class="rounded-full bg-fuchsia-50 px-2.5 py-0.5 text-xs font-semibold text-[#a21caf]"
                        >
                            {{ group.count }}
                        </span>
                    </header>

                    <ul class="divide-y divide-slate-100">
                        <li v-for="item in group.items" :key="item.id">
                            <component
                                :is="item.url ? Link : 'div'"
                                :href="item.url || undefined"
                                class="flex items-center gap-4 px-6 py-3.5 transition"
                                :class="item.url ? 'hover:bg-slate-50' : ''"
                            >
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#7c3aed] to-[#c026d3] text-white"
                                >
                                    {{ group.icon }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800">
                                        {{ item.title }}
                                    </p>
                                    <p
                                        v-if="item.subtitle"
                                        class="truncate text-xs text-slate-500"
                                    >
                                        {{ item.subtitle }}
                                    </p>
                                </div>
                                <svg
                                    v-if="item.url"
                                    class="ml-auto h-4 w-4 shrink-0 text-slate-300"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </component>
                        </li>
                    </ul>

                    <footer
                        v-if="group.count > group.items.length"
                        class="border-t border-slate-100 px-6 py-2.5 text-xs text-slate-400"
                    >
                        Mostrando {{ group.items.length }} de {{ group.count }}
                    </footer>
                </div>
            </section>

            <!-- Sin resultados -->
            <section
                v-else-if="hasQuery && !searching"
                class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center"
            >
                <p class="text-4xl">🤔</p>
                <h3 class="mt-4 text-lg font-bold text-slate-800">
                    Sin resultados
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    No encontramos nada para “{{ props.q }}”. Intenta con otras palabras.
                </p>
                <button
                    type="button"
                    class="mt-5 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"
                    @click="clear"
                >
                    Limpiar búsqueda
                </button>
            </section>

            <!-- Estado inicial -->
            <section
                v-else
                class="rounded-2xl border border-slate-200 bg-white p-12 text-center"
            >
                <p class="text-4xl">✨</p>
                <h3 class="mt-4 text-lg font-bold text-slate-800">
                    Empieza a escribir
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    Busca en todos los módulos de tu sistema desde un solo lugar.
                </p>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
