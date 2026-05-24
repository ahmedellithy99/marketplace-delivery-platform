<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const notificationsCount = computed(() => page.props.notificationsCount || 0);
const notifications = computed(() => page.props.notifications || []);
const sidebarOpen = ref(false);
const notifOpen = ref(false);

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
    {
        href: "/admin/delivery-men",
        label: "مناديب التوصيل",
        icon: "delivery-men",
    },
];

// Super admin only links
const superAdminLinks = [
    { href: "/admin/staff", label: "إدارة الموظفين", icon: "staff" },
];

// Filter nav links based on role
const visibleNavLinks = computed(() => {
    const role = user.value?.role;
    let links = [];

    if (role === "customer_service") {
        // Customer service only sees orders and deliveries
        links = navLinks.filter((l) =>
            ["/admin/dashboard", "/admin/orders", "/admin/deliveries"].includes(
                l.href,
            ),
        );
    } else {
        links = [...navLinks];
    }

    // Super admin gets staff management
    if (role === "super_admin") {
        links = [...links, ...superAdminLinks];
    }

    return links;
});

function isActive(href) {
    return page.url.startsWith(href);
}

function logout() {
    router.post("/logout");
}

function getUserInitials() {
    if (!user.value?.name) return "م";
    const parts = user.value.name.split(" ");
    if (parts.length >= 2) return parts[0][0] + parts[1][0];
    return parts[0][0];
}

function timeAgo(date) {
    const now = new Date();
    const diff = Math.floor((now - new Date(date)) / 1000);
    if (diff < 60) return "الآن";
    if (diff < 3600) return `منذ ${Math.floor(diff / 60)} د`;
    if (diff < 86400) return `منذ ${Math.floor(diff / 3600)} س`;
    return `منذ ${Math.floor(diff / 86400)} ي`;
}

function handleNotifClick(notif) {
    if (!notif.is_read) {
        router.patch(
            `/notifications/${notif.id}/read`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (notif.link) router.visit(notif.link);
                },
            },
        );
    } else if (notif.link) {
        router.visit(notif.link);
    }
    notifOpen.value = false;
}
</script>

