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
    return Number(price).toFixed(2) + " جنيه";
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
</script>

<template>
    <AdminLayout title="المنتجات">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-primary-900">إدارة المنتجات</h2>
            <Link
                href="/admin/products/create"
                class="inline-flex items-center gap-2 bg-primary-900 text-white px-4 py-2.5 rounded-lg hover:bg-primary-800 transition-colors text-sm font-medium"
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
            class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-5 py-3 text-start font-medium">
                                الصورة
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                الاسم
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                المتجر
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                القسم
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                السعر
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                متاح
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="product in productsList"
                            :key="product.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-5 py-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden"
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
                                                stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 font-medium text-gray-900">
                                {{ product.name }}
                            </td>
                            <td class="px-5 py-3 text-gray-600">
                                {{ product.store?.name || "-" }}
                            </td>
                            <td class="px-5 py-3 text-gray-600">
                                {{ product.category?.name || "-" }}
                            </td>
                            <td class="px-5 py-3 font-medium text-gray-900">
                                {{ formatPrice(product.price) }}
                            </td>
                            <td class="px-5 py-3">
                                <button
                                    @click="toggleAvailability(product)"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                                    :class="
                                        product.is_available
                                            ? 'bg-green-500'
                                            : 'bg-gray-300'
                                    "
                                >
                                    <span
                                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                        :class="
                                            product.is_available
                                                ? '-translate-x-6'
                                                : '-translate-x-1'
                                        "
                                    />
                                </button>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="`/admin/products/${product.slug}/edit`"
                                        class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                                    >
                                        تعديل
                                    </Link>
                                    <button
                                        @click="deleteProduct(product)"
                                        class="text-red-600 hover:text-red-800 text-xs font-medium"
                                    >
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="productsList.length === 0">
                            <td
                                colspan="7"
                                class="px-5 py-8 text-center text-gray-500"
                            >
                                لا توجد منتجات
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav
                v-if="paginationLinks.length > 3"
                class="flex items-center justify-center gap-1 p-4 border-t border-gray-200"
                aria-label="التنقل بين الصفحات"
            >
                <template v-for="(link, index) in paginationLinks" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="px-3 py-2 text-sm rounded-lg transition-colors"
                        :class="
                            link.active
                                ? 'bg-primary-900 text-white'
                                : 'text-gray-600 hover:bg-gray-100'
                        "
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="px-3 py-2 text-sm text-gray-400"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </div>
    </AdminLayout>
</template>
