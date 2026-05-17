<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div
        class="min-h-screen flex items-center justify-center bg-linear-to-br from-primary-900 to-primary-950 px-4 py-12"
    >
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-secondary-500 rounded-xl flex items-center justify-center shadow-lg"
                    >
                        <svg
                            class="w-7 h-7 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"
                            />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-white"
                        >ماركت بليس</span
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
