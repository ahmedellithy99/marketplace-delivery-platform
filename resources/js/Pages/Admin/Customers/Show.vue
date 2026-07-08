<script setup>
import { Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed } from "vue";

const props = defineProps({
    customer: { type: Object, required: true },
    orders: { type: Object, default: () => ({ data: [], links: [] }) },
});

const ordersList = computed(() => props.orders?.data || []);
const paginationLinks = computed(() => props.orders?.links || []);

const statusColors = {
    pending: "bg-yellow-100 text-yellow-800",
    accepted: "bg-blue-100 text-blue-800",
    preparing: "bg-indigo-100 text-indigo-800",
    on_the_way: "bg-purple-100 text-purple-800",
    delivered: "bg-green-100 text-green-800",
    cancelled: "bg-red-100 text-red-800",
};

const statusLabels = {
    pending: "قيد الانتظار",
    accepted: "تم القبول",
    preparing: "جاري التحضير",
    on_the_way: "في الطريق",
    delivered: "تم التوصيل",
    cancelled: "ملغي",
};

function formatPrice(price) {
    if (!price && price !== 0) return "0.00";
    return Number(price).toFixed(2);
}
</script>

<template>
    <AdminLayout :title="`${customer.name} — العميل`">
        <!-- Back Link -->
        <Link
            href="/admin/customers"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-900 hover:text-secondary-500 transition-colors mb-6"
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
                    d="M15 19l-7-7 7-7"
                />
            </svg>
            العودة للعملاء
        </Link>

        <!-- Customer Info Card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">
                بيانات العميل
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <span class="block text-xs text-gray-400 mb-1"
                        >الاسم</span
                    >
                    <span class="font-medium text-gray-900">{{
                        customer.name
                    }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400 mb-1"
                        >الهاتف</span
                    >
                    <span class="font-medium text-gray-900">{{
                        customer.phone
                    }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400 mb-1"
                        >البريد الإلكتروني</span
                    >
                    <span class="font-medium text-gray-900">{{
                        customer.email || "—"
                    }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400 mb-1"
                        >عدد الطلبات</span
                    >
                    <span class="font-medium text-gray-900">{{
                        customer.orders_count || 0
                    }}</span>
                </div>
            </div>
        </div>

        <!-- Orders Section -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6 pb-0">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    طلبات العميل
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                رقم الطلب
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الحالة
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الإجمالي
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                التاريخ
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
                            v-for="order in ordersList"
                            :key="order.id"
                            class="border-t border-gray-100 hover:bg-gray-50/50 transition-colors"
                        >
                            <td class="px-5 py-4">
                                <span class="font-medium text-gray-900">{{
                                    order.order_number
                                }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium"
                                    :class="
                                        statusColors[order.status] ||
                                        'bg-gray-100 text-gray-700'
                                    "
                                >
                                    {{
                                        statusLabels[order.status] ||
                                        order.status
                                    }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-gray-900"
                                    >{{ formatPrice(order.total) }}
                                    <span
                                        class="text-xs font-normal text-gray-500"
                                        >جنيه</span
                                    ></span
                                >
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-gray-500 text-xs">{{
                                    new Date(
                                        order.created_at,
                                    ).toLocaleDateString("ar-EG")
                                }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <Link
                                    :href="`/admin/orders/${order.id}`"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 transition-all duration-200"
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
                            </td>
                        </tr>
                        <tr v-if="ordersList.length === 0">
                            <td
                                colspan="5"
                                class="px-5 py-12 text-center text-gray-500"
                            >
                                لا توجد طلبات لهذا العميل
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav
                v-if="paginationLinks.length > 3"
                class="flex items-center justify-center gap-1 px-5 py-4 border-t border-gray-100"
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
