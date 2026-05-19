<script setup>
import { ref, computed } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    store: { type: Object, required: true },
    products: { type: Object, default: () => ({ data: [], links: [] }) },
    categories: { type: Array, default: () => [] },
});

const productsList = computed(() => props.products?.data || []);
const paginationLinks = computed(() => props.products?.links || []);

// ── Filters ──────────────────────────────────────────────────────────
const filters = ref({
    search: new URLSearchParams(window.location.search).get("search") || "",
    category: new URLSearchParams(window.location.search).get("category") || "",
    is_available:
        new URLSearchParams(window.location.search).get("is_available") || "",
    on_discount:
        new URLSearchParams(window.location.search).get("on_discount") || "",
    sort: new URLSearchParams(window.location.search).get("sort") || "",
});

let filterTimer = null;
function applyFilters() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        const query = Object.fromEntries(
            Object.entries(filters.value).filter(([, v]) => v !== ""),
        );
        router.get(`/admin/stores/${props.store.slug}`, query, {
            preserveState: true,
            replace: true,
        });
    }, 300);
}

function resetFilters() {
    filters.value = {
        search: "",
        category: "",
        is_available: "",
        on_discount: "",
        sort: "",
    };
    router.get(
        `/admin/stores/${props.store.slug}`,
        {},
        { preserveState: false },
    );
}

const hasActiveFilters = computed(() =>
    Object.values(filters.value).some((v) => v !== ""),
);

// ── Add Product Modal ─────────────────────────────────────────────────
const showAddModal = ref(false);

const form = useForm({
    store_id: props.store.id,
    category_id: "",
    name: "",
    description: "",
    price: "",
    discounted_price: "",
    images: [],
});

const imagePreviews = ref([]);
const imagesInput = ref(null);

function handleImages(e) {
    const files = Array.from(e.target.files);
    form.images = files;
    imagePreviews.value = files.map((f) => URL.createObjectURL(f));
}

function removeImage(index) {
    const newFiles = [...form.images];
    newFiles.splice(index, 1);
    form.images = newFiles;
    imagePreviews.value.splice(index, 1);
    if (newFiles.length === 0 && imagesInput.value)
        imagesInput.value.value = "";
}

function submitProduct() {
    form.post("/admin/products", {
        forceFormData: true,
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
            imagePreviews.value = [];
            router.reload({ only: ["products"] });
        },
    });
}

function openModal() {
    form.reset();
    form.store_id = props.store.id;
    imagePreviews.value = [];
    showAddModal.value = true;
}

// ── Helpers ───────────────────────────────────────────────────────────
function getLogoUrl(store) {
    return (
        store.media?.find((m) => m.collection_name === "logo")?.original_url ||
        null
    );
}

function getImageUrl(product) {
    return product.media?.[0]?.original_url || null;
}