<template>
    <div class="min-h-screen lg:flex bg-gray-50">
        <!-- Mobile Backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden"
                @click="sidebarOpen = false"
            ></div>
        </Transition>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 right-0 z-50 w-[272px] bg-gradient-to-b from-primary-950 to-primary-900 text-white flex flex-col transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:z-auto"
            :class="
                sidebarOpen
                    ? 'translate-x-0'
                    : 'translate-x-full lg:translate-x-0'
            "
        >
            <!-- Logo Section -->
            <div class="px-6 py-5">
                <Link
                    href="/admin/dashboard"
                    class="flex items-center gap-3 group"
                >
                    <div
                        class="w-10 h-10 bg-secondary-500 rounded-xl flex items-center justify-center shadow-lg shadow-secondary-500/20 group-hover:shadow-secondary-500/40 transition-shadow duration-200"
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
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"
                            />
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight"
                            >ماركت بليس</span
                        >
                        <span
                            class="block text-[10px] text-white/50 font-medium tracking-wider"
                            >MARKETPLACE</span
                        >
                    </div>
                </Link>
            </div>

            <!-- Divider -->
            <div class="mx-5 border-t border-white/10"></div>

            <!-- Navigation -->
            <nav class="flex-1 py-5 px-4 space-y-1 overflow-y-auto">
                <Link
                    v-for="link in visibleNavLinks"
                    :key="link.href"
                    :href="link.href"
                    class="relative flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200"
                    :class="
                        isActive(link.href)
                            ? 'bg-white/10 text-white shadow-sm'
                            : 'text-white/60 hover:bg-white/5 hover:text-white'
                    "
                    @click="sidebarOpen = false"
                >
                    <!-- Active Accent Bar -->
                    <span
                        v-if="isActive(link.href)"
                        class="absolute inset-s-0 top-2 bottom-2 w-1 bg-secondary-500 rounded-e-full"
                    ></span>

                    <!-- Dashboard Icon -->
                    <svg
                        v-if="link.icon === 'dashboard'"
                        class="w-5 h-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                        />
                    </svg>
                    <!-- Store Icon -->
                    <svg
                        v-else-if="link.icon === 'store'"
                        class="w-5 h-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                        />
                    </svg>
                    <!-- Category Icon -->
                    <svg
                        v-else-if="link.icon === 'category'"
                        class="w-5 h-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                        />
                    </svg>
                    <!-- Product Icon -->
                    <svg
                        v-else-if="link.icon === 'product'"
                        class="w-5 h-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                        />
                    </svg>
                    <!-- Order Icon -->
                    <svg
                        v-else-if="link.icon === 'order'"
                        class="w-5 h-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>
                    <!-- Delivery Icon -->
                    <svg
                        v-else-if="link.icon === 'delivery'"
                        class="w-5 h-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"
                        />
                    </svg>
                    <!-- Delivery Men Icon -->
                    <svg
                        v-else-if="link.icon === 'delivery-men'"
                        class="w-5 h-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>
                    <!-- Staff Icon -->
                    <svg
                        v-else-if="link.icon === 'staff'"
                        class="w-5 h-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                        />
                    </svg>
                    <span>{{ link.label }}</span>
                </Link>
            </nav>

            <!-- User Section -->
            <div class="border-t border-white/10">
                <div class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-full bg-secondary-500/20 border border-secondary-500/30 flex items-center justify-center text-secondary-400 text-sm font-bold"
                        >
                            {{ getUserInitials() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">
                                {{ user?.name }}
                            </p>
                            <p class="text-xs text-white/40">مدير النظام</p>
                        </div>
                    </div>
                </div>
                <div class="px-4 pb-4">
                    <button
                        @click="logout"
                        class="flex items-center gap-3 w-full px-4 py-2.5 rounded-lg text-sm font-medium text-white/50 hover:bg-red-500/10 hover:text-red-300 transition-all duration-200"
                    >
                        <svg
                            class="w-4 h-4"
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
            </div>
        </aside>

        <!-- Main Content -->
        <div
            class="flex-1 flex flex-col min-h-screen min-w-0 w-full overflow-x-hidden"
        >
            <!-- Top Bar -->
            <header
                class="bg-white border-b border-gray-200/80 sticky top-0 z-30"
            >
                <div
                    class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16"
                >
                    <!-- Right: Mobile Menu + Breadcrumb -->
                    <div class="flex items-center gap-3">
                        <button
                            @click="sidebarOpen = true"
                            class="lg:hidden p-2 -me-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors"
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
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </button>

                        <!-- Breadcrumb Title -->
                        <div class="flex items-center gap-2 text-sm">
                            <Link
                                href="/admin/dashboard"
                                class="text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                    />
                                </svg>
                            </Link>
                            <svg
                                class="w-4 h-4 text-gray-300 rtl:rotate-180"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                            <h1 class="font-semibold text-gray-900">
                                {{ title }}
                            </h1>
                        </div>
                    </div>

                    <!-- Left: Actions -->
                    <div class="flex items-center gap-2">
                        <!-- Notifications Dropdown -->
                        <div class="relative">
                            <button
                                @click="notifOpen = !notifOpen"
                                class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
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
                                        stroke-width="1.5"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                    />
                                </svg>
                                <span
                                    v-if="notificationsCount > 0"
                                    class="absolute top-1 end-1 w-4 h-4 bg-secondary-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center animate-pulse"
                                >
                                    {{
                                        notificationsCount > 9
                                            ? "9+"
                                            : notificationsCount
                                    }}
                                </span>
                            </button>
                            <!-- Dropdown Panel -->
                            <div
                                v-if="notifOpen"
                                class="absolute inset-e-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-50 overflow-hidden"
                            >
                                <div
                                    class="flex items-center justify-between px-4 py-3 border-b border-gray-100"
                                >
                                    <h3 class="text-sm font-bold text-gray-900">
                                        الإشعارات
                                    </h3>
                                    <Link
                                        href="/notifications"
                                        @click="notifOpen = false"
                                        class="text-xs text-primary-600 hover:text-primary-800 font-medium"
                                        >عرض الكل</Link
                                    >
                                </div>
                                <div class="max-h-80 overflow-y-auto">
                                    <div
                                        v-for="notif in notifications"
                                        :key="notif.id"
                                        @click="handleNotifClick(notif)"
                                        class="px-4 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 cursor-pointer transition-colors"
                                        :class="{
                                            'bg-primary-50/40': !notif.is_read,
                                        }"
                                    >
                                        <div class="flex items-start gap-2">
                                            <span
                                                v-if="!notif.is_read"
                                                class="w-2 h-2 bg-primary-500 rounded-full mt-1.5 shrink-0"
                                            ></span>
                                            <span
                                                v-else
                                                class="w-2 h-2 shrink-0"
                                            ></span>
                                            <div class="flex-1 min-w-0">
                                                <p
                                                    class="text-sm text-gray-900 line-clamp-1"
                                                    :class="{
                                                        'font-semibold':
                                                            !notif.is_read,
                                                    }"
                                                >
                                                    {{ notif.title }}
                                                </p>
                                                <p
                                                    v-if="notif.body"
                                                    class="text-xs text-gray-500 mt-0.5 line-clamp-1"
                                                >
                                                    {{ notif.body }}
                                                </p>
                                                <p
                                                    class="text-[10px] text-gray-400 mt-1"
                                                >
                                                    {{
                                                        timeAgo(
                                                            notif.created_at,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        v-if="notifications.length === 0"
                                        class="px-4 py-8 text-center"
                                    >
                                        <p class="text-sm text-gray-400">
                                            لا توجد إشعارات
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-if="notifOpen"
                                @click="notifOpen = false"
                                class="fixed inset-0 z-40"
                            ></div>
                        </div>

                        <!-- User Avatar -->
                        <div
                            v-if="user"
                            class="hidden sm:flex items-center gap-3 ps-3 border-s border-gray-200"
                        >
                            <div
                                class="w-8 h-8 rounded-full bg-primary-900 flex items-center justify-center text-white text-xs font-bold"
                            >
                                {{ getUserInitials() }}
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{
                                user.name
                            }}</span>
                        </div>
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
