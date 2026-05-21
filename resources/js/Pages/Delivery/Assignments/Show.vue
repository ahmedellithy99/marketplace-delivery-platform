<script setup>
import { Link, router } from "@inertiajs/vue3";
import DeliveryLayout from "@/Layouts/DeliveryLayout.vue";
import { ref, computed } from "vue";

const props = defineProps({
    delivery: { type: Object, required: true },
});

const processing = ref(false);

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

function formatPrice(price) {
    if (!price && price !== 0) return "";
    return Number(price).toFixed(2) + " جنيه";
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

const order = computed(() => props.delivery?.order || {});
const orderStatus = computed(() => order.value?.status || "");
const orderItems = computed(() => order.value?.items || []);
const customer = computed(() => order.value?.user || {});

// Group items by store
const groupedByStore = computed(() => {
    const groups = {};
    orderItems.value.forEach((item) => {
        const storeName =
            item.store?.name || item.product?.store?.name || "متجر";
        const storeAddress =
            item.store?.address || item.product?.store?.address || "";
        if (!groups[storeName]) {
            groups[storeName] = {
                store_name: storeName,
                store_address: storeAddress,
                items: [],
            };
        }
        groups[storeName].items.push(item);
    });
    return Object.values(groups);
});

function markPreparing() {
    if (!confirm("هل أنت متأكد من بدء تحضير هذا الطلب؟")) return;
    processing.value = true;
    router.post(
        `/delivery/assignments/${props.delivery.id}/preparing`,
        {},
        {
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}

function markPickedUp() {
    if (!confirm("هل أنت متأكد من تأكيد استلام الطلب؟")) return;
    processing.value = true;
    router.post(
        `/delivery/assignments/${props.delivery.id}/picked-up`,
        {},
        {
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}

function markDelivered() {
    if (!confirm("هل أنت متأكد من تأكيد توصيل الطلب؟")) return;
    processing.value = true;
    router.post(
        `/delivery/assignments/${props.delivery.id}/delivered`,
        {},
        {
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}
</script>

<template>
    <DeliveryLayout title="تفاصيل التوصيل">
        <!-- Back Link -->
        <Link
            href="/delivery/assignments"
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
            العودة للتوصيلات
        </Link>

        <!-- Delivery Header -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-4"
        >
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3"
            >
                <div>
                    <h2 class="text-xl font-bold text-primary-900 mb-1">
                        طلب #{{ order.order_number || "-" }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        تاريخ الإسناد:
                        {{
                            formatDate(
                                delivery.assigned_at || delivery.created_at,
                            )
                        }}
                    </p>
                </div>
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium self-start"
                    :class="
                        statusColors[orderStatus] || 'bg-gray-100 text-gray-700'
                    "
                >
                    {{ statusLabels[orderStatus] || orderStatus }}
                </span>
            </div>
        </div>

        <!-- Customer Info -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-4"
        >
            <h3 class="font-bold text-gray-900 mb-3">معلومات العميل</h3>
            <div class="space-y-2 text-sm">
                <p class="text-gray-600">
                    <span class="font-medium text-gray-700">الاسم:</span>
                    {{ customer.name || "-" }}
                </p>
                <p v-if="customer.phone" class="text-gray-600">
                    <span class="font-medium text-gray-700">الهاتف:</span>
                    <a
                        :href="`tel:${customer.phone}`"
                        dir="ltr"
                        class="text-primary-900 hover:underline"
                    >
                        {{ customer.phone }}
                    </a>
                </p>
                <p v-if="order.delivery_address" class="text-gray-600">
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

        <!-- Store Pickup Addresses -->
        <div
            v-if="groupedByStore.length"
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-4"
        >
            <h3 class="font-bold text-gray-900 mb-3">
                عناوين الاستلام من المتاجر
            </h3>
            <div class="space-y-3">
                <div
                    v-for="(group, index) in groupedByStore"
                    :key="index"
                    class="flex items-start gap-2 text-sm"
                >
                    <svg
                        class="w-4 h-4 text-secondary-500 mt-0.5 shrink-0"
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
                    <div>
                        <span class="font-medium text-gray-900">{{
                            group.store_name
                        }}</span>
                        <p
                            v-if="group.store_address"
                            class="text-gray-500 mt-0.5"
                        >
                            {{ group.store_address }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4"
        >
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="font-bold text-gray-900">محتويات الطلب</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <div
                    v-for="item in orderItems"
                    :key="item.id"
                    class="px-5 py-3 flex items-center justify-between gap-3"
                >
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ item.product_name || item.product?.name || "-" }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ item.quantity }} ×
                            {{ formatPrice(item.unit_price || item.price) }}
                        </p>
                    </div>
                    <span
                        class="text-sm font-medium text-gray-900 whitespace-nowrap"
                    >
                        {{
                            formatPrice(
                                (item.unit_price || item.price) * item.quantity,
                            )
                        }}
                    </span>
                </div>
            </div>
            <!-- Total -->
            <div
                class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-between items-center"
            >
                <span class="font-bold text-gray-900 text-sm">الإجمالي</span>
                <span class="font-bold text-primary-900">{{
                    formatPrice(order.total)
                }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <!-- Accepted → Start Preparing -->
            <div v-if="orderStatus === 'accepted'">
                <p class="text-sm text-gray-600 mb-4">
                    الطلب مقبول وجاهز للتحضير. اضغط الزر أدناه لبدء التحضير.
                </p>
                <button
                    @click="markPreparing"
                    :disabled="processing"
                    class="w-full sm:w-auto bg-orange-500 text-white px-6 py-3 rounded-lg hover:bg-orange-600 transition-colors font-bold text-base disabled:opacity-50"
                >
                    بدء التحضير
                </button>
            </div>

            <!-- Preparing → Picked Up -->
            <div v-else-if="orderStatus === 'preparing'">
                <p class="text-sm text-gray-600 mb-4">
                    الطلب قيد التحضير. عند استلام الطلب من المتجر اضغط الزر
                    أدناه.
                </p>
                <button
                    @click="markPickedUp"
                    :disabled="processing"
                    class="w-full sm:w-auto bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors font-bold text-base disabled:opacity-50"
                >
                    تم الاستلام
                </button>
            </div>

            <!-- On the Way → Delivered -->
            <div v-else-if="orderStatus === 'on_the_way'">
                <p class="text-sm text-gray-600 mb-4">
                    أنت في الطريق لتوصيل الطلب. عند التسليم للعميل اضغط الزر
                    أدناه.
                </p>
                <button
                    @click="markDelivered"
                    :disabled="processing"
                    class="w-full sm:w-auto bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors font-bold text-base disabled:opacity-50"
                >
                    تم التوصيل
                </button>
            </div>

            <!-- Delivered → Success Message -->
            <div v-else-if="orderStatus === 'delivered'">
                <div
                    class="flex items-center gap-3 text-green-700 bg-green-50 rounded-lg p-4"
                >
                    <svg
                        class="w-6 h-6 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <span class="font-medium">تم توصيل هذا الطلب بنجاح!</span>
                </div>
            </div>

            <!-- Other statuses -->
            <div v-else>
                <p class="text-sm text-gray-500">
                    لا توجد إجراءات متاحة لهذا الطلب حالياً.
                </p>
            </div>
        </div>
    </DeliveryLayout>
</template>
