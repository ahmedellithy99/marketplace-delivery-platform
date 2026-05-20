<script setup>
import { Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
});

function getStoreImage(store) {
    return (
        store.media?.find((m) => m.collection_name === "logo")?.original_url ||
        null
    );
}

function getStoreCover(store) {
    return (
        store.media?.find((m) => m.collection_name === "cover")?.original_url ||
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

function formatTime(time) {
    if (!time) return "";
    // Handle both string and datetime formats
    if (typeof time === "string" && time.length <= 5) return time;
    try {
        const date = new Date(time);
        return date.toLocaleTimeString("ar-SA", {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
        });
    } catch {
        return time;
    }
}
</script>

<template>
    <PublicLayout>
        <!-- Store Header -->
        <div class="relative">
            <!-- Cover Image -->
            <div class="h-48 sm:h-64 bg-gray-200">
                <img
                    v-if="getStoreCover(store)"
                    :src="getStoreCover(store)"
                    :alt="store.name"
                    class="w-full h-full object-cover"
                />
                <div
                    v-else
                    class="w-full h-full bg-linear-to-bl from-primary-200 to-primary-100"
                ></div>
            </div>

            <!-- Store Info Overlay -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="relative -mt-16 sm:-mt-20 bg-white rounded-xl shadow-lg border border-gray-200 p-6"
                >
                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        <!-- Logo -->
                        <div
                            class="w-20 h-20 sm:w-24 sm:h-24 bg-white rounded-xl shadow-md border border-gray-200 flex items-center justify-center overflow-hidden shrink-0"
                        >
                            <img
                                v-if="getStoreImage(store)"
                                :src="getStoreImage(store)"
                                :alt="store.name"
                                class="w-full h-full object-cover"
                            />
                            <svg
                                v-else
                                class="w-10 h-10 text-primary-300"
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

                        <!-- Details -->
                        <div class="flex-1">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4"
                            >
                                <h1 class="text-2xl font-bold text-primary-900">
                                    {{ store.name }}
                                </h1>
                                <span
                                    class="inline-flex items-center gap-1.5 text-sm font-medium px-3 py-1 rounded-full w-fit"
                                    :class="
                                        store.is_open
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-700'
                                    "
                                >
                                    <span
                                        class="w-2 h-2 rounded-full"
                                        :class="
                                            store.is_open
                                                ? 'bg-green-500'
                                                : 'bg-red-500'
                                        "
                                    ></span>
                                    {{
                                        store.is_open
                                            ? "مفتوح الآن"
                                            : "مغلق حالياً"
                                    }}
                                </span>
                            </div>

                            <p
                                v-if="store.store_type"
                                class="text-sm text-gray-500 mt-1"
                            >
                                {{ store.store_type.name }}
                            </p>

                            <div
                                class="flex flex-wrap gap-4 mt-3 text-sm text-gray-600"
                            >
                                <span
                                    v-if="store.address"
                                    class="flex items-center gap-1.5"
                                >
                                    <svg
                                        class="w-4 h-4 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                    {{ store.address }}
                                </span>
                                <span
                                    v-if="
                                        store.opening_time && store.closing_time
                                    "
                                    class="flex items-center gap-1.5"
                                >
                                    <svg
                                        class="w-4 h-4 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    {{ formatTime(store.opening_time) }} -
                                    {{ formatTime(store.closing_time) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products by Category -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <template
                v-if="
                    store.grouped_products && store.grouped_products.length > 0
                "
            >
                <div
                    v-for="group in store.grouped_products"
                    :key="group.category"
                    class="mb-10"
                >
                    <!-- Category Header -->
                    <h2
                        class="text-xl font-bold text-primary-900 mb-4 pb-2 border-b border-gray-200"
                    >
                        {{ group.category }}
                    </h2>

                    <!-- Products Grid -->
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                    >
                        <div
                            v-for="product in group.products"
                            :key="product.id"
                            class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
                        >
                            <!-- Product Image -->
                            <div
                                class="h-40 bg-gray-100 flex items-center justify-center relative"
                            >
                                <img
                                    v-if="getProductImage(product)"
                                    :src="getProductImage(product)"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    v-else
                                    class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center"
                                >
                                    <svg
                                        class="w-6 h-6 text-gray-400"
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

                                <!-- Discount Badge -->
                                <span
                                    v-if="false"
                                    class="absolute top-2 inset-s-2 bg-secondary-500 text-white text-xs font-bold px-2 py-1 rounded-lg"
                                >
                                    خصم
                                </span>
                            </div>

                            <!-- Product Info -->
                            <div class="p-4">
                                <h3
                                    class="font-semibold text-gray-900 text-sm line-clamp-2"
                                >
                                    {{ product.name }}
                                </h3>

                                <!-- Variants -->
                                <p
                                    v-if="
                                        product.variants &&
                                        product.variants.length > 0
                                    "
                                    class="text-xs text-gray-500 mt-1"
                                >
                                    {{ product.variants.length }} خيارات متاحة
                                </p>

                                <!-- Price -->
                                <div class="mt-3 flex items-center gap-2">
                                    <span
                                        class="text-primary-900 font-bold text-sm"
                                        >{{
                                            formatPrice(product.base_price)
                                        }}</span
                                    >
                                </div>

                                <!-- Add to Cart -->
                                <button
                                    class="mt-3 w-full bg-primary-900 hover:bg-primary-800 text-white text-sm font-medium py-2 rounded-lg transition-colors"
                                >
                                    أضف للسلة
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <svg
                    class="w-16 h-16 text-gray-300 mx-auto mb-4"
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
                <p class="text-gray-500 text-lg">
                    لا توجد منتجات متاحة في هذا المتجر حالياً
                </p>
            </div>
        </div>
    </PublicLayout>
</template>
