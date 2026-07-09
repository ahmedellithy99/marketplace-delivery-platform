<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import AddToCartModal from "@/Components/AddToCartModal.vue";
import { ref, computed, watch } from "vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isCustomer = computed(() => user.value?.role === "customer");

const props = defineProps({
    store: { type: Object, required: true },
    products: { type: Object, default: () => ({ data: [], links: [] }) },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

// Filters
const search = ref(props.filters.search || "");
const category = ref(props.filters.category || "");
const sort = ref(props.filters.sort || "");
const onDiscount = ref(props.filters.on_discount || "");
let searchTimeout = null;

// Sort dropdown
const sortOpen = ref(false);
const sortOptions = [
    { value: "", label: "الأحدث" },
    { value: "base_price", label: "السعر: من الأقل" },
    { value: "-base_price", label: "السعر: من الأعلى" },
    { value: "name", label: "الاسم: أ - ي" },
];
const sortLabel = computed(
    () => sortOptions.find((o) => o.value === sort.value)?.label || "الأحدث",
);

function applyFilters() {
    const params = {};
    if (search.value) params.search = search.value;
    if (category.value) params.category = category.value;
    if (sort.value) params.sort = sort.value;
    if (onDiscount.value) params.on_discount = onDiscount.value;
    router.get(`/stores/${props.store.slug}`, params, {
        preserveState: true,
        preserveScroll: true,
    });
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});
watch([category, sort, onDiscount], applyFilters);

function setSort(value) {
    sort.value = value;
}

// Helpers
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
    return (
        product.media?.find((m) => m.collection_name === "images")
            ?.original_url || null
    );
}
function formatPrice(price) {
    if (!price) return "0.00";
    return Number(price).toFixed(2);
}
function formatTime(time) {
    if (!time) return "";
    if (typeof time === "string" && time.length <= 5) return time;
    try {
        return new Date(time).toLocaleTimeString("ar-SA", {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
        });
    } catch {
        return time;
    }
}

// Cart
const showCartModal = ref(false);
const selectedProduct = ref(null);

function addToCart(product) {
    if (!isCustomer.value) {
        router.visit("/login");
        return;
    }
    if (product.type === "simple") {
        router.post(
            "/cart",
            { product_id: product.id, quantity: 1 },
            { preserveScroll: true },
        );
    } else {
        selectedProduct.value = product;
        showCartModal.value = true;
    }
}
</script>

