<script setup>
import { useForm, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { ref } from "vue";

const props = defineProps({
    order: { type: Object, required: true },
    groupedItems: { type: Array, default: () => [] },
    deliveryPersonnel: { type: Array, default: () => [] },
});

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
        hour: "2-digit",
        minute: "2-digit",
    });
}

// Accept form
const acceptForm = useForm({
    delivery_fee: "",
});

function acceptOrder() {
    acceptForm.post(`/admin/orders/${props.order.id}/accept`);
}

// Cancel
function cancelOrder() {
    if (confirm("هل أنت متأكد من إلغاء هذا الطلب؟")) {
        router.post(`/admin/orders/${props.order.id}/cancel`);
    }
}

// Assign delivery
const assignForm = useForm({
    delivery_man_id: "",
});

function assignDelivery() {
    assignForm.post(`/admin/orders/${props.order.id}/assign-delivery`);
}

function deleteOrder() {
    if (
        !confirm(
            "هل أنت متأكد من حذف هذا الطلب؟ سيتم حذف التوصيل المرتبط أيضاً.",
        )
    )
        return;
    router.delete(`/admin/orders/${props.order.id}`);
}
</script>

<template>
    <AdminLayout title="تفاصيل الطلب">
        <div class="max-w-4xl">
            <!-- Back Link -->
            <Link
                href="/admin/orders"
                class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-900 mb-6"
            >
                <svg
                    class="w-4 h-4 rotate-180"
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
                العودة للطلبات
            </Link>

            <!-- Order Header -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
            >
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                    <div>
                        <h2 class="text-xl font-bold text-primary-900 mb-1">
                            طلب #{{ order.order_number }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ formatDate(order.created_at) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 self-start">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                            :class="
                                statusColors[order.status] ||
                                'bg-gray-100 text-gray-700'
                            "
                        >
                            {{ statusLabels[order.status] || order.status }}
                        </span>
                        <button
                            @click="deleteOrder"
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
                            حذف الطلب
                        </button>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium text-gray-700">العميل:</span>
                        {{ order.user?.name || "-" }}
                    </p>
                    <p
                        v-if="order.user?.phone"
                        class="text-sm text-gray-600 mt-1"
                    >
                        <span class="font-medium text-gray-700">الهاتف:</span>
                        <span dir="ltr">{{ order.user.phone }}</span>
                    </p>
                    <p
                        v-if="order.delivery_address"
                        class="text-sm text-gray-600 mt-1"
                    >
                        <span class="font-medium text-gray-700"
                            >عنوان التوصيل:</span
                        >
                        {{ order.delivery_address }}
                    </p>
                    <a
                        v-if="order.latitude && order.longitude"
                        :href="`https://www.google.com/maps?q=${order.latitude},${order.longitude}`"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 mt-2 text-sm font-medium text-primary-700 hover:text-primary-900 transition-colors"
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
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        فتح الموقع على الخريطة
                        <svg
                            class="w-3 h-3"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                            />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Grouped Items by Store -->
            <div class="space-y-4 mb-6">
                <div
                    v-for="(group, index) in groupedItems"
                    :key="index"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                >
                    <!-- Store Header -->
                    <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                        <h3 class="font-bold text-gray-900 text-sm">
                            {{ group.store_name || "متجر" }}
                        </h3>
                        <p
                            v-if="group.store_address"
                            class="text-xs text-gray-500 mt-0.5"
                        >
                            {{ group.store_address }}
                        </p>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-gray-600">
                                <tr>
                                    <th
                                        class="px-5 py-2 text-start font-medium"
                                    >
                                        المنتج
                                    </th>
                                    <th
                                        class="px-5 py-2 text-start font-medium"
                                    >
                                        الكمية
                                    </th>
                                    <th
                                        class="px-5 py-2 text-start font-medium"
                                    >
                                        السعر
                                    </th>
                                    <th
                                        class="px-5 py-2 text-start font-medium"
                                    >
                                        الإجمالي
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in group.items" :key="item.id">
                                    <td class="px-5 py-2 text-gray-900">
                                        {{
                                            item.product_name ||
                                            item.product?.name ||
                                            "-"
                                        }}
                                    </td>
                                    <td class="px-5 py-2 text-gray-600">
                                        {{ item.quantity }}
                                    </td>
                                    <td class="px-5 py-2 text-gray-600">
                                        {{
                                            formatPrice(
                                                item.unit_price || item.price,
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="px-5 py-2 font-medium text-gray-900"
                                    >
                                        {{
                                            formatPrice(
                                                (item.unit_price ||
                                                    item.price) * item.quantity,
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Totals -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
            >
                <h3 class="font-bold text-gray-900 mb-3">ملخص الطلب</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">المجموع الفرعي</span>
                        <span class="font-medium text-gray-900">{{
                            formatPrice(order.subtotal)
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">رسوم التوصيل</span>
                        <span class="font-medium text-gray-900">{{
                            formatPrice(order.delivery_fee || 0)
                        }}</span>
                    </div>
                    <div
                        class="flex justify-between pt-2 border-t border-gray-200"
                    >
                        <span class="font-bold text-gray-900">الإجمالي</span>
                        <span class="font-bold text-primary-900">{{
                            formatPrice(order.total)
                        }}</span>
                    </div>
                </div>
            </div>

            <!-- Delivery Man Info -->
            <div
                v-if="order.delivery"
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
            >
                <h3 class="font-bold text-gray-900 mb-3">معلومات التوصيل</h3>
                <div class="text-sm space-y-1">
                    <p class="text-gray-600">
                        <span class="font-medium text-gray-700">المندوب:</span>
                        {{ order.delivery.delivery_man?.name || "-" }}
                    </p>
                    <p
                        v-if="order.delivery.delivery_man?.phone"
                        class="text-gray-600"
                    >
                        <span class="font-medium text-gray-700">الهاتف:</span>
                        <span dir="ltr">{{
                            order.delivery.delivery_man.phone
                        }}</span>
                    </p>
                    <p class="text-gray-600">
                        <span class="font-medium text-gray-700">الحالة:</span>
                        {{
                            order.delivery.delivered_at
                                ? "تم التوصيل"
                                : "جاري التوصيل"
                        }}
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div
                v-if="order.status === 'pending'"
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
            >
                <h3 class="font-bold text-gray-900 mb-4">إجراءات</h3>

                <!-- Accept Order -->
                <form
                    @submit.prevent="acceptOrder"
                    class="flex flex-col sm:flex-row items-start sm:items-end gap-3 mb-4"
                >
                    <div class="flex-1 w-full sm:w-auto">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >رسوم التوصيل (جنيه)</label
                        >
                        <input
                            v-model="acceptForm.delivery_fee"
                            type="number"
                            step="0.01"
                            min="0"
                            dir="ltr"
                            placeholder="0.00"
                            class="w-full sm:w-48 rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                        />
                        <p
                            v-if="acceptForm.errors.delivery_fee"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ acceptForm.errors.delivery_fee }}
                        </p>
                    </div>
                    <button
                        type="submit"
                        :disabled="acceptForm.processing"
                        class="bg-green-600 text-white px-5 py-2.5 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium disabled:opacity-50"
                    >
                        قبول الطلب
                    </button>
                </form>

                <!-- Cancel Order -->
                <button
                    @click="cancelOrder"
                    class="bg-red-600 text-white px-5 py-2.5 rounded-lg hover:bg-red-700 transition-colors text-sm font-medium"
                >
                    إلغاء الطلب
                </button>
            </div>

            <!-- Assign Delivery -->
            <div
                v-if="order.status === 'accepted' && !order.delivery"
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
            >
                <h3 class="font-bold text-gray-900 mb-4">تعيين مندوب توصيل</h3>
                <form
                    @submit.prevent="assignDelivery"
                    class="flex flex-col sm:flex-row items-start sm:items-end gap-3"
                >
                    <div class="flex-1 w-full sm:w-auto">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >المندوب</label
                        >
                        <select
                            v-model="assignForm.delivery_man_id"
                            class="w-full sm:w-64 rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                        >
                            <option value="">اختر المندوب</option>
                            <option
                                v-for="person in deliveryPersonnel"
                                :key="person.id"
                                :value="person.id"
                            >
                                {{ person.name }} —
                                {{
                                    person.active_deliveries_count === 0
                                        ? "✓ متاح"
                                        : `⚡ ${person.active_deliveries_count} توصيل نشط`
                                }}
                            </option>
                        </select>
                        <p
                            v-if="assignForm.errors.delivery_man_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ assignForm.errors.delivery_man_id }}
                        </p>
                    </div>
                    <button
                        type="submit"
                        :disabled="assignForm.processing"
                        class="bg-secondary-500 text-white px-5 py-2.5 rounded-lg hover:bg-secondary-600 transition-colors text-sm font-medium disabled:opacity-50"
                    >
                        تعيين المندوب
                    </button>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
