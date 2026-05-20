<script setup>
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed } from "vue";

const props = defineProps({
    discounts: { type: Object, default: () => ({ data: [], links: [] }) },
});

const discountsList = computed(() => props.discounts?.data || []);
const paginationLinks = computed(() => props.discounts?.links || []);

function toggleActive(discount) {
    router.patch(`/admin/discounts/${discount.id}/toggle-active`);
}

function deleteDiscount(discount) {
    if (confirm(`هل أنت متأكد من حذف الخصم "${discount.name}"؟`)) {
        router.delete(`/admin/discounts/${discount.id}`);
    }
}

function scopeLabel(scope) {
    return (
        { product: "منتج", variant: "متغير", store: "متجر", category: "قسم" }[
            scope
        ] || scope
    );
}

function typeLabel(type) {
    return type === "percentage" ? "نسبة مئوية" : "مبلغ ثابت";
}

function formatValue(discount) {
    return discount.type === "percentage"
        ? `${discount.value}%`
        : `${discount.value} جنيه`;
}

function isExpired(discount) {
    if (!discount.ends_at) return false;
    return new Date(discount.ends_at) < new Date();
}
</script>

<template>
    <AdminLayout title="الخصومات">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">إدارة الخصومات</h2>
                <p class="text-sm text-gray-500 mt-1">
                    إنشاء وإدارة خصومات المنتجات والمتاجر
                </p>
            </div>
            <Link
                href="/admin/discounts/create"
                class="inline-flex items-center gap-2 bg-primary-900 text-white px-5 py-2.5 rounded-lg hover:bg-primary-800 shadow-sm hover:shadow-md transition-all text-sm font-medium"
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
                إضافة خصم
            </Link>
        </div>

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
                                الخصم
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                النوع
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                القيمة
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                النطاق
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الحالة
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
                            v-for="discount in discountsList"
                            :key="discount.id"
                            class="border-t border-gray-100 hover:bg-gray-50/50 transition-colors"
                        >
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900">
                                    {{ discount.name }}
                                </p>
                                <p
                                    v-if="discount.ends_at"
                                    class="text-xs text-gray-400 mt-0.5"
                                >
                                    ينتهي:
                                    {{
                                        new Date(
                                            discount.ends_at,
                                        ).toLocaleDateString("ar-EG")
                                    }}
                                </p>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ typeLabel(discount.type) }}
                            </td>
                            <td
                                class="px-5 py-4 font-semibold text-secondary-500"
                            >
                                {{ formatValue(discount) }}
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200"
                                >
                                    {{ scopeLabel(discount.scope) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <button
                                    @click="toggleActive(discount)"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                                    :class="
                                        discount.is_active &&
                                        !isExpired(discount)
                                            ? 'bg-green-500'
                                            : 'bg-gray-300'
                                    "
                                >
                                    <span
                                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform"
                                        :class="
                                            discount.is_active &&
                                            !isExpired(discount)
                                                ? '-translate-x-6'
                                                : '-translate-x-1'
                                        "
                                    />
                                </button>
                                <span
                                    v-if="isExpired(discount)"
                                    class="block text-[10px] text-red-500 mt-1"
                                    >منتهي</span
                                >
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="`/admin/discounts/${discount.id}/edit`"
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
                                        @click="deleteDiscount(discount)"
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
                        <tr v-if="discountsList.length === 0">
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
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"
                                            />
                                        </svg>
                                    </div>
                                    <p class="text-gray-700 font-medium">
                                        لا توجد خصومات
                                    </p>
                                    <Link
                                        href="/admin/discounts/create"
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
                                        إضافة خصم
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <nav
                v-if="paginationLinks.length > 3"
                class="flex items-center justify-center gap-1 px-5 py-4 border-t border-gray-100"
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
    </AdminLayout>
</template>
