<script setup>
import { Link, router } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { computed } from "vue";

const props = defineProps({
    cart: {
        type: Object,
        default: () => ({}),
    },
    cartItems: {
        type: Array,
        default: () => [],
    },
    totals: {
        type: Object,
        default: () => ({ itemCount: 0, subtotal: 0 }),
    },
});

function formatPrice(price) {
    if (!price && price !== 0) return "";
    return Number(price).toFixed(2) + " جنيه";
}

function getProductImage(item) {
    const media = item.product?.media?.find(
        (m) => m.collection_name === "images",
    );
    return media?.original_url || null;
}

function getUnitPrice(item) {
    if (item.quantity === 0) return 0;
    return item.price / item.quantity;
}

function updateQuantity(cartItem, newQuantity) {
    if (newQuantity < 1) return;
    router.patch(
        `/cart/${cartItem.id}`,
        { quantity: newQuantity },
        { preserveScroll: true },
    );
}

function removeItem(cartItem) {
    router.delete(`/cart/${cartItem.id}`, { preserveScroll: true });
}

function clearCart() {
    router.delete("/cart/clear", { preserveScroll: true });
}

const isEmpty = computed(() => props.cartItems.length === 0);
</script>

<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-primary-900">سلة التسوق</h1>
                <button
                    v-if="!isEmpty"
                    @click="clearCart"
                    class="text-sm text-red-600 hover:text-red-700 font-medium transition-colors"
                >
                    مسح السلة
                </button>
            </div>

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
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"
                        />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-700 mb-2">
                    سلة التسوق فارغة
                </h2>
                <p class="text-gray-500 mb-6">لم تقم بإضافة أي منتجات بعد</p>
                <Link
                    href="/products"
                    class="inline-flex items-center gap-2 bg-primary-900 text-white px-6 py-3 rounded-lg hover:bg-primary-800 transition-colors font-medium"
                >
                    تصفح المنتجات
                </Link>
            </div>

            <!-- Cart Content -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-4">
                    <div
                        v-for="item in cartItems"
                        :key="item.id"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex gap-4"
                    >
                        <!-- Product Image -->
                        <div
                            class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-100 rounded-lg shrink-0 overflow-hidden"
                        >
                            <img
                                v-if="getProductImage(item)"
                                :src="getProductImage(item)"
                                :alt="item.product?.name"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center"
                            >
                                <svg
                                    class="w-8 h-8 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                        </div>

                        <!-- Item Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3
                                        class="font-semibold text-gray-900 text-sm sm:text-base line-clamp-1"
                                    >
                                        {{ item.product?.name }}
                                    </h3>
                                    <p
                                        v-if="item.variant"
                                        class="text-xs text-gray-500 mt-0.5"
                                    >
                                        {{ item.variant.name }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        سعر الوحدة:
                                        {{ formatPrice(getUnitPrice(item)) }}
                                    </p>
                                </div>
                                <!-- Remove Button -->
                                <button
                                    @click="removeItem(item)"
                                    class="text-red-500 hover:text-red-600 p-1 transition-colors shrink-0"
                                    aria-label="حذف المنتج"
                                >
                                    <svg
                                        class="w-5 h-5"
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
                                </button>
                            </div>

                            <!-- Quantity & Total -->
                            <div class="flex items-center justify-between mt-3">
                                <!-- Quantity Controls -->
                                <div
                                    class="flex items-center gap-2 border border-gray-200 rounded-lg"
                                >
                                    <button
                                        @click="
                                            updateQuantity(
                                                item,
                                                item.quantity - 1,
                                            )
                                        "
                                        :disabled="item.quantity <= 1"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-100 rounded-s-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        aria-label="تقليل الكمية"
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
                                                d="M20 12H4"
                                            />
                                        </svg>
                                    </button>
                                    <span
                                        class="w-8 text-center text-sm font-medium text-gray-900"
                                    >
                                        {{ item.quantity }}
                                    </span>
                                    <button
                                        @click="
                                            updateQuantity(
                                                item,
                                                item.quantity + 1,
                                            )
                                        "
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-100 rounded-e-lg transition-colors"
                                        aria-label="زيادة الكمية"
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
                                                d="M12 4v16m8-8H4"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Line Total -->
                                <span
                                    class="font-bold text-primary-900 text-sm sm:text-base"
                                >
                                    {{ formatPrice(item.price) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="lg:col-span-1">
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24"
                    >
                        <h2 class="text-lg font-bold text-gray-900 mb-4">
                            ملخص الطلب
                        </h2>

                        <div class="space-y-3 mb-6">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="text-gray-600">عدد المنتجات</span>
                                <span class="font-medium text-gray-900">{{
                                    totals.itemCount
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="text-gray-600"
                                    >المجموع الفرعي</span
                                >
                                <span class="font-medium text-gray-900">{{
                                    formatPrice(totals.subtotal)
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between text-sm text-gray-500"
                            >
                                <span>رسوم التوصيل</span>
                                <span>تحسب عند الطلب</span>
                            </div>
                        </div>

                        <div
                            class="border-t border-gray-200 pt-4 mb-6 flex items-center justify-between"
                        >
                            <span class="font-bold text-gray-900"
                                >الإجمالي</span
                            >
                            <span class="font-bold text-primary-900 text-lg">{{
                                formatPrice(totals.subtotal)
                            }}</span>
                        </div>

                        <Link
                            href="/orders/create"
                            class="block w-full text-center bg-secondary-500 hover:bg-secondary-600 text-white py-3 rounded-lg font-semibold transition-colors"
                        >
                            إتمام الطلب
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
