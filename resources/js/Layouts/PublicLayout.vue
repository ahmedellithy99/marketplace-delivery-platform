<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const notificationsCount = computed(() => page.props.notificationsCount || 0);
const mobileMenuOpen = ref(false);
const userMenuOpen = ref(false);
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
                    </nav>

                    <!-- Right Section -->
                    <div class="flex items-center gap-3">
                        <!-- Notifications -->
                        <template v-if="user">
                            <Link
                                href="/notifications"
                                class="relative p-2 text-white/90 hover:text-white transition-colors"
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
                        </template>

                        <!-- User Menu / Auth Links -->
                        <template v-if="user">
                            <div class="relative">
                                <button
                                    @click="userMenuOpen = !userMenuOpen"
                                    class="flex items-center gap-2 text-sm text-white/90 hover:text-white transition-colors"
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
                                        class="block w-full text-start px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
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
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
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
    </div>
</template>
