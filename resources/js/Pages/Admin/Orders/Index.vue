<script setup>
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed, ref } from "vue";

const props = defineProps({
    orders: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
});

const ordersList = computed(() => props.orders?.data || []);
const paginationLinks = computed(() => props.orders?.links || []);

const statusFilter = ref("");

const statusOptions = [
    { value: "", label: "الكل" },
    { value: "pending", label: "قيد الانتظار" },
    { value: "accepted", label: "مقبول" },
    { value: "preparing", label: "جاري التحضير" },
    { value: "on_the_way", label: "في الطريق" },
    { value: "delivered", label: "تم التوصيل" },
    { value: "cancelled", label: "ملغي" },
];

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

function filterByStatus(status) {
    statusFilter.value = status;
    router.get("/admin/orders", status ? { status } : {}, {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <AdminLayout title="الطلبات">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-primary-900">إدارة الطلبات</h2>
        </div>

        <!-- Status Filter -->
        <div class="flex flex-wrap gap-2 mb-6">
            <button
                v-for="option in statusOptions"
                :key="option.value"
                @click="filterByStatus(option.value)"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                :class="
                    statusFilter === option.value
                        ? 'bg-primary-900 text-white'
                        : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
                "
            >
                {{ option.label }}
            </button>
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
                            <th class="px-5 py-3 text-start font-medium">
                                الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="order in ordersList"
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
                            <td class="px-5 py-3">
                                <Link
                                    :href="`/admin/orders/${order.id}`"
                                    class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                                >
                                    عرض
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="ordersList.length === 0">
                            <td
                                colspan="6"
                                class="px-5 py-8 text-center text-gray-500"
                            >
                                لا توجد طلبات
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
