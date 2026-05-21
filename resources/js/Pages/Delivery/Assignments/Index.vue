<script setup>
import { Link } from "@inertiajs/vue3";
import DeliveryLayout from "@/Layouts/DeliveryLayout.vue";
import { computed } from "vue";

const props = defineProps({
    deliveries: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} }),
    },
});

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

const deliveriesList = computed(() => props.deliveries?.data || []);
const paginationLinks = computed(() => props.deliveries?.links || []);
const isEmpty = computed(() => deliveriesList.value.length === 0);
</script>

<template>
    <DeliveryLayout title="التوصيلات">
        <!-- Page Header -->
        <h1 class="text-2xl font-bold text-primary-900 mb-6">
            التوصيلات المسندة إليك
        </h1>

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
                        d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"
                    />
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-700 mb-2">
                لا توجد توصيلات مسندة إليك
            </h2>
            <p class="text-gray-500">ستظهر هنا التوصيلات عند إسنادها إليك</p>
        </div>

        <!-- Deliveries List -->
        <div v-else class="space-y-4">
            <Link
                v-for="delivery in deliveriesList"
                :key="delivery.id"
                :href="`/delivery/assignments/${delivery.id}`"
                class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-5 hover:shadow-md transition-shadow"
            >
                <!-- Card Header -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="font-bold text-gray-900 text-sm sm:text-base"
                        >
                            #{{
                                delivery.order?.order_number ||
                                delivery.order_number ||
                                "-"
                            }}
                        </span>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="
                                getStatusColor(
                                    delivery.order?.status || delivery.status,
                                )
                            "
                        >
                            {{
                                getStatusLabel(
                                    delivery.order?.status || delivery.status,
                                )
                            }}
                        </span>
                    </div>
                    <span class="text-sm text-gray-500">
                        {{
                            formatDate(
                                delivery.assigned_at || delivery.created_at,
                            )
                        }}
                    </span>
                </div>

                <!-- Delivery Details -->
                <div class="space-y-2 text-sm text-gray-600">
                    <!-- Customer Address -->
                    <div
                        v-if="delivery.order?.delivery_address"
                        class="flex items-start gap-2"
                    >
                        <svg
                            class="w-4 h-4 text-gray-400 mt-0.5 shrink-0"
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
                            <span>{{ delivery.order.delivery_address }}</span>
                            <a
                                v-if="
                                    delivery.order.latitude &&
                                    delivery.order.longitude
                                "
                                :href="`https://www.google.com/maps?q=${delivery.order.latitude},${delivery.order.longitude}`"
                                target="_blank"
                                rel="noopener"
                                class="block text-xs text-primary-700 hover:text-primary-900 font-medium mt-1"
                                @click.stop
                            >
                                فتح على الخريطة ↗
                            </a>
                        </div>
                    </div>

                    <!-- Store addresses -->
                    <div
                        v-if="
                            delivery.store_addresses &&
                            delivery.store_addresses.length
                        "
                        class="flex items-start gap-2"
                    >
                        <svg
                            class="w-4 h-4 text-gray-400 mt-0.5 shrink-0"
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
                        <span>{{ delivery.store_addresses.join(" • ") }}</span>
                    </div>
                </div>
            </Link>

            <!-- Pagination -->
            <nav
                v-if="paginationLinks.length > 3"
                class="flex items-center justify-center gap-1 mt-8"
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
    </DeliveryLayout>
</template>
