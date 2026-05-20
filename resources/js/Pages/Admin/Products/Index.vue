<script setup>
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed } from "vue";

const props = defineProps({
    products: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
});

const productsList = computed(() => props.products?.data || []);
const paginationLinks = computed(() => props.products?.links || []);

function formatPrice(price) {
    if (!price && price !== 0) return "";
    return Number(price).toFixed(2);
}

function toggleAvailability(product) {
    router.patch(`/admin/products/${product.slug}/toggle-availability`);
}

function deleteProduct(product) {
    if (confirm(`هل أنت متأكد من حذف المنتج "${product.name}"؟`)) {
        router.delete(`/admin/products/${product.slug}`);
    }
}

function getImageUrl(product) {
    if (product.media && product.media.length > 0) {
        return product.media[0].original_url || product.media[0].url;
    }
    return null;
}

function hasDiscount(product) {
    return product.pricing?.has_discount || false;
}

function discountPercentage(product) {
    if (!hasDiscount(product) || !product.pricing) return 0;
    const unit = product.pricing.unit_price;
    const effective = product.pricing.effective_price;
    if (!unit || unit <= 0) return 0;
    return Math.round((1 - effective / unit) * 100);
}
</script>

<template>
    <AdminLayout title="المنتجات">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">إدارة المنتجات</h2>
                <p class="text-sm text-gray-500 mt-1">
                    عرض وإدارة جميع المنتجات المتاحة
                </p>
            </div>
            <Link
                href="/admin/products/create"
                class="inline-flex items-center gap-2 bg-primary-900 text-white px-5 py-2.5 rounded-lg hover:bg-primary-800 shadow-sm hover:shadow-md transition-all duration-200 text-sm font-medium focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
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
            </Link>
        </div>

        <!-- Table -->
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
                            <!-- Product with Image -->
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
                                        <!-- Discount Badge -->
                                        <span
                                            v-if="hasDiscount(product)"
                                            class="absolute top-0 start-0 bg-red-500 text-white text-[9px] font-bold px-1 py-0.5 rounded-ee-md"
                                        >
                                            -{{ discountPercentage(product) }}%
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p
                                            class="font-medium text-gray-900 truncate"
                                        >
                                            {{ product.name }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-400 mt-0.5 truncate"
                                        >
                                            {{ product.store?.name || "" }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <!-- Category -->
                            <td class="px-5 py-4">
                                <span
                                    v-if="product.category?.name"
                                    class="text-gray-600"
                                    >{{ product.category.name }}</span
                                >
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <!-- Price -->
                            <td class="px-5 py-4">
                                <div class="tabular-nums">
                                    <div
                                        v-if="hasDiscount(product)"
                                        class="flex flex-col"
                                    >
                                        <span
                                            class="font-semibold text-green-700"
                                            >{{
                                                formatPrice(
                                                    product.pricing
                                                        .effective_price,
                                                )
                                            }}
                                            <span
                                                class="text-xs font-normal text-gray-500"
                                                >جنيه</span
                                            ></span
                                        >
                                        <span
                                            class="text-xs text-gray-400 line-through"
                                            >{{
                                                formatPrice(
                                                    product.pricing.unit_price,
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div v-else>
                                        <span
                                            class="font-semibold text-gray-900"
                                            >{{
                                                formatPrice(
                                                    product.pricing
                                                        ?.effective_price ||
                                                        product.base_price,
                                                )
                                            }}
                                            <span
                                                class="text-xs font-normal text-gray-500"
                                                >جنيه</span
                                            ></span
                                        >
                                    </div>
                                </div>
                            </td>
                            <!-- Availability Toggle -->
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
                                            ? 'متاح - اضغط لإيقاف'
                                            : 'غير متاح - اضغط للتفعيل'
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
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 transition-all duration-200 focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
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
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition-all duration-200 focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
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
                                            ابدأ بإضافة أول منتج للمنصة
                                        </p>
                                    </div>
                                    <Link
                                        href="/admin/products/create"
                                        class="inline-flex items-center gap-2 bg-primary-900 text-white px-4 py-2 rounded-lg hover:bg-primary-800 transition-all duration-200 text-sm font-medium"
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
                                    </Link>
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
                        class="px-3.5 py-2 text-sm rounded-lg transition-all duration-200 font-medium"
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
    </AdminLayout>
</template>
