<script setup>
import { Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { computed } from "vue";

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
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
    pending: "bg-yellow-100 text-yellow-700 border-yellow-200",
    accepted: "bg-blue-100 text-blue-700 border-blue-200",
    preparing: "bg-orange-100 text-orange-700 border-orange-200",
    on_the_way: "bg-purple-100 text-purple-700 border-purple-200",
    delivered: "bg-green-100 text-green-700 border-green-200",
    cancelled: "bg-red-100 text-red-700 border-red-200",
};

const statusSteps = [
    "pending",
    "accepted",
    "preparing",
    "on_the_way",
    "delivered",
];

function getStatusLabel(status) {
    return statusLabels[status] || status;
}

function getStatusColor(status) {
    return statusColors[status] || "bg-gray-100 text-gray-700 border-gray-200";
}

const currentStepIndex = computed(() => {
    if (props.order.status === "cancelled") return -1;
    return statusSteps.indexOf(props.order.status);
});

function isStepCompleted(stepIndex) {
    return stepIndex <= currentStepIndex.value;
}

function isStepActive(stepIndex) {
    return stepIndex === currentStepIndex.value;
}

const isCancelled = computed(() => props.order.status === "cancelled");

const showDeliveryFeeRange = computed(() => {
    return props.order.status === "pending";
});

const showActualDeliveryFee = computed(() => {
    return ["accepted", "preparing", "on_the_way", "delivered"].includes(
        props.order.status,
    );
});

const deliveryMan = computed(() => {
    return props.order.delivery?.delivery_man || null;
});
</script>

