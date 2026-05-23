<script setup>
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed, ref } from "vue";

const props = defineProps({
    deliveries: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    currentMonth: {
        type: String,
        default: "",
    },
    deliveryMenEarnings: {
        type: Array,
        default: () => [],
    },
});

const month = ref(props.currentMonth || new Date().toISOString().slice(0, 7));

function changeMonth() {
    router.get(
        "/admin/deliveries",
        { month: month.value },
        { preserveState: true },
    );
}

const deliveriesList = computed(() => props.deliveries?.data || []);
const paginationLinks = computed(() => props.deliveries?.links || []);

function formatDate(dateStr) {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return date.toLocaleDateString("ar-EG", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

const deliveryStatusLabels = {
    assigned: "تم التعيين",
    picked_up: "تم الاستلام",
    on_the_way: "في الطريق",
    delivered: "تم التوصيل",
};

const deliveryStatusColors = {
    assigned: "bg-blue-100 text-blue-700",
    picked_up: "bg-orange-100 text-orange-700",
    on_the_way: "bg-purple-100 text-purple-700",
    delivered: "bg-green-100 text-green-700",
};

function getStatus(delivery) {
    if (delivery.delivered_at) return "delivered";
    if (delivery.picked_up_at) return "on_the_way";
    return "assigned";
}
</script>

<template>
    <AdminLayout title="التوصيلات">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-primary-900">
                    إدارة التوصيلات
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ deliveries.total || 0 }} توصيل
                </p>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-600">الشهر:</label>
                <input
                    v-model="month"
                    type="month"
                    @change="changeMonth"
                    class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                />
            </div>
        </div>

        <!-- Delivery Men Earnings -->
        <div
            v-if="deliveryMenEarnings.length > 0"
            class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6"
        >
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-900">
                    أرباح المناديب — هذا الشهر
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th
                                class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                المندوب
                            </th>
                            <th
                                class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الهاتف
                            </th>
                            <th
                                class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                عدد التوصيلات
                            </th>
                            <th
                                class="px-5 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                إجمالي الأرباح
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="dm in deliveryMenEarnings"
                            :key="dm.id"
                            class="border-t border-gray-100 hover:bg-gray-50/50"
                        >
                            <td class="px-5 py-3 font-medium text-gray-900">
                                {{ dm.name }}
                            </td>
                            <td
                                class="px-5 py-3 text-gray-600 tabular-nums"
                                dir="ltr"
                            >
                                {{ dm.phone || "—" }}
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700"
                                >
                                    {{ dm.deliveries_count }} توصيل
                                </span>
                            </td>
                            <td
                                class="px-5 py-3 font-bold text-green-700 tabular-nums"
                            >
                                {{ dm.total_fees.toFixed(2) }} جنيه
                            </td>
                        </tr>
                        <tr v-if="deliveryMenEarnings.length === 0">
                            <td
                                colspan="4"
                                class="px-5 py-8 text-center text-gray-400"
                            >
                                لا توجد بيانات
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
                                المندوب
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                الحالة
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                تاريخ التعيين
                            </th>
                            <th class="px-5 py-3 text-start font-medium">
                                الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="delivery in deliveriesList"
                            :key="delivery.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-5 py-3 font-medium text-gray-900">
                                #{{ delivery.order?.order_number || "-" }}
                            </td>
                            <td class="px-5 py-3 text-gray-600">
                                {{
                                    delivery.delivery_man?.name ||
                                    delivery.deliveryMan?.name ||
                                    "-"
                                }}
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="
                                        deliveryStatusColors[
                                            getStatus(delivery)
                                        ] || 'bg-gray-100 text-gray-700'
                                    "
                                >
                                    {{
                                        deliveryStatusLabels[
                                            getStatus(delivery)
                                        ] || "غير معروف"
                                    }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500">
                                {{
                                    formatDate(
                                        delivery.assigned_at ||
                                            delivery.created_at,
                                    )
                                }}
                            </td>
                            <td class="px-5 py-3">
                                <Link
                                    v-if="delivery.order_id"
                                    :href="`/admin/orders/${delivery.order_id}`"
                                    class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                                >
                                    عرض الطلب
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="deliveriesList.length === 0">
                            <td
                                colspan="5"
                                class="px-5 py-8 text-center text-gray-500"
                            >
                                لا توجد توصيلات
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
