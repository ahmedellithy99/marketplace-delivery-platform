<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const notificationsCount = computed(() => page.props.notificationsCount || 0);
const sidebarOpen = ref(false);

defineProps({
    title: {
        type: String,
        default: "لوحة التحكم",
    },
});

const navLinks = [
    { href: "/admin/dashboard", label: "لوحة التحكم", icon: "dashboard" },
    { href: "/admin/stores", label: "المتاجر", icon: "store" },
    { href: "/admin/categories", label: "الأقسام", icon: "category" },
    { href: "/admin/products", label: "المنتجات", icon: "product" },
    { href: "/admin/orders", label: "الطلبات", icon: "order" },
    { href: "/admin/deliveries", label: "التوصيلات", icon: "delivery" },
];

function isActive(href) {
    return page.url.startsWith(href);
}

function logout() {
    router.post("/logout");
}
</script>

<template>
    <div class="min-h-screen flex bg-gray-50" dir="rtl">
        <!-- Mobile Overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 right-0 z-50 w-64 bg-primary-900 text-white flex flex-col transform transition-transform duration-300 lg:translate-x-0 lg:static lg:z-auto"
            :class="
                sidebarOpen
                    ? 'translate-x-0'
                    : 'translate-x-full lg:translate-x-0'
            "
        >
            <!-- Logo -->
            <div class="p-6 border-b border-primary-800">
                <Link href="/admin/dashboard" class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-secondary-500 rounded-lg flex items-center justify-center"
                    >
                        <svg
                            class="w-6 h-6 text-white"
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
                    <span class="text-lg font-bold">ماركت بليس</span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
                <Link
                    v-for="link in navLinks"
                    :key="link.href"
                    :href="link.href"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors"
                    :class="
                        isActive(link.href)
                            ? 'bg-white text-primary-900'
                            : 'text-white/80 hover:bg-primary-800 hover:text-white'
                    "
                    @click="sidebarOpen = false"
                >
                    <!-- Dashboard Icon -->
                    <svg
                        v-if="link.icon === 'dashboard'"
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                        />
                    </svg>
                    <!-- Store Icon -->
                    <svg
                        v-else-if="link.icon === 'store'"
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                        />
                    </svg>
                    <!-- Category Icon -->
                    <svg
                        v-else-if="link.icon === 'category'"
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                        />
                    </svg>
                    <!-- Product Icon -->
                    <svg
                        v-else-if="link.icon === 'product'"
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                        />
                    </svg>
                    <!-- Order Icon -->
                    <svg
                        v-else-if="link.icon === 'order'"
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>
                    <!-- Delivery Icon -->
                    <svg
                        v-else-if="link.icon === 'delivery'"
                        class="w-5 h-5"
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
                    <span>{{ link.label }}</span>
                </Link>
            </nav>

            <!-- Logout -->
            <div class="p-4 border-t border-primary-800">
                <button
                    @click="logout"
                    class="flex items-center gap-3 w-full px-4 py-3 rounded-lg text-sm font-medium text-white/80 hover:bg-primary-800 hover:text-white transition-colors"
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
                    <span>تسجيل الخروج</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen lg:min-w-0">
            <!-- Top Bar -->
            <header
                class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30"
            >
                <div
                    class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16"
                >
                    <!-- Mobile Menu Button -->
                    <button
                        @click="sidebarOpen = true"
                        class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <h1 class="text-lg font-bold text-primary-900">
                        {{ title }}
                    </h1>

                    <!-- Right Section -->
                    <div class="flex items-center gap-4">
                        <!-- Notifications -->
                        <Link
                            href="/notifications"
                            class="relative p-2 text-gray-600 hover:text-primary-900 transition-colors"
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
                            class="text-sm font-medium text-gray-700 hidden sm:block"
                        >
                            {{ user.name }}
                        </span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
