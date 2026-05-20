<script setup>
import { Link, router } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { ref, watch } from "vue";

const props = defineProps({
    products: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || "");
const category = ref(props.filters.category || "");
const priceMin = ref(props.filters.price_min || "");
const priceMax = ref(props.filters.price_max || "");
const sort = ref(props.filters.sort || "");

let searchTimeout = null;

function applyFilters() {
    const params = {};
    if (search.value) params.search = search.value;
    if (category.value) params.category = category.value;
    if (priceMin.value) params.price_min = priceMin.value;
    if (priceMax.value) params.price_max = priceMax.value;
    if (sort.value) params.sort = sort.value;

    router.get("/products", params, {
        preserveState: true,
        preserveScroll: true,
    });
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

watch([category, priceMin, priceMax, sort], applyFilters);

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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <h1 class="text-2xl font-bold text-primary-900 mb-6">المنتجات</h1>

            <!-- Filter Bar -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-8"
            >
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4"
                >
                    <!-- Search -->
                    <div class="lg:col-span-2">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="ابحث عن منتج..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                        />
                    </div>

                    <!-- Category -->
                    <div>
                        <select
                            v-model="category"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                        >
                            <option value="">كل الأقسام</option>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="flex gap-2">
                        <input
                            v-model="priceMin"
                            type="number"
                            placeholder="من"
                            min="0"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                        />
                        <input
                            v-model="priceMax"
                            type="number"
                            placeholder="إلى"
                            min="0"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                        />
                    </div>

                    <!-- Sort -->
                    <div>
                        <select
                            v-model="sort"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                        >
                            <option value="">الأحدث</option>
                            <option value="price_asc">السعر: من الأقل</option>
                            <option value="price_desc">السعر: من الأعلى</option>
                            <option value="name_asc">الاسم: أ-ي</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
            >
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
                >
                    <!-- Product Image -->
                    <div
                        class="h-48 bg-gray-100 flex items-center justify-center relative"
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

                        <!-- Discount Badge -->
                        <span
                            v-if="product.pricing?.has_discount"
                            class="absolute top-2 inset-s-2 bg-secondary-500 text-white text-xs font-bold px-2 py-1 rounded-lg"
                        >
                            {{ product.pricing.discount_label }}
                        </span>
                    </div>

                    <!-- Product Info -->
                    <div class="p-4">
                        <h3
                            class="font-semibold text-gray-900 text-sm line-clamp-2 mb-1"
                        >
                            {{ product.name }}
                        </h3>
                        <p v-if="product.store" class="text-xs text-gray-500">
                            {{ product.store.name }}
                        </p>

                        <!-- Price -->
                        <div class="mt-3 flex items-center gap-2">
                            <template v-if="product.pricing?.has_discount">
                                <span
                                    class="text-secondary-500 font-bold text-sm"
                                    >{{
                                        formatPrice(
                                            product.pricing.effective_price,
                                        )
                                    }}</span
                                >
                                <span
                                    class="text-gray-400 text-xs line-through"
                                    >{{
                                        formatPrice(product.pricing.unit_price)
                                    }}</span
                                >
                            </template>
                            <template v-else>
                                <span
                                    class="text-primary-900 font-bold text-sm"
                                    >{{
                                        formatPrice(
                                            product.pricing?.effective_price ||
                                                product.base_price,
                                        )
                                    }}</span
                                >
                            </template>
                        </div>

                        <!-- Add to Cart Button -->
                        <button
                            class="mt-4 w-full bg-primary-900 hover:bg-primary-800 text-white text-sm font-medium py-2.5 rounded-lg transition-colors"
                        >
                            أضف للسلة
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="products.data && products.data.length === 0"
                class="text-center py-16"
            >
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
                <p class="text-gray-500 text-lg">لا توجد منتجات مطابقة للبحث</p>
            </div>

            <!-- Pagination -->
            <nav
                v-if="products.links && products.links.length > 3"
                class="mt-8 flex justify-center"
            >
                <div class="flex items-center gap-1">
                    <template v-for="link in products.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-2 text-sm rounded-lg transition-colors"
                            :class="
                                link.active
                                    ? 'bg-primary-900 text-white'
                                    : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'
                            "
                            v-html="link.label"
                            preserve-scroll
                        />
                        <span
                            v-else
                            class="px-3 py-2 text-sm text-gray-400"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </nav>
        </div>
    </PublicLayout>
</template>
