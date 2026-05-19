<script setup>
import { Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { ref, onMounted } from "vue";

defineProps({
    featuredStores: { type: Array, default: () => [] },
    featuredProducts: { type: Array, default: () => [] },
});

function getStoreImage(store) {
    return (
        store.media?.find((m) => m.collection_name === "logo")?.original_url ||
        null
    );
}

function getProductImage(product) {
    return (
        product.media?.find((m) => m.collection_name === "images")
            ?.original_url || null
    );
}

function formatPrice(price) {
    if (!price) return "";
    return Number(price).toFixed(2);
}

function hasDiscount(product) {
    return (
        product.discounted_price &&
        Number(product.discounted_price) < Number(product.price)
    );
}

function discountPct(product) {
    if (!hasDiscount(product)) return 0;
    return Math.round(
        (1 - Number(product.discounted_price) / Number(product.price)) * 100,
    );
}

// Animate on mount
const heroVisible = ref(false);
const storesVisible = ref(false);
const productsVisible = ref(false);

onMounted(() => {
    setTimeout(() => (heroVisible.value = true), 100);
    setTimeout(() => (storesVisible.value = true), 400);
    setTimeout(() => (productsVisible.value = true), 700);
});
</script>

<template>
    <PublicLayout>
        <!-- Hero Section — Immersive gradient with pattern overlay -->
        <section class="relative overflow-hidden bg-primary-900">
            <!-- Decorative background elements -->
            <div class="absolute inset-0 opacity-10">
                <div
                    class="absolute top-0 end-0 w-96 h-96 bg-secondary-500 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/3"
                ></div>
                <div
                    class="absolute bottom-0 start-0 w-80 h-80 bg-primary-400 rounded-full blur-[100px] translate-y-1/2 -translate-x-1/3"
                ></div>
            </div>
            <!-- Subtle grid pattern -->
            <div
                class="absolute inset-0 opacity-[0.03]"
                style="
                    background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');
                "
            ></div>

            <div
                class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 lg:py-36 text-center transition-all duration-1000 ease-out"
                :class="
                    heroVisible
                        ? 'opacity-100 translate-y-0'
                        : 'opacity-0 translate-y-8'
                "
            >
                <!-- Badge -->
                <div
                    class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-4 py-1.5 mb-6"
                >
                    <span
                        class="w-2 h-2 bg-secondary-400 rounded-full animate-pulse"
                    ></span>
                    <span class="text-sm text-white/90 font-medium"
                        >توصيل سريع لباب بيتك</span
                    >
                </div>

                <h1
                    class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white mb-6 leading-tight"
                >
                    اطلب من
                    <span class="relative inline-block">
                        <span class="relative z-10 text-secondary-400"
                            >متاجرك المفضلة</span
                        >
                        <span
                            class="absolute bottom-2 start-0 end-0 h-3 bg-secondary-500/30 rounded-sm -z-0"
                        ></span>
                    </span>
                </h1>

                <p
                    class="text-lg sm:text-xl text-white/70 mb-10 max-w-2xl mx-auto leading-relaxed"
                >
                    تصفح مئات المتاجر والمنتجات واحصل على توصيل سريع وآمن — كل
                    ما تحتاجه في مكان واحد
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        href="/products"
                        class="group relative bg-secondary-500 hover:bg-secondary-600 text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all duration-300 shadow-lg shadow-secondary-500/25 hover:shadow-xl hover:shadow-secondary-500/30 hover:-translate-y-0.5"
                    >
                        <span class="flex items-center justify-center gap-2">
                            تصفح المنتجات
                            <svg
                                class="w-5 h-5 transition-transform duration-300 group-hover:-translate-x-1"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                        </span>
                    </Link>
                    <Link
                        href="/stores"
                        class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all duration-300 border border-white/20 hover:border-white/40"
                    >
                        استكشف المتاجر
                    </Link>
                </div>

                <!-- Stats row -->
                <div class="mt-14 grid grid-cols-3 gap-4 max-w-lg mx-auto">
                    <div class="text-center">
                        <p
                            class="text-2xl sm:text-3xl font-extrabold text-white"
                        >
                            100+
                        </p>
                        <p class="text-xs sm:text-sm text-white/50 mt-1">
                            متجر
                        </p>
                    </div>
                    <div class="text-center border-x border-white/10">
                        <p
                            class="text-2xl sm:text-3xl font-extrabold text-white"
                        >
                            1000+
                        </p>
                        <p class="text-xs sm:text-sm text-white/50 mt-1">
                            منتج
                        </p>
                    </div>
                    <div class="text-center">
                        <p
                            class="text-2xl sm:text-3xl font-extrabold text-white"
                        >
                            30 د
                        </p>
                        <p class="text-xs sm:text-sm text-white/50 mt-1">
                            متوسط التوصيل
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Stores Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
            <div
                class="transition-all duration-700 ease-out"
                :class="
                    storesVisible
                        ? 'opacity-100 translate-y-0'
                        : 'opacity-0 translate-y-6'
                "
            >
                <!-- Section Header -->
                <div class="flex items-end justify-between mb-10">
                    <div>
                        <span
                            class="text-secondary-500 text-sm font-bold tracking-wide uppercase"
                            >المتاجر</span
                        >
                        <h2
                            class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-1"
                        >
                            المتاجر المميزة
                        </h2>
                    </div>
                    <Link
                        href="/stores"
                        class="hidden sm:inline-flex items-center gap-2 text-sm font-semibold text-primary-900 hover:text-secondary-500 transition-colors group"
                    >
                        عرض الكل
                        <svg
                            class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </Link>
                </div>

                <!-- Stores Row (Rounded Avatars) -->
                <div
                    class="flex gap-6 overflow-x-auto pb-4 scrollbar-hide snap-x snap-mandatory -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap sm:overflow-visible sm:grid sm:grid-cols-4 lg:grid-cols-8"
                >
                    <Link
                        v-for="(store, index) in featuredStores"
                        :key="store.id"
                        :href="`/stores/${store.slug}`"
                        class="group flex flex-col items-center gap-3 shrink-0 snap-start transition-all duration-300 hover:-translate-y-1"
                        :style="{ transitionDelay: `${index * 60}ms` }"
                    >
                        <!-- Rounded Store Logo -->
                        <div class="relative">
                            <div
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-full overflow-hidden border-2 border-gray-100 shadow-md transition-all duration-300 group-hover:border-secondary-400 group-hover:shadow-lg group-hover:shadow-secondary-100"
                            >
                                <img
                                    v-if="getStoreImage(store)"
                                    :src="getStoreImage(store)"
                                    :alt="store.name"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                />
                                <div
                                    v-else
                                    class="w-full h-full bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-8 h-8 text-primary-300"
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
                                </div>
                            </div>
                            <!-- Open/Closed indicator dot -->
                            <span
                                class="absolute bottom-1 end-1 w-4 h-4 rounded-full border-2 border-white shadow-sm"
                                :class="
                                    store.is_open
                                        ? 'bg-green-500'
                                        : 'bg-gray-400'
                                "
                            ></span>
                        </div>
                        <!-- Store Name -->
                        <div class="text-center max-w-[6rem]">
                            <p
                                class="text-xs sm:text-sm font-semibold text-gray-800 truncate group-hover:text-primary-900 transition-colors"
                            >
                                {{ store.name }}
                            </p>
                            <p
                                v-if="store.store_type"
                                class="text-[10px] text-gray-400 truncate mt-0.5"
                            >
                                {{ store.store_type.name }}
                            </p>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div
                    v-if="featuredStores.length === 0"
                    class="text-center py-16"
                >
                    <div
                        class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4"
                    >
                        <svg
                            class="w-10 h-10 text-gray-300"
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
                    </div>
                    <p class="text-gray-500 font-medium">
                        لا توجد متاجر حالياً
                    </p>
                    <p class="text-sm text-gray-400 mt-1">
                        سيتم إضافة متاجر جديدة قريباً
                    </p>
                </div>

                <!-- Mobile "View All" -->
                <div class="sm:hidden mt-6 text-center">
                    <Link
                        href="/stores"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-primary-900 hover:text-secondary-500 transition-colors"
                    >
                        عرض كل المتاجر
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
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="bg-gradient-to-b from-gray-50/50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
                <div
                    class="transition-all duration-700 ease-out"
                    :class="
                        productsVisible
                            ? 'opacity-100 translate-y-0'
                            : 'opacity-0 translate-y-6'
                    "
                >
                    <!-- Section Header -->
                    <div class="flex items-end justify-between mb-10">
                        <div>
                            <span
                                class="text-secondary-500 text-sm font-bold tracking-wide uppercase"
                                >المنتجات</span
                            >
                            <h2
                                class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-1"
                            >
                                منتجات مميزة
                            </h2>
                        </div>
                        <Link
                            href="/products"
                            class="hidden sm:inline-flex items-center gap-2 text-sm font-semibold text-primary-900 hover:text-secondary-500 transition-colors group"
                        >
                            عرض الكل
                            <svg
                                class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-1"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                        </Link>
                    </div>

                    <!-- Products — Compact Talabat-style grid -->
                    <div
                        class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4"
                    >
                        <div
                            v-for="(product, index) in featuredProducts"
                            :key="product.id"
                            class="group bg-white rounded-xl overflow-hidden transition-all duration-200 hover:shadow-lg border border-gray-100"
                        >
                            <!-- Product Image — compact square -->
                            <div
                                class="relative aspect-square overflow-hidden bg-gray-50"
                            >
                                <img
                                    v-if="getProductImage(product)"
                                    :src="getProductImage(product)"
                                    :alt="product.name"
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                />
                                <div
                                    v-else
                                    class="w-full h-full flex items-center justify-center"
                                >
                                    <svg
                                        class="w-10 h-10 text-gray-200"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>

                                <!-- Discount badge — small pill -->
                                <span
                                    v-if="hasDiscount(product)"
                                    class="absolute top-2 start-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md"
                                >
                                    {{ discountPct(product) }}%-
                                </span>

                                <!-- Add button — bottom end corner -->
                                <button
                                    type="button"
                                    class="absolute bottom-2 end-2 w-7 h-7 bg-primary-900 text-white rounded-lg flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-primary-800 active:scale-90"
                                    aria-label="أضف للسلة"
                                >
                                    <svg
                                        class="w-3.5 h-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2.5"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Product Info — tight padding -->
                            <div class="p-2.5 sm:p-3">
                                <h3
                                    class="font-semibold text-gray-900 text-xs sm:text-sm leading-tight line-clamp-2 min-h-[2rem]"
                                >
                                    {{ product.name }}
                                </h3>
                                <p
                                    v-if="product.store"
                                    class="text-[10px] sm:text-xs text-gray-400 mt-1 truncate"
                                >
                                    {{ product.store.name }}
                                </p>

                                <!-- Price row -->
                                <div class="mt-2 flex items-baseline gap-1">
                                    <template v-if="hasDiscount(product)">
                                        <span
                                            class="text-sm sm:text-base font-bold text-secondary-500"
                                            >{{
                                                formatPrice(
                                                    product.discounted_price,
                                                )
                                            }}</span
                                        >
                                        <span
                                            class="text-[10px] text-gray-300 line-through"
                                            >{{
                                                formatPrice(product.price)
                                            }}</span
                                        >
                                    </template>
                                    <template v-else>
                                        <span
                                            class="text-sm sm:text-base font-bold text-gray-900"
                                            >{{
                                                formatPrice(product.price)
                                            }}</span
                                        >
                                    </template>
                                    <span class="text-[10px] text-gray-400"
                                        >جنيه</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="featuredProducts.length === 0"
                        class="text-center py-16"
                    >
                        <div
                            class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4"
                        >
                            <svg
                                class="w-10 h-10 text-gray-300"
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
                        </div>
                        <p class="text-gray-500 font-medium">
                            لا توجد منتجات حالياً
                        </p>
                        <p class="text-sm text-gray-400 mt-1">
                            سيتم إضافة منتجات جديدة قريباً
                        </p>
                    </div>

                    <!-- Mobile "View All" -->
                    <div class="sm:hidden mt-6 text-center">
                        <Link
                            href="/products"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-primary-900 hover:text-secondary-500 transition-colors"
                        >
                            عرض كل المنتجات
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
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Banner -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div
                class="relative bg-primary-900 rounded-3xl overflow-hidden px-8 py-12 sm:px-12 sm:py-16 text-center"
            >
                <!-- Decorative -->
                <div
                    class="absolute top-0 end-0 w-64 h-64 bg-secondary-500/10 rounded-full blur-[80px]"
                ></div>
                <div
                    class="absolute bottom-0 start-0 w-48 h-48 bg-primary-400/10 rounded-full blur-[60px]"
                ></div>

                <div class="relative">
                    <h2
                        class="text-2xl sm:text-3xl font-extrabold text-white mb-4"
                    >
                        جاهز تطلب؟
                    </h2>
                    <p class="text-white/70 mb-8 max-w-md mx-auto">
                        سجل الآن واحصل على أول توصيل مجاني — آلاف المنتجات
                        بانتظارك
                    </p>
                    <Link
                        href="/register"
                        class="inline-flex items-center gap-2 bg-secondary-500 hover:bg-secondary-600 text-white px-8 py-3.5 rounded-2xl font-bold transition-all duration-300 shadow-lg shadow-secondary-500/25 hover:shadow-xl hover:-translate-y-0.5"
                    >
                        إنشاء حساب مجاني
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
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
