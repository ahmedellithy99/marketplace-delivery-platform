<script setup>
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed } from "vue";

const props = defineProps({
    categories: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
});

const categoriesList = computed(() => props.categories?.data || []);
const paginationLinks = computed(() => props.categories?.links || []);

function deleteCategory(category) {
    if (confirm(`هل أنت متأكد من حذف القسم "${category.name}"؟`)) {
        router.delete(`/admin/categories/${category.slug}`);
    }
}

function getCategoryImage(category) {
    if (category.media && category.media.length > 0) {
        return category.media[0].original_url || category.media[0].url;
    }
    return null;
}
</script>

<template>
    <AdminLayout title="الأقسام">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">إدارة الأقسام</h2>
                <p class="text-sm text-gray-500 mt-1">
                    تنظيم أقسام وتصنيفات المنتجات
                </p>
            </div>
            <Link
                href="/admin/categories/create"
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
                إضافة قسم
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
                                القسم
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                القسم الأب
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
                            v-for="category in categoriesList"
                            :key="category.id"
                            class="border-t border-gray-100 hover:bg-gray-50/50 transition-colors"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <!-- Category Image -->
                                    <div
                                        class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden shrink-0 border border-gray-200"
                                    >
                                        <img
                                            v-if="getCategoryImage(category)"
                                            :src="getCategoryImage(category)"
                                            :alt="category.name"
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
                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                    <!-- Indent child categories -->
                                    <div>
                                        <span
                                            class="font-medium text-gray-900"
                                            >{{ category.name }}</span
                                        >
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    v-if="category.parent?.name"
                                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200"
                                >
                                    {{ category.parent.name }}
                                </span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="`/admin/categories/${category.slug}/edit`"
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
                                        @click="deleteCategory(category)"
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
                        <tr v-if="categoriesList.length === 0">
                            <td colspan="3" class="px-5 py-16 text-center">
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
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-gray-700 font-medium">
                                            لا توجد أقسام
                                        </p>
                                        <p class="text-sm text-gray-400 mt-1">
                                            ابدأ بإضافة أول قسم لتنظيم المنتجات
                                        </p>
                                    </div>
                                    <Link
                                        href="/admin/categories/create"
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
                                        إضافة قسم
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
