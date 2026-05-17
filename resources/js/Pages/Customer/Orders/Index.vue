<script setup>
import { Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { computed } from "vue";

const props = defineProps({
    orders: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} }),
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

function getStatusLabel(status) {
    return statusLabels[status] || status;
}

function getStatusColor(status) {
    return statusColors[status] || "bg-gray-100 text-gray-700";
}

const ordersList = computed(() => props.orders?.data || []);
const paginationLinks = computed(() => props.orders?.links || []);
const isEmpty = computed(() => ordersList.value.length === 0);
</script>

<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <h1 class="text-2xl font-bold text-primary-900 mb-8">طلباتي</h1>

            <!-- Empty State -->
            <div
                v-if="isEmpty"
                class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-200"
            >
                <div
                    class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"
                >
                    <svg
                        class="w-10 h-10 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-700 mb-2">
                    لا توجد طلبات بعد
                </h2>
                <p class="text-gray-500 mb-6">
                    لم تقم بإجراء أي طلبات حتى الآن
                </p>
                <Link
                    href="/products"
                    class="inline-flex items-center gap-2 bg-primary-900 text-white px-6 py-3 rounded-lg hover:bg-primary-800 transition-colors font-medium"
                >
                    تصفح المنتجات
                </Link>
            </div>

            <!-- Orders List -->
            <div v-else class="space-y-4">
                <Link
                    v-for="order in ordersList"
                    :key="order.id"
                    :href="`/orders/${order.id}`"
                    class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 hover:shadow-md transition-shadow"
                >
                    <!-- Order Header -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="font-bold text-gray-900 text-sm sm:text-base"
                            >
                                #{{ order.order_number }}
                            </span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="getStatusColor(order.status)"
                            >
                                {{ getStatusLabel(order.status) }}
                            </span>
                        </div>
                        <span class="text-sm text-gray-500">
                            {{ formatDate(order.created_at) }}
                        </span>
                    </div>

                    <!-- Order Details -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2"
                    >
                        <div
                            class="flex items-center gap-4 text-sm text-gray-600"
                        >
                            <span class="flex items-center gap-1">
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
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                    />
                                </svg>
                                {{
                                    order.items_count ||
                                    order.items?.length ||
                                    0
                                }}
                                منتج
                            </span>
                        </div>
                        <span class="font-bold text-primary-900">
                            {{ formatPrice(order.total) }}
                        </span>
                    </div>
                </Link>

                <!-- Pagination -->
                <nav
                    v-if="paginationLinks.length > 3"
                    class="flex items-center justify-center gap-1 mt-8"
                    aria-label="التنقل بين الصفحات"
                >
                    <template
                        v-for="(link, index) in paginationLinks"
                        :key="index"
                    >
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
        </div>
    </PublicLayout>
</template>
