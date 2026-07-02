<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const open = ref(false);
const items = ref([]);
const noLeidas = ref(0);
const cargando = ref(false);
let intervalo = null;

const tipoColor = (tipo) => {
    switch (tipo) {
        case 'exito':
            return 'bg-emerald-500';
        case 'advertencia':
            return 'bg-amber-500';
        case 'error':
            return 'bg-rose-500';
        default:
            return 'bg-[#92400e]';
    }
};

const badge = computed(() => (noLeidas.value > 9 ? '9+' : String(noLeidas.value)));

const cargar = async () => {
    cargando.value = true;
    try {
        const res = await fetch(route('notificaciones.recientes'), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        if (!res.ok) return;
        const json = await res.json();
        items.value = json?.data?.items ?? [];
        noLeidas.value = json?.data?.no_leidas ?? 0;
    } catch (e) {
        // Degradación elegante: si falla la carga, la campana queda vacía.
    } finally {
        cargando.value = false;
    }
};

const toggle = () => {
    open.value = !open.value;
    if (open.value) cargar();
};

const cerrar = () => {
    open.value = false;
};

const leerTodas = () => {
    router.post(
        route('notificaciones.leer-todas'),
        {},
        {
            preserveScroll: true,
            onSuccess: () => cargar(),
        },
    );
};

onMounted(() => {
    cargar();
    intervalo = window.setInterval(cargar, 60000);
});

onBeforeUnmount(() => {
    if (intervalo) window.clearInterval(intervalo);
});
</script>

<template>
    <div class="relative">
        <button
            type="button"
            @click="toggle"
            class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-800 focus:outline-none"
            aria-label="Notificaciones"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>
            <span
                v-if="noLeidas > 0"
                class="absolute -right-0.5 -top-0.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-gradient-to-br from-[#92400e] to-[#d97706] px-1 text-[10px] font-bold text-white shadow"
            >
                {{ badge }}
            </span>
        </button>

        <!-- Capa para cerrar al hacer clic fuera -->
        <div v-if="open" class="fixed inset-0 z-40" @click="cerrar"></div>

        <transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1"
        >
            <div
                v-if="open"
                class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <p class="text-sm font-bold text-slate-800">Notificaciones</p>
                    <button
                        v-if="noLeidas > 0"
                        type="button"
                        @click="leerTodas"
                        class="text-xs font-semibold text-[#92400e] transition hover:text-[#d97706]"
                    >
                        Marcar todas
                    </button>
                </div>

                <div class="max-h-80 overflow-y-auto">
                    <p
                        v-if="!cargando && items.length === 0"
                        class="px-4 py-8 text-center text-sm text-slate-400"
                    >
                        No tienes notificaciones.
                    </p>

                    <ul v-else class="divide-y divide-slate-50">
                        <li
                            v-for="item in items"
                            :key="item.id"
                            :class="[
                                'flex gap-3 px-4 py-3 transition hover:bg-slate-50',
                                item.leida ? 'opacity-70' : 'bg-orange-50/40',
                            ]"
                        >
                            <span
                                :class="['mt-1.5 h-2 w-2 shrink-0 rounded-full', tipoColor(item.tipo)]"
                            ></span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">
                                    {{ item.titulo }}
                                </p>
                                <p class="line-clamp-2 text-xs text-slate-500">
                                    {{ item.mensaje }}
                                </p>
                                <p class="mt-0.5 text-[11px] text-slate-400">{{ item.fecha }}</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="border-t border-slate-100 px-4 py-3">
                    <Link
                        :href="route('notificaciones.index')"
                        class="block text-center text-sm font-semibold text-[#92400e] transition hover:text-[#d97706]"
                        @click="cerrar"
                    >
                        Ver todas
                    </Link>
                </div>
            </div>
        </transition>
    </div>
</template>
