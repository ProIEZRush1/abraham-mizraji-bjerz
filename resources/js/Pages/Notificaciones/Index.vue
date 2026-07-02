<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NotificacionBell from '@/Pages/Notificaciones/NotificacionBell.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    notificaciones: { type: Object, required: true },
    noLeidas: { type: Number, default: 0 },
});

const tipoEstilo = (tipo) => {
    switch (tipo) {
        case 'exito':
            return { punto: 'bg-emerald-500', chip: 'bg-emerald-50 text-emerald-700', etiqueta: 'Éxito' };
        case 'advertencia':
            return { punto: 'bg-amber-500', chip: 'bg-amber-50 text-amber-700', etiqueta: 'Aviso' };
        case 'error':
            return { punto: 'bg-rose-500', chip: 'bg-rose-50 text-rose-700', etiqueta: 'Error' };
        default:
            return { punto: 'bg-[#92400e]', chip: 'bg-orange-50 text-[#92400e]', etiqueta: 'Info' };
    }
};

const marcarLeida = (id) => {
    router.post(route('notificaciones.leer', id), {}, { preserveScroll: true });
};

const marcarTodas = () => {
    router.post(route('notificaciones.leer-todas'), {}, { preserveScroll: true });
};

const eliminar = (id) => {
    router.delete(route('notificaciones.destroy', id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Notificaciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-bold tracking-tight text-slate-800">Notificaciones</h2>
                <NotificacionBell />
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">
            <!-- Resumen -->
            <section
                class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div class="flex items-center gap-4">
                    <span
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-[#92400e] to-[#d97706] text-white shadow-md"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                            />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Tus notificaciones</p>
                        <p class="text-xs text-slate-500">
                            <span v-if="noLeidas > 0">
                                Tienes <span class="font-semibold text-[#92400e]">{{ noLeidas }}</span>
                                sin leer
                            </span>
                            <span v-else>Estás al día</span>
                        </p>
                    </div>
                </div>
                <button
                    v-if="noLeidas > 0"
                    type="button"
                    @click="marcarTodas"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#92400e] to-[#d97706] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-orange-500/20 transition hover:opacity-90"
                >
                    Marcar todas como leídas
                </button>
            </section>

            <!-- Listado -->
            <section class="space-y-3">
                <div
                    v-if="notificaciones.data.length === 0"
                    class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center"
                >
                    <p class="text-base font-semibold text-slate-700">No tienes notificaciones</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Aquí aparecerán los avisos y actualizaciones de tu negocio.
                    </p>
                </div>

                <article
                    v-for="item in notificaciones.data"
                    :key="item.id"
                    :class="[
                        'rounded-2xl border bg-white p-5 shadow-sm transition',
                        item.leida ? 'border-slate-200' : 'border-orange-200 ring-1 ring-orange-100',
                    ]"
                >
                    <div class="flex items-start gap-4">
                        <span
                            :class="['mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full', tipoEstilo(item.tipo).punto]"
                        ></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-800">{{ item.titulo }}</h3>
                                <span
                                    :class="[
                                        'rounded-full px-2 py-0.5 text-[11px] font-semibold',
                                        tipoEstilo(item.tipo).chip,
                                    ]"
                                >
                                    {{ tipoEstilo(item.tipo).etiqueta }}
                                </span>
                                <span
                                    v-if="!item.leida"
                                    class="rounded-full bg-[#92400e] px-2 py-0.5 text-[11px] font-semibold text-white"
                                >
                                    Nuevo
                                </span>
                            </div>
                            <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ item.mensaje }}</p>
                            <p class="mt-2 text-xs text-slate-400" :title="item.fecha_exacta">
                                {{ item.fecha }}
                            </p>

                            <div class="mt-3 flex flex-wrap items-center gap-4">
                                <a
                                    v-if="item.enlace"
                                    :href="item.enlace"
                                    class="text-xs font-semibold text-[#92400e] transition hover:text-[#d97706]"
                                >
                                    Ver detalle
                                </a>
                                <button
                                    v-if="!item.leida"
                                    type="button"
                                    @click="marcarLeida(item.id)"
                                    class="text-xs font-semibold text-slate-500 transition hover:text-slate-800"
                                >
                                    Marcar como leída
                                </button>
                                <button
                                    type="button"
                                    @click="eliminar(item.id)"
                                    class="text-xs font-semibold text-slate-400 transition hover:text-rose-600"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <!-- Paginación -->
            <nav
                v-if="notificaciones.links.length > 3"
                class="flex flex-wrap items-center justify-center gap-1 pt-2"
            >
                <template v-for="(link, i) in notificaciones.links" :key="i">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        :class="[
                            'min-w-[2.25rem] rounded-lg px-3 py-2 text-center text-sm font-semibold transition',
                            link.active
                                ? 'bg-gradient-to-r from-[#92400e] to-[#d97706] text-white shadow'
                                : 'text-slate-600 hover:bg-slate-100',
                        ]"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="min-w-[2.25rem] rounded-lg px-3 py-2 text-center text-sm font-semibold text-slate-300"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </div>
    </AuthenticatedLayout>
</template>
