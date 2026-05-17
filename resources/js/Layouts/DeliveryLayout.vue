<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const notificationsCount = computed(() => page.props.notificationsCount || 0);

defineProps({
    title: {
        type: String,
        default: "التوصيلات",
    },
});

function logout() {
    router.post("/logout");
}
</script>

<template>
    <div class="min-h-screen bg-gray-50" dir="rtl">
        <!-- Top Bar -->
        <header class="bg-primary-900 text-white sticky top-0 z-30 shadow-md">
            <div
                class="flex items-center justify-between px-4 sm:px-6 h-16 max-w-7xl mx-auto"
            >
                <!-- Logo & Title -->
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 bg-secondary-500 rounded-lg flex items-center justify-center"
                    >
                        <svg
                            class="w-5 h-5 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"
                            />
                        </svg>
                    </div>
                    <span class="text-lg font-bold">{{ title }}</span>
                </div>

                <!-- Right Section -->
                <div class="flex items-center gap-3">
                    <!-- Notifications -->
                    <Link
                        href="/notifications"
                        class="relative p-2 text-white/80 hover:text-white transition-colors"
                        aria-label="الإشعارات"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                            />
                        </svg>
                        <span
                            v-if="notificationsCount > 0"
                            class="absolute -top-0.5 -inset-e-0.5 bg-secondary-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"
                        >
                            {{
                                notificationsCount > 9
                                    ? "9+"
                                    : notificationsCount
                            }}
                        </span>
                    </Link>

                    <!-- User Name -->
                    <span
                        v-if="user"
                        class="text-sm font-medium text-white/90 hidden sm:block"
                    >
                        {{ user.name }}
                    </span>

                    <!-- Logout -->
                    <button
                        @click="logout"
                        class="p-2 text-white/80 hover:text-white transition-colors"
                        aria-label="تسجيل الخروج"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-4 sm:p-6 max-w-7xl mx-auto">
            <slot />
        </main>
    </div>
</template>
