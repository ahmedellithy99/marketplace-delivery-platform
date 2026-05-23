<script setup>
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { ref, computed } from "vue";

const props = defineProps({
    deliveryMan: { type: Object, required: true },
    deliveries: { type: Object, default: () => ({ data: [], links: [] }) },
    currentMonth: { type: String, default: "" },
    stats: { type: Object, default: () => ({}) },
});

const month = ref(props.currentMonth || new Date().toISOString().slice(0, 7));

function changeMonth() {
    router.get(
        `/admin/deliveries/men/${props.deliveryMan.id}`,
        { month: month.value },
        { preserveState: true },
    );
}

function formatDate(dateStr) {
    if (!dateStr) return "—";
    return new Date(dateStr).toLocaleDateString("ar-EG", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function formatPrice(price) {
    if (!price && price !== 0) return "0.00";
    return Number(price).toFixed(2);
}

const statusLabels = {
    assigned: "تم التعيين",
    pickedUp: "تم الاستلام",
    onTheWay: "في الطريق",
    completed: "تم التوصيل",
};
const statusColors = {
    assigned: "bg-yellow-100 text-yellow-700",
    pickedUp: "bg-blue-100 text-blue-700",
    onTheWay: "bg-orange-100 text-orange-700",
    completed: "bg-green-100 text-green-700",
};

function getStatus(delivery) {
    if (delivery.delivered_at) return "completed";
    if (delivery.picked_up_at) return "onTheWay";
    return "assigned";
}
</script>

<template>
    <AdminLayout :title="`المندوب: ${deliveryMan.name}`">
        <!-- Back -->
        <Link
            href="/admin/deliveries"
            class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-primary-900 mb-6 transition-colors"
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
                    d="M9 5l7 7-7 7"
                />
            </svg>
            العودة للتوصيلات
        </Link>

        <!-- Delivery Man Info Card -->
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6"
        >
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 bg-primary-100 rounded-full flex items-center justify-center text-primary-700 text-xl font-bold"
                >
                    {{ deliveryMan.name?.charAt(0) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        {{ deliveryMan.name }}
                    </h1>
                    <div
                        class="flex items-center gap-4 mt-1 text-sm text-gray-500"
                    >
                        <span v-if="deliveryMan.phone" dir="ltr">{{
                            deliveryMan.phone
                        }}</span>
                        <span v-if="deliveryMan.email">{{
                            deliveryMan.email
                        }}</span>
                    </div>
                </div>
                <div class="ms-auto">
                    <span
                        v-if="stats.active_count > 0"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-700"
                    >
                        <span
                            class="w-1.5 h-1.5 bg-orange-500 rounded-full animate-pulse"
                        ></span>
                        {{ stats.active_count }} توصيل نشط
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700"
                    >
                        <span
                            class="w-1.5 h-1.5 bg-green-500 rounded-full"
                        ></span>
                        متاح
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500 font-medium">توصيلات الشهر</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    {{ stats.month_completed }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500 font-medium">أرباح الشهر</p>
                <p class="text-2xl font-bold text-green-700 mt-1">
                    {{ formatPrice(stats.month_fees) }}
                    <span class="text-sm font-normal text-gray-400">جنيه</span>
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500 font-medium">
                    إجمالي التوصيلات
                </p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    {{ stats.total_completed }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500 font-medium">إجمالي الأرباح</p>
                <p class="text-2xl font-bold text-green-700 mt-1">
                    {{ formatPrice(stats.total_fees) }}
                    <span class="text-sm font-normal text-gray-400">جنيه</span>
                </p>
            </div>
        </div>

        <!-- Month Filter + Deliveries Table -->
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
        >
            <div
                class="flex items-center justify-between px-5 py-4 border-b border-gray-100"
            >
                <h3 class="text-sm font-bold text-gray-900">سجل التوصيلات</h3>
                <input
                    v-model="month"
                    type="month"
                    @change="changeMonth"
                    class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none"
                />
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th
                                class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الطلب
                            </th>
                            <th
                                class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                التاريخ
                            </th>
                            <th
                                class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الحالة
                            </th>
                            <th
                                class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                رسوم التوصيل
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="delivery in deliveries.data"
                            :key="delivery.id"
                            class="border-t border-gray-100 hover:bg-gray-50/50"
                        >
                            <td class="px-5 py-3">
                                <Link
                                    :href="`/admin/orders/${delivery.order?.id}`"
                                    class="font-medium text-primary-700 hover:text-primary-900"
                                >
                                    #{{ delivery.order?.order_number || "—" }}
                                </Link>
                            </td>
                            <td class="px-5 py-3 text-gray-600 text-xs">
                                {{ formatDate(delivery.assigned_at) }}
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium"
                                    :class="statusColors[getStatus(delivery)]"
                                >
                                    {{ statusLabels[getStatus(delivery)] }}
                                </span>
                            </td>
                            <td
                                class="px-5 py-3 font-semibold text-gray-900 tabular-nums"
                            >
                                {{
                                    formatPrice(delivery.order?.delivery_fee)
                                }}
                                جنيه
                            </td>
                        </tr>
                        <tr v-if="deliveries.data.length === 0">
                            <td
                                colspan="4"
                                class="px-5 py-12 text-center text-gray-400"
                            >
                                لا توجد توصيلات في هذا الشهر
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <nav
                v-if="deliveries.links && deliveries.links.length > 3"
                class="flex items-center justify-center gap-1 px-5 py-4 border-t border-gray-100"
            >
                <template
                    v-for="(link, index) in deliveries.links"
                    :key="index"
                >
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
