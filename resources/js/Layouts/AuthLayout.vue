<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    title: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <Head :title="title" />
    <div
        class="min-h-screen flex items-center justify-center bg-linear-to-br from-primary-900 to-primary-950 px-4 py-12"
    >
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center gap-3">
                    <img
                        src="/images/logo.svg"
                        alt="ماي واي"
                        class="h-10 w-auto"
                    />
                    <span class="text-2xl font-bold text-white"
                        >ماي واي</span
                    >
                </Link>
            </div>

            <!-- Card Container -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <slot />
            </div>
        </div>
    </div>
</template>
