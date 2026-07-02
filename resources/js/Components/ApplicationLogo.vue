<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    markSize: {
        type: String,
        default: 'h-10 w-10',
    },
    textSize: {
        type: String,
        default: 'text-2xl',
    },
    showText: {
        type: Boolean,
        default: true,
    },
});

const page = usePage();
const name = computed(() => page.props.name || 'Abraham Mizraji');
const initials = computed(() => {
    const parts = name.value.trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return 'AM';
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});
</script>

<template>
    <div class="flex items-center gap-3">
        <span
            :class="markSize"
            class="relative inline-flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#92400e] to-[#d97706] shadow-lg shadow-orange-500/30"
        >
            <svg
                viewBox="0 0 40 40"
                class="h-3/5 w-3/5 text-white"
                fill="none"
                aria-hidden="true"
            >
                <rect
                    x="9"
                    y="9"
                    width="22"
                    height="22"
                    rx="6"
                    transform="rotate(45 20 20)"
                    fill="currentColor"
                    fill-opacity="0.9"
                />
                <text
                    x="20"
                    y="25"
                    text-anchor="middle"
                    font-size="13"
                    font-weight="700"
                    fill="#92400e"
                    font-family="sans-serif"
                >{{ initials }}</text>
            </svg>
        </span>

        <span
            v-if="showText"
            :class="textSize"
            class="bg-gradient-to-r from-[#92400e] to-[#d97706] bg-clip-text font-extrabold tracking-tight text-transparent"
        >
            {{ name }}
        </span>
    </div>
</template>
