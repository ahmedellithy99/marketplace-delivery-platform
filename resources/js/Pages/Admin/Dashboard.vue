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

const statusLabels = {
    pending: "قيد الانتظار",
    accepted: "مقبول",
    preparing: "جاري التحضير",
    on_the_way: "في الطريق",
    delivered: "تم التوصيل",
    cancelled: "ملغي",
};

const statusColors = {
    pending: "bg-yellow-100 text-yellow-700",
    accepted: "bg-blue-100 text-blue-700",
    preparing: "bg-orange-100 text-orange-700",
    on_the_way: "bg-purple-100 text-purple-700",
    delivered: "bg-green-100 text-green-700",
    cancelled: "bg-red-100 text-red-700",
};

const stats = [
    {
        label: "طلبات معلقة",
        key: "pending_orders_count",
        color: "bg-yellow-50 border-yellow-200 text-yellow-700",
        iconColor: "bg-yellow-100 text-yellow-600",
        icon: "clock",
    },
    {
        label: "توصيلات نشطة",
        key: "active_deliveries_count",
        color: "bg-blue-50 border-blue-200 text-blue-700",
        iconColor: "bg-blue-100 text-blue-600",
        icon: "truck",
    },
    {
        label: "المتاجر",
        key: "stores_count",
        color: "bg-green-50 border-green-200 text-green-700",
        iconColor: "bg-green-100 text-green-600",
        icon: "store",
    },
    {
        label: "العملاء",
        key: "customers_count",
        color: "bg-purple-50 border-purple-200 text-purple-700",
        iconColor: "bg-purple-100 text-purple-600",
        icon: "users",
    },
];
</script>

<template>
    <AdminLayout title="لوحة التحكم">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div
                v-for="stat in stats"
                :key="stat.key"
                class="rounded-xl border p-5"
                :class="stat.color"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-lg flex items-center justify-center"
                        :class="stat.iconColor"
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
                                stroke-width="2"
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
                                stroke-width="2"
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
                                stroke-width="2"
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
                                stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ props[stat.key] }}</p>
                        <p class="text-sm font-medium opacity-80">
                            {{ stat.label }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div
                class="p-5 border-b border-gray-200 flex items-center justify-between"
            >
                <h2 class="text-lg font-bold text-primary-900">أحدث الطلبات</h2>
                <Link
                    href="/admin/orders"
                    class="text-sm text-secondary-500 hover:text-secondary-600 font-medium"
                >
                    عرض الكل
                </Link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-5 py-3 text-start font-medium">
                                رقم الطلب
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                العميل
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                الحالة
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                الإجمالي
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                التاريخ
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="order in recent_orders"
                            :key="order.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-5 py-3 font-medium text-gray-900">
                                #{{ order.order_number }}
                            </td>
                            <td class="px-5 py-3 text-gray-600">
                                {{ order.user?.name || "-" }}
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
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
                            <td class="px-5 py-3 font-medium text-gray-900">
                                {{ formatPrice(order.total) }}
                            </td>
                            <td class="px-5 py-3 text-gray-500">
                                {{ formatDate(order.created_at) }}
                            </td>
                        </tr>
                        <tr v-if="recent_orders.length === 0">
                            <td
                                colspan="5"
                                class="px-5 py-8 text-center text-gray-500"
                            >
                                لا توجد طلبات حتى الآن
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
