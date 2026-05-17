<script setup>
import { Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";

defineProps({
    featuredStores: {
        type: Array,
        default: () => [],
    },
    featuredProducts: {
        type: Array,
        default: () => [],
    },
});

function getStoreImage(store) {
    return (
        store.media?.find((m) => m.collection_name === "logo")?.original_url ||
        null
    );
}

function getProductImage(product) {
    const media = product.media?.find((m) => m.collection_name === "images");
    return media?.original_url || null;
}

function formatPrice(price) {
    if (!price) return "";
    return Number(price).toFixed(2) + " جنيه";
}
</script>

<template>
    <PublicLayout>
        <!-- Hero Section -->
        <section
            class="bg-linear-to-bl from-primary-900 to-primary-800 text-white py-16 sm:py-24"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-4">
                    اطلب من متاجرك المفضلة
                </h1>
                <p
                    class="text-lg sm:text-xl text-white/80 mb-8 max-w-2xl mx-auto"
                >
                    تصفح المتاجر والمنتجات واحصل على توصيل سريع لباب بيتك
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        href="/products"
                        class="bg-secondary-500 hover:bg-secondary-600 text-white px-8 py-3 rounded-xl font-semibold transition-colors shadow-lg"
                    >
                        تصفح المنتجات
                    </Link>
                    <Link
                        href="/stores"
                        class="bg-white/10 hover:bg-white/20 text-white px-8 py-3 rounded-xl font-semibold transition-colors border border-white/20"
                    >
                        استكشف المتاجر
                    </Link>
                </div>
            </div>
        </section>

        <!-- Featured Stores -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-primary-900">
                    المتاجر المميزة
                </h2>
                <Link
                    href="/stores"
                    class="text-secondary-500 hover:text-secondary-600 text-sm font-medium transition-colors"
                >
                    عرض الكل &larr;
                </Link>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <Link
                    v-for="store in featuredStores"
                    :key="store.id"
                    :href="`/stores/${store.slug}`"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group"
                >
                    <!-- Store Logo -->
                    <div
                        class="h-32 bg-gray-100 flex items-center justify-center"
                    >
                        <img
                            v-if="getStoreImage(store)"
                            :src="getStoreImage(store)"
                            :alt="store.name"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center"
                        >
                            <svg
                                class="w-8 h-8 text-primary-400"
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
                        </div>
                    </div>

                    <!-- Store Info -->
                    <div class="p-4">
                        <h3
                            class="font-semibold text-gray-900 group-hover:text-primary-900 transition-colors"
                        >
                            {{ store.name }}
                        </h3>
                        <p
                            v-if="store.store_type"
                            class="text-xs text-gray-500 mt-1"
                        >
                            {{ store.store_type.name }}
                        </p>
                        <div class="mt-3 flex items-center justify-between">
                            <span
                                class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full"
                                :class="
                                    store.is_open
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                "
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full"
                                    :class="
                                        store.is_open
                                            ? 'bg-green-500'
                                            : 'bg-red-500'
                                    "
                                ></span>
                                {{ store.is_open ? "مفتوح" : "مغلق" }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <div
                v-if="featuredStores.length === 0"
                class="text-center py-12 text-gray-500"
            >
                لا توجد متاجر حالياً
            </div>
        </section>

        <!-- Featured Products -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-primary-900">
                    منتجات مميزة
                </h2>
                <Link
                    href="/products"
                    class="text-secondary-500 hover:text-secondary-600 text-sm font-medium transition-colors"
                >
                    عرض الكل &larr;
                </Link>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    v-for="product in featuredProducts"
                    :key="product.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
                >
                    <!-- Product Image -->
                    <div
                        class="h-48 bg-gray-100 flex items-center justify-center"
                    >
                        <img
                            v-if="getProductImage(product)"
                            :src="getProductImage(product)"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center"
                        >
                            <svg
                                class="w-8 h-8 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="p-4">
                        <h3
                            class="font-semibold text-gray-900 text-sm line-clamp-2"
                        >
                            {{ product.name }}
                        </h3>
                        <p
                            v-if="product.store"
                            class="text-xs text-gray-500 mt-1"
                        >
                            {{ product.store.name }}
                        </p>

                        <!-- Price -->
                        <div class="mt-3 flex items-center gap-2">
                            <template
                                v-if="
                                    product.discounted_price &&
                                    Number(product.discounted_price) > 0
                                "
                            >
                                <span class="text-secondary-500 font-bold">{{
                                    formatPrice(product.discounted_price)
                                }}</span>
                                <span
                                    class="text-gray-400 text-sm line-through"
                                    >{{ formatPrice(product.price) }}</span
                                >
                            </template>
                            <template v-else>
                                <span class="text-primary-900 font-bold">{{
                                    formatPrice(product.price)
                                }}</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="featuredProducts.length === 0"
                class="text-center py-12 text-gray-500"
            >
                لا توجد منتجات حالياً
            </div>
        </section>
    </PublicLayout>
</template>
