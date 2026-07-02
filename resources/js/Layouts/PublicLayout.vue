<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const businessName = computed(() => page.props.name ?? 'Abraham Mizraji');
const user = computed(() => page.props.auth?.user ?? null);
const isAdmin = computed(() => page.props.auth?.isAdmin === true);
const carritoCount = computed(() => page.props.carritoCount ?? 0);
const menuOpen = ref(false);

const nav = [
    { label: 'Inicio', href: '/' },
    { label: 'Tienda', href: '/tienda' },
];
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white">
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-18 max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <Link href="/" class="shrink-0 transition hover:opacity-90">
                    <ApplicationLogo mark-size="h-10 w-10" text-size="text-xl" />
                </Link>

                <nav class="hidden items-center gap-8 md:flex">
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        class="text-sm font-semibold text-slate-600 transition hover:text-[#92400e]"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="flex items-center gap-3">
                    <Link
                        href="/carrito"
                        class="relative inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-600 transition hover:bg-slate-100 hover:text-[#92400e]"
                        aria-label="Carrito"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.94-4.716 2.442-7.203a1.125 1.125 0 00-1.11-1.297H5.106M7.5 14.25L5.106 5.25M9.75 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm9 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                        <span
                            v-if="carritoCount > 0"
                            class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-gradient-to-br from-[#92400e] to-[#d97706] px-1 text-[11px] font-bold text-white"
                        >
                            {{ carritoCount }}
                        </span>
                    </Link>

                    <Dropdown v-if="user" align="right" width="52">
                        <template #trigger>
                            <button
                                type="button"
                                class="hidden items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-3 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:text-slate-900 focus:outline-none sm:inline-flex"
                            >
                                <span
                                    class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-[#92400e] to-[#d97706] text-xs font-bold text-white"
                                >
                                    {{ (user.name || '?').charAt(0).toUpperCase() }}
                                </span>
                                {{ user.name }}
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink v-if="isAdmin" href="/dashboard">Panel de administración</DropdownLink>
                            <DropdownLink href="/mis-pedidos">Mis pedidos</DropdownLink>
                            <DropdownLink href="/mi-cuenta/direcciones">Mis direcciones</DropdownLink>
                            <DropdownLink href="/profile">Mi perfil</DropdownLink>
                            <DropdownLink href="/logout" method="post" as="button">Cerrar sesión</DropdownLink>
                        </template>
                    </Dropdown>
                    <Link
                        v-else
                        href="/login"
                        class="hidden rounded-xl bg-gradient-to-r from-[#92400e] to-[#d97706] px-4 py-2 text-sm font-semibold text-white shadow-md shadow-orange-500/20 transition hover:opacity-90 sm:inline-flex"
                    >
                        Iniciar sesión
                    </Link>

                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 md:hidden"
                        @click="menuOpen = !menuOpen"
                    >
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <div v-if="menuOpen" class="border-t border-slate-200 bg-white md:hidden">
                <div class="space-y-1 px-4 py-3">
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                    >
                        {{ item.label }}
                    </Link>
                    <template v-if="user">
                        <Link v-if="isAdmin" href="/dashboard" class="block rounded-lg px-3 py-2 text-sm font-semibold text-[#92400e] hover:bg-slate-50">
                            Panel de administración
                        </Link>
                        <Link href="/mis-pedidos" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Mis pedidos
                        </Link>
                        <Link href="/mi-cuenta/direcciones" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Mis direcciones
                        </Link>
                        <Link href="/logout" method="post" as="button" class="block w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Cerrar sesión
                        </Link>
                    </template>
                    <Link v-else href="/login" class="block rounded-lg px-3 py-2 text-sm font-semibold text-[#92400e] hover:bg-slate-50">
                        Iniciar sesión
                    </Link>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-slate-200 bg-slate-950 text-slate-300">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-3">
                    <div>
                        <ApplicationLogo mark-size="h-9 w-9" text-size="text-lg" />
                        <p class="mt-3 text-sm text-slate-400">
                            Productos seleccionados con calidad y atención personalizada.
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Ayuda</p>
                        <ul class="mt-3 space-y-2 text-sm text-slate-400">
                            <li><Link href="/tienda" class="hover:text-white">Ver catálogo</Link></li>
                            <li><Link href="/mis-pedidos" class="hover:text-white">Mis pedidos</Link></li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Contacto</p>
                        <p class="mt-3 text-sm text-slate-400">{{ page.props.branding?.contact?.email ?? 'contacto@abrahammizraji.com' }}</p>
                        <p class="text-sm text-slate-400">{{ page.props.branding?.contact?.phone ?? '+54 11 5555-5555' }}</p>
                    </div>
                </div>
                <div class="mt-10 border-t border-slate-800 pt-6 text-center text-xs text-slate-500">
                    &copy; {{ new Date().getFullYear() }} {{ businessName }} &middot; Desarrollado por
                    <span class="font-semibold text-slate-400">Overcloud</span>
                </div>
            </div>
        </footer>
    </div>
</template>
