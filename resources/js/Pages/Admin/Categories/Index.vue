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
</script>

<template>
    <AdminLayout title="الأقسام">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-primary-900">إدارة الأقسام</h2>
            <Link
                href="/admin/categories/create"
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
                إضافة قسم
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
                                الاسم
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                القسم الأب
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="category in categoriesList"
                            :key="category.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-5 py-3 font-medium text-gray-900">
                                {{ category.name }}
                            </td>
                            <td class="px-5 py-3 text-gray-600">
                                {{ category.parent?.name || "-" }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="`/admin/categories/${category.slug}/edit`"
                                        class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                                    >
                                        تعديل
                                    </Link>
                                    <button
                                        @click="deleteCategory(category)"
                                        class="text-red-600 hover:text-red-800 text-xs font-medium"
                                    >
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="categoriesList.length === 0">
                            <td
                                colspan="3"
                                class="px-5 py-8 text-center text-gray-500"
                            >
                                لا توجد أقسام
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