<template>
    <PublicLayout :title="store.name">
        <!-- Store Header -->
        <div class="relative">
            <!-- Cover -->
            <div
                class="h-44 sm:h-56 bg-gradient-to-br from-primary-100 to-primary-50 overflow-hidden"
            >
                <img
                    v-if="getStoreCover(store)"
                    :src="getStoreCover(store)"
                    :alt="store.name"
                    class="w-full h-full object-cover"
                />
            </div>

            <!-- Store Info Card -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="relative -mt-14 sm:-mt-16 bg-white rounded-2xl shadow-lg border border-gray-100 p-5 sm:p-6"
                >
                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        <!-- Logo -->
                        <div
                            class="w-20 h-20 sm:w-24 sm:h-24 bg-white rounded-2xl shadow-md border-2 border-white overflow-hidden shrink-0 -mt-12 sm:-mt-14"
                        >
                            <img
                                v-if="getStoreImage(store)"
                                :src="getStoreImage(store)"
                                :alt="store.name"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                class="w-full h-full bg-primary-50 flex items-center justify-center"
                            >
                                <svg
                                    class="w-10 h-10 text-primary-300"
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

                        <!-- Details -->
                        <div class="flex-1 min-w-0">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3"
                            >
                                <h1
                                    class="text-xl sm:text-2xl font-extrabold text-gray-900"
                                >
                                    {{ store.name }}
                                </h1>
                                <span
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full w-fit"
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
                                                ? 'bg-green-500 animate-pulse'
                                                : 'bg-red-500'
                                        "
                                    ></span>
                                    {{ store.is_open ? "مفتوح" : "مغلق" }}
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

        <!-- Products Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Search -->
            <div class="relative mb-5">
                <svg
                    class="absolute top-1/2 -translate-y-1/2 start-4 w-5 h-5 text-gray-400 pointer-events-none"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="ابحث في منتجات هذا المتجر..."
                    class="w-full ps-12 pe-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none shadow-sm transition-all"
                />
            </div>

            <!-- Category Chips -->
            <div
                class="flex gap-2 overflow-x-auto pb-3 scrollbar-hide -mx-4 px-4 sm:mx-0 sm:px-0 mb-5"
            >
                <button
                    @click="category = ''"
                    class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all"
                    :class="
                        !category
                            ? 'bg-primary-900 text-white shadow-sm'
                            : 'bg-white border border-gray-200 text-gray-600 hover:border-gray-300'
                    "
                >
                    الكل
                </button>
                <button
                    @click="onDiscount = onDiscount ? '' : '1'"
                    class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all"
                    :class="
                        onDiscount
                            ? 'bg-red-500 text-white shadow-sm'
                            : 'bg-white border border-gray-200 text-gray-600 hover:border-gray-300'
                    "
                >
                    🏷️ عروض
                </button>
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="category = String(cat.id)"
                    class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all"
                    :class="
                        category === String(cat.id)
                            ? 'bg-primary-900 text-white shadow-sm'
                            : 'bg-white border border-gray-200 text-gray-600 hover:border-gray-300'
                    "
                >
                    {{ cat.name }}
                </button>
            </div>

            <!-- Sort Row -->
            <div class="flex items-center justify-between mb-5">
                <p class="text-sm text-gray-500 font-medium">
                    {{ products.total || 0 }} منتج
                </p>
                <!-- Custom Sort Dropdown -->
                <div class="relative">
                    <button
                        @click="sortOpen = !sortOpen"
                        class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-4 py-2.5 shadow-sm hover:shadow hover:border-gray-300 transition-all"
                    >
                        <svg
                            class="w-4 h-4 text-primary-900"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"
                            />
                        </svg>
                        <span class="text-sm font-bold text-gray-900">{{
                            sortLabel
                        }}</span>
                        <svg
                            class="w-4 h-4 text-gray-400 transition-transform"
                            :class="{ 'rotate-180': sortOpen }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </button>
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 -translate-y-1"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 -translate-y-1"
                    >
                        <div
                            v-if="sortOpen"
                            class="absolute inset-e-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50 overflow-hidden"
                        >
                            <button
                                v-for="option in sortOptions"
                                :key="option.value"
                                @click="
                                    setSort(option.value);
                                    sortOpen = false;
                                "
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm transition-colors text-start"
                                :class="
                                    sort === option.value
                                        ? 'bg-primary-50 text-primary-900 font-semibold'
                                        : 'text-gray-700 hover:bg-gray-50'
                                "
                            >
                                <svg
                                    v-if="sort === option.value"
                                    class="w-4 h-4 text-primary-600 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2.5"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                <span v-else class="w-4 shrink-0"></span>
                                <span>{{ option.label }}</span>
                            </button>
                        </div>
                    </Transition>
                    <div
                        v-if="sortOpen"
                        @click="sortOpen = false"
                        class="fixed inset-0 z-40"
                    ></div>
                </div>
            </div>

            <!-- Products Grid (same as products page) -->
            <div
                class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4"
            >
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="group bg-white rounded-xl overflow-hidden border border-gray-100 transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5"
                >
                    <div
                        class="relative aspect-square overflow-hidden bg-gray-50"
                    >
                        <img
                            v-if="getProductImage(product)"
                            :src="getProductImage(product)"
                            :alt="product.name"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                            loading="lazy"
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
                        <span
                            v-if="product.pricing?.has_discount"
                            class="absolute top-2 start-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md shadow-sm"
                            >{{ product.pricing.discount_label }}</span
                        >
                        <span
                            v-if="product.type === 'measured'"
                            class="absolute top-2 end-2 bg-white/90 backdrop-blur-sm text-[10px] font-medium text-gray-600 px-1.5 py-0.5 rounded-md"
                            >بالوزن</span
                        >
                        <span
                            v-else-if="product.type === 'variant'"
                            class="absolute top-2 end-2 bg-white/90 backdrop-blur-sm text-[10px] font-medium text-gray-600 px-1.5 py-0.5 rounded-md"
                            >متعدد</span
                        >
                        <button
                            @click="addToCart(product)"
                            class="absolute bottom-2 end-2 w-8 h-8 bg-primary-900 text-white rounded-lg flex items-center justify-center shadow-md sm:opacity-0 sm:group-hover:opacity-100 transition-all duration-200 hover:bg-primary-800 active:scale-90"
                            aria-label="أضف للسلة"
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
                                    stroke-width="2.5"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                        </button>
                    </div>
                    <div class="p-2.5 sm:p-3">
                        <h3
                            class="font-semibold text-gray-900 text-xs sm:text-sm leading-tight line-clamp-2 min-h-[2rem]"
                        >
                            {{ product.name }}
                        </h3>
                        <p
                            v-if="product.description"
                            class="text-[10px] sm:text-xs text-gray-500 mt-1 line-clamp-2"
                        >
                            {{ product.description }}
                        </p>
                        <div class="mt-2 flex items-baseline gap-1">
                            <template v-if="product.pricing?.has_discount">
                                <span
                                    class="text-sm sm:text-base font-bold text-secondary-500"
                                    >{{
                                        formatPrice(
                                            product.pricing.effective_price,
                                        )
                                    }}</span
                                >
                                <span
                                    class="text-[10px] text-gray-300 line-through"
                                    >{{
                                        formatPrice(product.pricing.unit_price)
                                    }}</span
                                >
                            </template>
                            <template v-else>
                                <span
                                    class="text-sm sm:text-base font-bold text-gray-900"
                                    >{{
                                        formatPrice(
                                            product.pricing?.effective_price ??
                                                product.base_price,
                                        )
                                    }}</span
                                >
                            </template>
                            <span class="text-[10px] text-gray-400">جنيه</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="products.data && products.data.length === 0"
                class="text-center py-16"
            >
                <div
                    class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4"
                >
                    <svg
                        class="w-8 h-8 text-gray-300"
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
                <p class="text-gray-700 font-medium">لا توجد منتجات</p>
                <p class="text-sm text-gray-400 mt-1">
                    لا توجد منتجات مطابقة في هذا المتجر
                </p>
            </div>

            <!-- Pagination -->
            <nav
                v-if="products.links && products.links.length > 3"
                class="mt-10 flex justify-center"
            >
                <div class="flex items-center gap-1">
                    <template v-for="link in products.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3.5 py-2 text-sm rounded-lg transition-all font-medium"
                            :class="
                                link.active
                                    ? 'bg-primary-900 text-white shadow-sm'
                                    : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
                            "
                            v-html="link.label"
                            preserve-scroll
                        />
                        <span
                            v-else
                            class="px-3.5 py-2 text-sm text-gray-300"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </nav>
        </div>

        <!-- Add to Cart Modal -->
        <AddToCartModal
            :show="showCartModal"
            :product="selectedProduct"
            @close="
                showCartModal = false;
                selectedProduct = null;
            "
        />
    </PublicLayout>
</template>