<template>
    <PublicLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Back Link -->
            <Link
                href="/orders"
                class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-900 mb-6 transition-colors"
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
                        <h1 class="text-xl font-bold text-gray-900">
                            طلب #{{ order.order_number }}
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ formatDate(order.created_at) }}
                        </p>
                    </div>
                    <span
                        class="inline-flex items-center self-start px-3 py-1 rounded-full text-sm font-medium border"
                        :class="getStatusColor(order.status)"
                    >
                        {{ getStatusLabel(order.status) }}
                    </span>
                </div>
            </div>

            <!-- Status Timeline -->
            <div
                v-if="!isCancelled"
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
            >
                <h2 class="text-lg font-bold text-gray-900 mb-6">تتبع الطلب</h2>
                <div class="relative">
                    <!-- Progress Line -->
                    <div
                        class="absolute top-4 inset-s-4 inset-e-4 h-0.5 bg-gray-200"
                    ></div>
                    <div
                        class="absolute top-4 inset-s-4 h-0.5 bg-primary-900 transition-all duration-500"
                        :style="{
                            width:
                                currentStepIndex >= 0
                                    ? `${(currentStepIndex / (statusSteps.length - 1)) * 100}%`
                                    : '0%',
                        }"
                    ></div>

                    <!-- Steps -->
                    <div class="relative flex justify-between">
                        <div
                            v-for="(step, index) in statusSteps"
                            :key="step"
                            class="flex flex-col items-center"
                        >
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors z-10"
                                :class="
                                    isStepCompleted(index)
                                        ? 'bg-primary-900 text-white'
                                        : 'bg-gray-200 text-gray-500'
                                "
                            >
                                <svg
                                    v-if="
                                        isStepCompleted(index) &&
                                        !isStepActive(index)
                                    "
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                <span v-else>{{ index + 1 }}</span>
                            </div>
                            <span
                                class="text-xs mt-2 text-center max-w-[60px] sm:max-w-none"
                                :class="
                                    isStepCompleted(index)
                                        ? 'text-primary-900 font-medium'
                                        : 'text-gray-500'
                                "
                            >
                                {{ getStatusLabel(step) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancelled Notice -->
            <div
                v-if="isCancelled"
                class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6"
            >
                <div class="flex items-center gap-3">
                    <svg
                        class="w-6 h-6 text-red-500 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <div>
                        <h3 class="font-semibold text-red-700">
                            تم إلغاء الطلب
                        </h3>
                        <p class="text-sm text-red-600 mt-1">
                            هذا الطلب تم إلغاؤه
                        </p>
                    </div>
                </div>
            </div>

            <!-- Delivery Info -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
            >
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    معلومات التوصيل
                </h2>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <svg
                            class="w-5 h-5 text-gray-400 mt-0.5 shrink-0"
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
                        <div>
                            <span class="text-sm text-gray-500">العنوان</span>
                            <p class="text-gray-900">
                                {{ order.delivery_address }}
                            </p>
                        </div>
                    </div>
                    <div v-if="order.notes" class="flex items-start gap-3">
                        <svg
                            class="w-5 h-5 text-gray-400 mt-0.5 shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
                            />
                        </svg>
                        <div>
                            <span class="text-sm text-gray-500">ملاحظات</span>
                            <p class="text-gray-900">{{ order.notes }}</p>
                        </div>
                    </div>

                    <!-- Delivery Man Info -->
                    <div
                        v-if="deliveryMan"
                        class="flex items-start gap-3 pt-3 border-t border-gray-100"
                    >
                        <svg
                            class="w-5 h-5 text-gray-400 mt-0.5 shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            />
                        </svg>
                        <div>
                            <span class="text-sm text-gray-500"
                                >مندوب التوصيل</span
                            >
                            <p class="text-gray-900 font-medium">
                                {{ deliveryMan.name }}
                            </p>
                            <p
                                v-if="deliveryMan.phone"
                                class="text-sm text-gray-600"
                            >
                                {{ deliveryMan.phone }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
            >
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    تفاصيل الطلب
                </h2>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th
                                    class="text-start pb-3 font-medium text-gray-500"
                                >
                                    المنتج
                                </th>
                                <th
                                    class="text-center pb-3 font-medium text-gray-500"
                                >
                                    الكمية
                                </th>
                                <th
                                    class="text-start pb-3 font-medium text-gray-500"
                                >
                                    السعر
                                </th>
                                <th
                                    class="text-start pb-3 font-medium text-gray-500"
                                >
                                    الإجمالي
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="item in order.items"
                                :key="item.id"
                                class="align-top"
                            >
                                <td class="py-3 pe-4">
                                    <span class="text-gray-900 font-medium">{{
                                        item.product_name
                                    }}</span>
                                </td>
                                <td class="py-3 text-center text-gray-600">
                                    {{ item.quantity }}
                                </td>
                                <td class="py-3 text-gray-600">
                                    {{ formatPrice(item.price) }}
                                </td>
                                <td class="py-3 font-medium text-gray-900">
                                    {{ formatPrice(item.total) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="border-t border-gray-200 mt-4 pt-4 space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">المجموع الفرعي</span>
                        <span class="text-gray-900">{{
                            formatPrice(order.subtotal)
                        }}</span>
                    </div>

                    <!-- Delivery Fee Range (pending) -->
                    <div
                        v-if="showDeliveryFeeRange"
                        class="flex items-center justify-between text-sm"
                    >
                        <span class="text-gray-600"
                            >رسوم التوصيل (تقديرية)</span
                        >
                        <span class="text-gray-900">
                            {{ formatPrice(order.delivery_fee_min) }} -
                            {{ formatPrice(order.delivery_fee_max) }}
                        </span>
                    </div>

                    <!-- Actual Delivery Fee (accepted+) -->
                    <div
                        v-if="showActualDeliveryFee"
                        class="flex items-center justify-between text-sm"
                    >
                        <span class="text-gray-600">رسوم التوصيل</span>
                        <span class="text-gray-900">{{
                            formatPrice(order.delivery_fee)
                        }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between pt-2 border-t border-gray-100"
                    >
                        <span class="font-bold text-gray-900">الإجمالي</span>
                        <span class="font-bold text-primary-900 text-lg">{{
                            formatPrice(order.total)
                        }}</span>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
