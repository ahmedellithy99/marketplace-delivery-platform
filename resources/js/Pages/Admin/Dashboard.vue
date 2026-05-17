<script setup>
import { Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    pending_orders_count: { type: Number, default: 0 },
    active_deliveries_count: { type: Number, default: 0 },
    stores_count: { type: Number, default: 0 },
    customers_count: { type: Number, default: 0 },
    delivery_personnel_count: { type: Number, default: 0 },
    recent_orders: { type: Array, default: () => [] },
});

function formatPrice(price) {
    if (!price && price !== 0) return "";
    return Number(price).toFixed(2) + " جنيه";
}

function formatDate(dateStr) {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return date.toLocaleDateString("ar-EG", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

function formatTime(dateStr) {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return date.toLocaleTimeString("ar-EG", {
        hour: "2-digit",
        minute: "2-digit",
    });
}

const statusLabels = {
    pending: "قيد الانتظار",
    accepted: "مقبول",
    preparing: "جاري التحضير",
    on_the_way: "في الطريق",
    delivered: "تم التوصيل",
    cancelled: "ملغي",
};

const statusColors = {
    pending: "bg-yellow-50 text-yellow-700 border border-yellow-200",
    accepted: "bg-blue-50 text-blue-700 border border-blue-200",
    preparing: "bg-orange-50 text-orange-700 border border-orange-200",
    on_the_way: "bg-purple-50 text-purple-700 border border-purple-200",
    delivered: "bg-green-50 text-green-700 border border-green-200",
    cancelled: "bg-red-50 text-red-700 border border-red-200",
};

const statusDotColors = {
    pending: "bg-yellow-500",
    accepted: "bg-blue-500",
    preparing: "bg-orange-500",
    on_the_way: "bg-purple-500",
    delivered: "bg-green-500",
    cancelled: "bg-red-500",
};

const stats = [
    {
        label: "طلبات معلقة",
        key: "pending_orders_count",
        borderColor: "border-s-yellow-400",
        iconBg: "bg-yellow-50",
        iconColor: "text-yellow-600",
        icon: "clock",
    },
    {
        label: "توصيلات نشطة",
        key: "active_deliveries_count",
        borderColor: "border-s-blue-400",
        iconBg: "bg-blue-50",
        iconColor: "text-blue-600",
        icon: "truck",
    },
    {
        label: "المتاجر",
        key: "stores_count",
        borderColor: "border-s-green-400",
        iconBg: "bg-green-50",
        iconColor: "text-green-600",
        icon: "store",
    },
    {
        label: "العملاء",
        key: "customers_count",
        borderColor: "border-s-purple-400",
        iconBg: "bg-purple-50",
        iconColor: "text-purple-600",
        icon: "users",
    },
];
</script>

<template>
    <AdminLayout title="لوحة التحكم">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div
                v-for="stat in stats"
                :key="stat.key"
                class="bg-white rounded-xl border border-gray-100 border-s-4 p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-default"
                :class="stat.borderColor"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                        :class="[stat.iconBg, stat.iconColor]"
                    >
                        <!-- Clock -->
                        <svg
                            v-if="stat.icon === 'clock'"
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <!-- Truck -->
                        <svg
                            v-else-if="stat.icon === 'truck'"
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"
                            />
                        </svg>
                        <!-- Store -->
                        <svg
                            v-else-if="stat.icon === 'store'"
                            class="w-6 h-6"
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
                        <!-- Users -->
                        <svg
                            v-else-if="stat.icon === 'users'"
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-3xl font-bold text-gray-900 tabular-nums"
                        >
                            {{ props[stat.key] }}
                        </p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ stat.label }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
        >
            <div
                class="px-6 py-5 border-b border-gray-100 flex items-center justify-between"
            >
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        أحدث الطلبات
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        آخر الطلبات الواردة للمنصة
                    </p>
                </div>
                <Link
                    href="/admin/orders"
                    class="inline-flex items-center gap-1.5 text-sm text-secondary-500 hover:text-secondary-600 font-medium transition-colors"
                >
                    عرض الكل
                    <svg
                        class="w-4 h-4 rtl:rotate-180"
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
                </Link>
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
                                العميل
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
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in recent_orders"
                            :key="order.id"
                            class="border-t border-gray-100 hover:bg-gray-50/50 transition-colors even:bg-gray-50/30"
                        >
                            <td class="px-5 py-4">
                                <span
                                    class="font-semibold text-primary-900 tabular-nums"
                                    >#{{ order.order_number }}</span
                                >
                            </td>
                            <td class="px-5 py-4 text-gray-700">
                                {{ order.user?.name || "-" }}
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                                    :class="
                                        statusColors[order.status] ||
                                        'bg-gray-50 text-gray-700 border border-gray-200'
                                    "
                                >
                                    <span
                                        class="w-1.5 h-1.5 rounded-full"
                                        :class="
                                            statusDotColors[order.status] ||
                                            'bg-gray-400'
                                        "
                                    ></span>
                                    {{
                                        statusLabels[order.status] ||
                                        order.status
                                    }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="font-semibold text-gray-900 tabular-nums"
                                    >{{ formatPrice(order.total) }}</span
                                >
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-gray-700">
                                    {{ formatDate(order.created_at) }}
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ formatTime(order.created_at) }}
                                </div>
                            </td>
                        </tr>
                        <tr v-if="recent_orders.length === 0">
                            <td colspan="5" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div
                                        class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center"
                                    >
                                        <svg
                                            class="w-7 h-7 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                            />
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 font-medium">
                                        لا توجد طلبات حتى الآن
                                    </p>
                                    <p class="text-sm text-gray-400">
                                        ستظهر الطلبات الجديدة هنا
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