function formatPrice(price) {
    return price ? Number(price).toFixed(2) : "0.00";
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

function toggleAvailability(product) {
    router.patch(
        `/admin/products/${product.slug}/toggle-availability`,
        {},
        {
            preserveScroll: true,
            only: ["products"],
        },
    );
}

function deleteProduct(product) {
    if (confirm(`هل أنت متأكد من حذف المنتج "${product.name}"؟`)) {
        router.delete(`/admin/products/${product.slug}`, {
            preserveScroll: true,
            only: ["products"],
        });
    }
}
</script>

<template>
    <AdminLayout :title="`${store.name} — المنتجات`">
        <!-- Back -->
        <Link
            href="/admin/stores"
            class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-primary-900 mb-6 transition-colors"
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
                    d="M9 5l7 7-7 7"
                />
            </svg>
            العودة للمتاجر
        </Link>

        <!-- Store Header Card -->
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6 flex items-center gap-4"
        >
            <div
                class="w-16 h-16 rounded-xl bg-gray-100 overflow-hidden shrink-0 border border-gray-200"
            >
                <img
                    v-if="getLogoUrl(store)"
                    :src="getLogoUrl(store)"
                    :alt="store.name"
                    class="w-full h-full object-cover"
                />
                <div
                    v-else
                    class="w-full h-full flex items-center justify-center text-gray-400"
                >
                    <svg
                        class="w-7 h-7"
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
            <div class="flex-1 min-w-0">
                <h1 class="text-xl font-bold text-primary-900 truncate">
                    {{ store.name }}
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ store.store_type?.name || "" }}
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                    :class="
                        store.is_open
                            ? 'bg-green-50 text-green-700 border border-green-200'
                            : 'bg-red-50 text-red-700 border border-red-200'
                    "
                >
                    <span
                        class="w-1.5 h-1.5 rounded-full"
                        :class="store.is_open ? 'bg-green-500' : 'bg-red-500'"
                    ></span>
                    {{ store.is_open ? "مفتوح" : "مغلق" }}
                </span>
                <Link
                    :href="`/admin/stores/${store.slug}/edit`"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 transition-all"
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
                            stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                        />
                    </svg>
                    تعديل المتجر
                </Link>
            </div>
        </div>

        <!-- Products Section Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">منتجات المتجر</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ products.total ?? 0 }} منتج
                </p>
            </div>
            <button
                @click="openModal"
                class="inline-flex items-center gap-2 bg-primary-900 text-white px-5 py-2.5 rounded-lg hover:bg-primary-800 shadow-sm hover:shadow-md transition-all text-sm font-medium focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
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
                        d="M12 4v16m8-8H4"
                    />
                </svg>
                إضافة منتج
            </button>
        </div>

        <!-- Filters Bar -->
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-4"
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search -->
                <div class="relative">
                    <svg
                        class="absolute top-1/2 -translate-y-1/2 start-3 w-4 h-4 text-gray-400 pointer-events-none"
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
                        v-model="filters.search"
                        @input="applyFilters"
                        type="text"
                        placeholder="بحث عن منتج..."
                        class="w-full ps-9 pe-4 py-2 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                    />
                </div>
                <!-- Category -->
                <select
                    v-model="filters.category"
                    @change="applyFilters"
                    class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all appearance-none bg-white"
                >
                    <option value="">كل الأقسام</option>
                    <option
                        v-for="cat in categories"
                        :key="cat.id"
                        :value="cat.id"
                    >
                        {{ cat.name }}
                    </option>
                </select>
                <!-- Availability -->
                <select
                    v-model="filters.is_available"
                    @change="applyFilters"
                    class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all appearance-none bg-white"
                >
                    <option value="">كل الحالات</option>
                    <option value="1">متاح</option>
                    <option value="0">غير متاح</option>
                </select>
                <!-- Sort + Reset -->
                <div class="flex gap-2">
                    <select
                        v-model="filters.sort"
                        @change="applyFilters"
                        class="flex-1 py-2 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all appearance-none bg-white"
                    >
                        <option value="">الترتيب الافتراضي</option>
                        <option value="name">الاسم (أ-ي)</option>
                        <option value="-name">الاسم (ي-أ)</option>
                        <option value="price">السعر (الأقل)</option>
                        <option value="-price">السعر (الأعلى)</option>
                        <option value="-created_at">الأحدث</option>
                    </select>
                    <button
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="px-3 py-2 text-sm rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-all"
                        title="مسح الفلاتر"
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
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                المنتج
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                القسم
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                السعر
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                التوفر
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="product in productsList"
                            :key="product.id"
                            class="border-t border-gray-100 hover:bg-gray-50/50 transition-colors"
                        >
                            <!-- Product -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="relative w-12 h-12 rounded-lg bg-gray-100 overflow-hidden shrink-0 border border-gray-200"
                                    >
                                        <img
                                            v-if="getImageUrl(product)"
                                            :src="getImageUrl(product)"
                                            :alt="product.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="w-full h-full flex items-center justify-center text-gray-400"
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
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                        </div>
                                        <span
                                            v-if="hasDiscount(product)"
                                            class="absolute top-0 start-0 bg-red-500 text-white text-[9px] font-bold px-1 py-0.5 rounded-ee-md"
                                        >
                                            -{{ discountPct(product) }}%
                                        </span>
                                    </div>
                                    <p
                                        class="font-medium text-gray-900 truncate max-w-[180px]"
                                    >
                                        {{ product.name }}
                                    </p>
                                </div>
                            </td>
                            <!-- Category -->
                            <td class="px-5 py-4 text-gray-600">
                                {{ product.category?.name || "—" }}
                            </td>
                            <!-- Price -->
                            <td class="px-5 py-4 tabular-nums">
                                <div
                                    v-if="hasDiscount(product)"
                                    class="flex flex-col"
                                >
                                    <span class="font-semibold text-green-700"
                                        >{{
                                            formatPrice(
                                                product.discounted_price,
                                            )
                                        }}
                                        <span
                                            class="text-xs font-normal text-gray-500"
                                            >جنيه</span
                                        ></span
                                    >
                                    <span
                                        class="text-xs text-gray-400 line-through"
                                        >{{ formatPrice(product.price) }}</span
                                    >
                                </div>
                                <div v-else>
                                    <span class="font-semibold text-gray-900"
                                        >{{ formatPrice(product.price) }}
                                        <span
                                            class="text-xs font-normal text-gray-500"
                                            >جنيه</span
                                        ></span
                                    >
                                </div>
                            </td>
                            <!-- Availability -->
                            <td class="px-5 py-4">
                                <button
                                    @click="toggleAvailability(product)"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                                    :class="
                                        product.is_available
                                            ? 'bg-green-500'
                                            : 'bg-gray-300'
                                    "
                                    :aria-label="
                                        product.is_available
                                            ? 'متاح'
                                            : 'غير متاح'
                                    "
                                >
                                    <span
                                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform duration-200"
                                        :class="
                                            product.is_available
                                                ? '-translate-x-6'
                                                : '-translate-x-1'
                                        "
                                    />
                                </button>
                                <span
                                    class="block text-[10px] mt-1"
                                    :class="
                                        product.is_available
                                            ? 'text-green-600'
                                            : 'text-gray-400'
                                    "
                                >
                                    {{
                                        product.is_available
                                            ? "متاح"
                                            : "غير متاح"
                                    }}
                                </span>
                            </td>
                            <!-- Actions -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="`/admin/products/${product.slug}/edit`"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 transition-all"
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
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            />
                                        </svg>
                                        تعديل
                                    </Link>
                                    <button
                                        @click="deleteProduct(product)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition-all"
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
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Empty State -->
                        <tr v-if="productsList.length === 0">
                            <td colspan="5" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div
                                        class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center"
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
                                                stroke-width="1.5"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-gray-700 font-medium">
                                            لا توجد منتجات
                                        </p>
                                        <p class="text-sm text-gray-400 mt-1">
                                            ابدأ بإضافة أول منتج لهذا المتجر
                                        </p>
                                    </div>
                                    <button
                                        @click="openModal"
                                        class="inline-flex items-center gap-2 bg-primary-900 text-white px-4 py-2 rounded-lg hover:bg-primary-800 transition-all text-sm font-medium"
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
                                                d="M12 4v16m8-8H4"
                                            />
                                        </svg>
                                        إضافة منتج
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <nav
                v-if="paginationLinks.length > 3"
                class="flex items-center justify-center gap-1 px-5 py-4 border-t border-gray-100"
                aria-label="التنقل بين الصفحات"
            >
                <template v-for="(link, index) in paginationLinks" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="px-3.5 py-2 text-sm rounded-lg transition-all font-medium"
                        :class="
                            link.active
                                ? 'bg-primary-900 text-white shadow-sm'
                                : 'text-gray-600 hover:bg-gray-100'
                        "
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="px-3.5 py-2 text-sm text-gray-300"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </div>

        <!-- Add Product Modal -->
        <Teleport to="body">
            <div
                v-if="showAddModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                    @click="showAddModal = false"
                />
                <!-- Modal -->
                <div
                    class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                >
                    <!-- Modal Header -->
                    <div
                        class="flex items-center justify-between p-6 border-b border-gray-100"
                    >
                        <div>
                            <h3 class="text-lg font-bold text-primary-900">
                                إضافة منتج جديد
                            </h3>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ store.name }}
                            </p>
                        </div>
                        <button
                            @click="showAddModal = false"
                            class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all"
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
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                    <!-- Modal Body -->
                    <form @submit.prevent="submitProduct" class="p-6 space-y-5">
                        <!-- Name -->
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 mb-2"
                                >اسم المنتج
                                <span class="text-red-500">*</span></label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="أدخل اسم المنتج"
                                class="w-full py-2.5 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                :class="{ 'border-red-300': form.errors.name }"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <!-- Category -->
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 mb-2"
                                >القسم
                                <span class="text-red-500">*</span></label
                            >
                            <select
                                v-model="form.category_id"
                                class="w-full py-2.5 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all appearance-none bg-white"
                                :class="{
                                    'border-red-300': form.errors.category_id,
                                }"
                            >
                                <option value="">اختر القسم</option>
                                <option
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.name }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.category_id"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.category_id }}
                            </p>
                        </div>
                        <!-- Price -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                    >السعر (جنيه)
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    dir="ltr"
                                    placeholder="0.00"
                                    class="w-full py-2.5 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                    :class="{
                                        'border-red-300': form.errors.price,
                                    }"
                                />
                                <p
                                    v-if="form.errors.price"
                                    class="text-sm text-red-600 mt-1"
                                >
                                    {{ form.errors.price }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                    >السعر بعد الخصم</label
                                >
                                <input
                                    v-model="form.discounted_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    dir="ltr"
                                    placeholder="0.00"
                                    class="w-full py-2.5 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                    :class="{
                                        'border-red-300':
                                            form.errors.discounted_price,
                                    }"
                                />
                                <p
                                    v-if="form.errors.discounted_price"
                                    class="text-sm text-red-600 mt-1"
                                >
                                    {{ form.errors.discounted_price }}
                                </p>
                            </div>
                        </div>
                        <!-- Description -->
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 mb-2"
                                >الوصف</label
                            >
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="أدخل وصف المنتج..."
                                class="w-full py-2.5 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all resize-none"
                            ></textarea>
                        </div>
                        <!-- Images -->
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 mb-2"
                                >الصور</label
                            >
                            <div
                                @click="imagesInput?.click()"
                                class="border-2 border-dashed border-gray-300 rounded-xl p-5 text-center hover:border-primary-500 hover:bg-primary-50/30 transition-all cursor-pointer"
                            >
                                <svg
                                    class="w-8 h-8 mx-auto text-gray-400 mb-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                    />
                                </svg>
                                <p class="text-sm text-gray-600">
                                    اضغط لاختيار الصور
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    PNG, JPG, WebP — حد أقصى 5MB
                                </p>
                            </div>
                            <input
                                ref="imagesInput"
                                type="file"
                                accept="image/*"
                                multiple
                                @change="handleImages"
                                class="hidden"
                            />
                            <div
                                v-if="imagePreviews.length > 0"
                                class="grid grid-cols-4 gap-2 mt-3"
                            >
                                <div
                                    v-for="(preview, index) in imagePreviews"
                                    :key="index"
                                    class="relative group aspect-square"
                                >
                                    <img
                                        :src="preview"
                                        alt="معاينة"
                                        class="w-full h-full object-cover rounded-lg"
                                    />
                                    <button
                                        type="button"
                                        @click="removeImage(index)"
                                        class="absolute top-1 start-1 bg-red-500 text-white rounded-md p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-md"
                                    >
                                        <svg
                                            class="w-3 h-3"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Actions -->
                        <div
                            class="flex items-center gap-3 pt-2 border-t border-gray-100"
                        >
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 bg-primary-900 hover:bg-primary-800 text-white py-2.5 rounded-xl font-semibold shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <svg
                                    v-if="form.processing"
                                    class="w-4 h-4 animate-spin"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    />
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                    />
                                </svg>
                                {{
                                    form.processing
                                        ? "جاري الحفظ..."
                                        : "حفظ المنتج"
                                }}
                            </button>
                            <button
                                type="button"
                                @click="showAddModal = false"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all font-medium"
                            >
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
