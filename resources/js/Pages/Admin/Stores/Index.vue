<script setup>
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed } from "vue";

const props = defineProps({
    stores: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
});

const storesList = computed(() => props.stores?.data || []);
const paginationLinks = computed(() => props.stores?.links || []);

function deleteStore(store) {
    if (confirm(`هل أنت متأكد من حذف المتجر "${store.name}"؟`)) {
        router.delete(`/admin/stores/${store.slug}`);
    }
}

function getLogoUrl(store) {
    if (store.media && store.media.length > 0) {
        return store.media[0].original_url || store.media[0].url;
    }
    return null;
}
</script>

<template>
    <AdminLayout title="المتاجر">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">إدارة المتاجر</h2>
                <p class="text-sm text-gray-500 mt-1">
                    إدارة جميع المتاجر المسجلة في المنصة
                </p>
            </div>
            <Link
                href="/admin/stores/create"
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
                إضافة متجر
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
                                المتجر
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                النوع
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الهاتف
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الحالة
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                متاح
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
                            v-for="store in storesList"
                            :key="store.id"
                            class="border-t border-gray-100 hover:bg-gray-50/50 transition-colors"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden shrink-0 border border-gray-200"
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
                                                class="w-5 h-5"
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
                                    <span class="font-medium text-gray-900">{{
                                        store.name
                                    }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ store.store_type?.name || "-" }}
                            </td>
                            <td
                                class="px-5 py-4 text-gray-600 tabular-nums"
                                dir="ltr"
                            >
                                {{ store.phone || "-" }}
                            </td>
                            <td class="px-5 py-4">
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
                                        :class="
                                            store.is_open
                                                ? 'bg-green-500'
                                                : 'bg-red-500'
                                        "
                                    ></span>
                                    {{ store.is_open ? "مفتوح" : "مغلق" }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <button
                                    @click="
                                        router.patch(
                                            `/admin/stores/${store.slug}/toggle-availability`,
                                        )
                                    "
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                                    :class="
                                        store.is_available
                                            ? 'bg-green-500'
                                            : 'bg-gray-300'
                                    "
                                    :title="
                                        store.is_available
                                            ? 'اضغط لإخفاء المتجر'
                                            : 'اضغط لإظهار المتجر'
                                    "
                                >
                                    <span
                                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform"
                                        :class="
                                            store.is_available
                                                ? '-translate-x-6'
                                                : '-translate-x-1'
                                        "
                                    />
                                </button>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="`/admin/stores/${store.slug}`"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-primary-700 bg-primary-50 border border-primary-200 hover:bg-primary-100 transition-all duration-200 focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
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
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>
                                        عرض
                                    </Link>
                                    <Link
                                        :href="`/admin/stores/${store.slug}/edit`"
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
                                        @click="deleteStore(store)"
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
                        <tr v-if="storesList.length === 0">
                            <td colspan="6" class="px-5 py-16 text-center">
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
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-gray-700 font-medium">
                                            لا توجد متاجر
                                        </p>
                                        <p class="text-sm text-gray-400 mt-1">
                                            ابدأ بإضافة أول متجر للمنصة
                                        </p>
                                    </div>
                                    <Link
                                        href="/admin/stores/create"
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
                                        إضافة متجر
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
