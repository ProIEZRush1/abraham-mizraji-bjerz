<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
    status: {
        type: String,
        default: null,
    },
});

const form = useForm({
    business_name: props.settings.business_name ?? '',
    logo_url: props.settings.logo_url ?? '',
    brand_color_primary: props.settings.brand_color_primary ?? '#7c3aed',
    brand_color_secondary: props.settings.brand_color_secondary ?? '#c026d3',
    contact_email: props.settings.contact_email ?? '',
    contact_phone: props.settings.contact_phone ?? '',
    contact_address: props.settings.contact_address ?? '',
});

const isHex = (value) => /^#[0-9a-fA-F]{6}$/.test(value || '');

const primary = computed(() =>
    isHex(form.brand_color_primary) ? form.brand_color_primary : '#7c3aed',
);
const secondary = computed(() =>
    isHex(form.brand_color_secondary) ? form.brand_color_secondary : '#c026d3',
);

const previewName = computed(() => form.business_name?.trim() || 'Mi Negocio');

const previewInitials = computed(() => {
    const parts = previewName.value.split(/\s+/).filter(Boolean);
    if (parts.length === 1) {
        return parts[0].substring(0, 2).toUpperCase();
    }
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

const gradientStyle = computed(() => ({
    backgroundImage: `linear-gradient(135deg, ${primary.value}, ${secondary.value})`,
}));

const submit = () => {
    form.put(route('ajustes.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Ajustes y marca" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-slate-800">
                Ajustes y marca
            </h2>
        </template>

        <div class="mx-auto max-w-5xl space-y-6">
            <p class="text-sm text-slate-500">
                Personaliza el nombre, el logo, los colores y los datos de
                contacto de tu negocio. Estos valores se aplican en todo el panel.
            </p>

            <!-- Vista previa de la marca -->
            <div
                class="overflow-hidden rounded-2xl text-white shadow-lg"
                :style="gradientStyle"
            >
                <div class="flex items-center gap-4 p-6">
                    <span
                        v-if="!form.logo_url"
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-white/20 text-lg font-bold backdrop-blur"
                    >
                        {{ previewInitials }}
                    </span>
                    <img
                        v-else
                        :src="form.logo_url"
                        :alt="previewName"
                        class="h-14 w-14 shrink-0 rounded-xl bg-white/20 object-contain p-1"
                    />
                    <div class="min-w-0">
                        <p class="truncate text-xl font-extrabold tracking-tight">
                            {{ previewName }}
                        </p>
                        <p class="text-sm text-white/80">Vista previa de la marca</p>
                    </div>
                </div>
            </div>

            <div
                v-if="status === 'ajustes-guardados'"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
            >
                Ajustes guardados correctamente.
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Identidad -->
                <section class="rounded-2xl bg-white p-6 shadow sm:p-8">
                    <header class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Identidad del negocio
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Nombre que se muestra en el panel y URL pública de tu logo.
                        </p>
                    </header>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel for="business_name" value="Nombre del negocio" />
                            <TextInput
                                id="business_name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.business_name"
                                autocomplete="organization"
                            />
                            <InputError class="mt-2" :message="form.errors.business_name" />
                        </div>

                        <div>
                            <InputLabel for="logo_url" value="URL del logo" />
                            <TextInput
                                id="logo_url"
                                type="url"
                                class="mt-1 block w-full"
                                v-model="form.logo_url"
                                placeholder="https://..."
                            />
                            <InputError class="mt-2" :message="form.errors.logo_url" />
                        </div>
                    </div>
                </section>

                <!-- Colores -->
                <section class="rounded-2xl bg-white p-6 shadow sm:p-8">
                    <header class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Colores de marca
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Define el degradado de tu marca en formato hexadecimal.
                        </p>
                    </header>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel for="brand_color_primary" value="Color primario" />
                            <div class="mt-1 flex items-center gap-3">
                                <input
                                    type="color"
                                    v-model="form.brand_color_primary"
                                    class="h-10 w-12 cursor-pointer rounded-md border border-gray-300 bg-white p-1"
                                    aria-label="Selector de color primario"
                                />
                                <TextInput
                                    id="brand_color_primary"
                                    type="text"
                                    class="block w-full"
                                    v-model="form.brand_color_primary"
                                    placeholder="#7c3aed"
                                />
                            </div>
                            <InputError class="mt-2" :message="form.errors.brand_color_primary" />
                        </div>

                        <div>
                            <InputLabel for="brand_color_secondary" value="Color secundario" />
                            <div class="mt-1 flex items-center gap-3">
                                <input
                                    type="color"
                                    v-model="form.brand_color_secondary"
                                    class="h-10 w-12 cursor-pointer rounded-md border border-gray-300 bg-white p-1"
                                    aria-label="Selector de color secundario"
                                />
                                <TextInput
                                    id="brand_color_secondary"
                                    type="text"
                                    class="block w-full"
                                    v-model="form.brand_color_secondary"
                                    placeholder="#c026d3"
                                />
                            </div>
                            <InputError class="mt-2" :message="form.errors.brand_color_secondary" />
                        </div>
                    </div>
                </section>

                <!-- Contacto -->
                <section class="rounded-2xl bg-white p-6 shadow sm:p-8">
                    <header class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Información de contacto
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Datos de contacto públicos de tu negocio.
                        </p>
                    </header>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel for="contact_email" value="Correo electrónico" />
                            <TextInput
                                id="contact_email"
                                type="email"
                                class="mt-1 block w-full"
                                v-model="form.contact_email"
                                autocomplete="email"
                            />
                            <InputError class="mt-2" :message="form.errors.contact_email" />
                        </div>

                        <div>
                            <InputLabel for="contact_phone" value="Teléfono" />
                            <TextInput
                                id="contact_phone"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.contact_phone"
                                autocomplete="tel"
                            />
                            <InputError class="mt-2" :message="form.errors.contact_phone" />
                        </div>

                        <div class="sm:col-span-2">
                            <InputLabel for="contact_address" value="Dirección" />
                            <TextInput
                                id="contact_address"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.contact_address"
                                autocomplete="street-address"
                            />
                            <InputError class="mt-2" :message="form.errors.contact_address" />
                        </div>
                    </div>
                </section>

                <div class="flex items-center gap-4">
                    <PrimaryButton :disabled="form.processing">
                        Guardar cambios
                    </PrimaryButton>

                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p v-if="form.recentlySuccessful" class="text-sm text-slate-500">
                            Guardado.
                        </p>
                    </Transition>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
