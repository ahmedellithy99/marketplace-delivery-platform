<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const notificationsCount = computed(() => page.props.notificationsCount || 0);
const notifications = computed(() => page.props.notifications || []);
const cartItemCount = computed(() => page.props.cart?.itemCount || 0);
const cartSubtotal = computed(() => page.props.cart?.subtotal || 0);
const mobileMenuOpen = ref(false);
const userMenuOpen = ref(false);
const notifOpen = ref(false);

function formatCartTotal(amount) {
    return Number(amount).toFixed(2);
}

function timeAgo(date) {
    const now = new Date();
    const diff = Math.floor((now - new Date(date)) / 1000);
    if (diff < 60) return "الآن";
    if (diff < 3600) return `منذ ${Math.floor(diff / 60)} د`;
    if (diff < 86400) return `منذ ${Math.floor(diff / 3600)} س`;
    return `منذ ${Math.floor(diff / 86400)} ي`;
}

function markAsRead(notification) {
    if (notification.is_read) return;
    router.patch(
        `/notifications/${notification.id}/read`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                if (notification.link) {
                    router.visit(notification.link);
                }
            },
        },
    );
    notifOpen.value = false;
}

function handleNotifClick(notification) {
    if (!notification.is_read) {
        markAsRead(notification);
    } else if (notification.link) {
        router.visit(notification.link);
        notifOpen.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50">
        <!-- Header -->
        <header class="bg-primary-900 text-white shadow-lg sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 bg-secondary-500 rounded-lg flex items-center justify-center"
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
                        <span class="text-lg font-bold hidden sm:block"
                            >ماركت بليس</span
                        >
                    </Link>

                    <!-- Desktop Navigation -->
                    <nav class="hidden md:flex items-center gap-6">
                        <Link
                            href="/"
                            class="text-white/90 hover:text-white transition-colors text-sm font-medium"
                        >
                            الرئيسية
                        </Link>
                        <Link
                            href="/products"
                            class="text-white/90 hover:text-white transition-colors text-sm font-medium"
                        >
                            المنتجات
                        </Link>
                        <Link
                            href="/stores"
                            class="text-white/90 hover:text-white transition-colors text-sm font-medium"
                        >
                            المتاجر
                        </Link>
                        <Link
                            v-if="user?.role === 'customer'"
                            href="/orders"
                            class="text-white/90 hover:text-white transition-colors text-sm font-medium"
                        >
                            طلباتي
                        </Link>
                        <Link
                            v-if="user?.role === 'admin'"
                            href="/admin/dashboard"
                            class="text-secondary-400 hover:text-secondary-300 transition-colors text-sm font-medium"
                        >
                            لوحة التحكم
                        </Link>
                        <Link
                            v-if="user?.role === 'delivery'"
                            href="/delivery/assignments"
                            class="text-secondary-400 hover:text-secondary-300 transition-colors text-sm font-medium"
                        >
                            التوصيلات
                        </Link>
                    </nav>

                    <!-- Right Section -->
                    <div class="flex items-center gap-3">
                        <!-- Notifications Dropdown -->
                        <template v-if="user">
                            <div class="relative">
                                <button
                                    @click="notifOpen = !notifOpen"
                                    class="relative p-2 text-white/90 hover:text-white transition-colors"
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
                                </button>
                                <!-- Dropdown -->
                                <div
                                    v-if="notifOpen"
                                    class="absolute inset-e-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-50 overflow-hidden"
                                >
                                    <div
                                        class="flex items-center justify-between px-4 py-3 border-b border-gray-100"
                                    >
                                        <h3
                                            class="text-sm font-bold text-gray-900"
                                        >
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
                                                'bg-primary-50/40':
                                                    !notif.is_read,
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
                                <!-- Backdrop to close -->
                                <div
                                    v-if="notifOpen"
                                    @click="notifOpen = false"
                                    class="fixed inset-0 z-40"
                                ></div>
                            </div>
                        </template>

                        <!-- User Menu / Auth Links -->
                        <template v-if="user">
                            <div class="relative">
                                <button
                                    @click="userMenuOpen = !userMenuOpen"
                                    class="flex items-center gap-2 text-sm text-white/90 hover:text-white transition-colors"
                                    aria-label="قائمة الحساب"
                                >
                                    <div
                                        class="w-8 h-8 bg-primary-700 rounded-full flex items-center justify-center"
                                    >
                                        {{ user.name?.charAt(0) }}
                                    </div>
                                    <span class="hidden sm:block">{{
                                        user.name
                                    }}</span>
                                </button>
                                <div
                                    v-if="userMenuOpen"
                                    @click="userMenuOpen = false"
                                    class="absolute inset-e-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50"
                                >
                                    <Link
                                        href="/logout"
                                        method="post"
                                        as="button"
                                        class="block w-full text-start px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                    >
                                        تسجيل الخروج
                                    </Link>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <Link
                                href="/login"
                                class="text-sm text-white/90 hover:text-white transition-colors"
                            >
                                تسجيل الدخول
                            </Link>
                            <Link
                                href="/register"
                                class="text-sm bg-secondary-500 hover:bg-secondary-600 text-white px-4 py-2 rounded-lg transition-colors"
                            >
                                إنشاء حساب
                            </Link>
                        </template>

                        <!-- Mobile Menu Toggle -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden p-2 text-white/90 hover:text-white"
                            aria-label="القائمة"
                        >
                            <svg
                                class="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    v-if="!mobileMenuOpen"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    v-else
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Navigation -->
                <div
                    v-if="mobileMenuOpen"
                    class="md:hidden pb-4 border-t border-primary-800 mt-2 pt-4"
                >
                    <nav class="flex flex-col gap-3">
                        <Link
                            href="/"
                            class="text-white/90 hover:text-white text-sm font-medium"
                            @click="mobileMenuOpen = false"
                        >
                            الرئيسية
                        </Link>
                        <Link
                            href="/products"
                            class="text-white/90 hover:text-white text-sm font-medium"
                            @click="mobileMenuOpen = false"
                        >
                            المنتجات
                        </Link>
                        <Link
                            href="/stores"
                            class="text-white/90 hover:text-white text-sm font-medium"
                            @click="mobileMenuOpen = false"
                        >
                            المتاجر
                        </Link>
                        <Link
                            v-if="user?.role === 'customer'"
                            href="/orders"
                            class="text-secondary-400 hover:text-secondary-300 text-sm font-medium"
                            @click="mobileMenuOpen = false"
                        >
                            طلباتي
                        </Link>
                        <Link
                            v-if="user?.role === 'admin'"
                            href="/admin/dashboard"
                            class="text-secondary-400 hover:text-secondary-300 text-sm font-medium"
                            @click="mobileMenuOpen = false"
                        >
                            لوحة التحكم
                        </Link>
                        <Link
                            v-if="user?.role === 'delivery'"
                            href="/delivery/assignments"
                            class="text-secondary-400 hover:text-secondary-300 text-sm font-medium"
                            @click="mobileMenuOpen = false"
                        >
                            التوصيلات
                        </Link>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-primary-950 text-white/80 py-8 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-white font-bold text-lg mb-3">
                            ماركت بليس
                        </h3>
                        <p class="text-sm text-white/60">
                            منصة التوصيل الأولى - نوصل لك كل ما تحتاجه من متاجرك
                            المفضلة
                        </p>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-3">
                            روابط سريعة
                        </h4>
                        <nav class="flex flex-col gap-2">
                            <Link
                                href="/products"
                                class="text-sm text-white/60 hover:text-white transition-colors"
                                >المنتجات</Link
                            >
                            <Link
                                href="/stores"
                                class="text-sm text-white/60 hover:text-white transition-colors"
                                >المتاجر</Link
                            >
                        </nav>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-3">
                            تواصل معنا
                        </h4>
                        <p class="text-sm text-white/60">
                            support@marketplace.com
                        </p>
                    </div>
                </div>
                <div
                    class="border-t border-white/10 mt-8 pt-6 text-center text-sm text-white/40"
                >
                    &copy; {{ new Date().getFullYear() }} ماركت بليس. جميع
                    الحقوق محفوظة.
                </div>
            </div>
        </footer>

        <!-- Floating Cart Bar (Talabat-style) — appears when cart has items -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-full opacity-0"
        >
            <div
                v-if="cartItemCount > 0 && user"
                class="fixed bottom-0 inset-x-0 z-50 p-3 sm:p-4"
            >
                <Link
                    href="/cart"
                    class="flex items-center justify-between max-w-lg mx-auto bg-primary-900 text-white rounded-2xl px-5 py-3.5 shadow-2xl shadow-primary-900/40 hover:bg-primary-800 transition-all duration-200 active:scale-[0.98]"
                >
                    <!-- Left: item count badge -->
                    <div class="flex items-center gap-3">
                        <span
                            class="w-7 h-7 bg-secondary-500 rounded-lg flex items-center justify-center text-xs font-bold"
                        >
                            {{ cartItemCount }}
                        </span>
                        <span class="text-sm font-semibold">عرض السلة</span>
                    </div>

                    <!-- Right: total price -->
                    <div class="flex items-center gap-1">
                        <span class="text-base font-bold">{{
                            formatCartTotal(cartSubtotal)
                        }}</span>
                        <span class="text-xs text-white/70">جنيه</span>
                    </div>
                </Link>
            </div>
        </Transition>
    </div>
</template>
