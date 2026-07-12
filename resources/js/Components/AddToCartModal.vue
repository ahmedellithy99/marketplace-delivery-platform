<script setup>
import { ref, computed, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();

const props = defineProps({
    show: { type: Boolean, default: false },
    product: { type: Object, default: null },
});

const emit = defineEmits(["close"]);

const selectedVariantId = ref(null);
const quantity = ref(1);
const adding = ref(false);
const cartError = ref("");

watch(() => page.props.errors, (errors) => {
    if (errors && Object.keys(errors).length > 0) {
        const firstError = Object.values(errors)[0];
        if (typeof firstError === "string") {
            cartError.value = firstError;
            setTimeout(() => { cartError.value = ""; }, 5000);
        }
    }
}, { deep: true });

const step = computed(() => Number(props.product?.quantity_step) || 0.25);
const minQty = computed(() => Number(props.product?.min_quantity) || step.value);
const maxQty = computed(() => Number(props.product?.max_quantity) || 10);

const unitLabel = computed(() => {
    const units = { kg: "كيلو", g: "جرام", liter: "لتر", piece: "قطعة" };
    return units[props.product?.measurement_unit] || props.product?.measurement_unit || "";
});

const selectedVariant = computed(() => {
    if (props.product?.type !== "variant" || !selectedVariantId.value) return null;
    return props.product.variants?.find((v) => v.id === selectedVariantId.value);
});

const displayPrice = computed(() => {
    if (!props.product) return 0;
    if (props.product.type === "variant") {
        const variant = selectedVariant.value;
        if (!variant) return 0;
        return Number(variant.pricing?.effective_price ?? variant.price);
    }
    if (props.product.type === "measured") {
        return Number(props.product.pricing?.effective_price || props.product.base_price || 0) * quantity.value;
    }
    return Number(props.product.pricing?.effective_price || props.product.base_price || 0);
});

const unitPrice = computed(() => {
    if (!props.product) return 0;
    if (props.product.type === "variant") {
        return Number(selectedVariant.value?.price || 0);
    }
    return Number(props.product.base_price || 0);
});

const unitEffectivePrice = computed(() => {
    if (!props.product) return 0;
    if (props.product.type === "variant") {
        const v = selectedVariant.value;
        return Number(v?.pricing?.effective_price ?? v?.price ?? 0);
    }
    return Number(props.product.pricing?.effective_price || props.product.base_price || 0);
});

const hasDiscount = computed(() => {
    if (props.product?.type === "variant" && selectedVariant.value) {
        return selectedVariant.value.pricing?.has_discount || false;
    }
    return props.product?.pricing?.has_discount || false;
});

const discountLabel = computed(() => {
    if (props.product?.type === "variant" && selectedVariant.value) {
        return selectedVariant.value.pricing?.discount_label || null;
    }
    return props.product?.pricing?.discount_label || null;
});

function formatPrice(price) {
    return Number(price).toFixed(2);
}

function increaseQty() {
    if (quantity.value + step.value <= maxQty.value) {
        quantity.value = Math.round((quantity.value + step.value) * 1000) / 1000;
    }
}

function decreaseQty() {
    if (quantity.value - step.value >= minQty.value) {
        quantity.value = Math.round((quantity.value - step.value) * 1000) / 1000;
    }
}

function resetAndClose() {
    selectedVariantId.value = null;
    quantity.value = minQty.value || 1;
    adding.value = false;
    cartError.value = "";
    emit("close");
}

function addToCart() {
    if (adding.value) return;
    adding.value = true;

    const data = { product_id: props.product.id, quantity: quantity.value };

    if (props.product.type === "variant" && selectedVariantId.value) {
        data.variant_id = selectedVariantId.value;
    }

    router.post("/cart", data, {
        preserveScroll: true,
        onSuccess: () => resetAndClose(),
        onError: () => { adding.value = false; },
    });
}

function initModal() {
    if (props.product?.type === "variant" && props.product.variants?.length) {
        const defaultV = props.product.variants.find((v) => v.is_default) || props.product.variants[0];
        selectedVariantId.value = defaultV.id;
    }
    if (props.product?.type === "measured") {
        quantity.value = minQty.value || step.value;
    } else {
        quantity.value = 1;
    }
}

watch(() => props.show, (val) => {
    if (val) initModal();
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show && product"
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
                role="dialog"
                aria-modal="true"
                :aria-label="'تفاصيل ' + (product?.name || '')"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="resetAndClose" />

                <!-- Modal -->
                <Transition
                    enter-active-class="transition-transform duration-300 ease-out"
                    enter-from-class="translate-y-full sm:translate-y-4 sm:scale-95"
                    enter-to-class="translate-y-0 sm:scale-100"
                    leave-active-class="transition-transform duration-200 ease-in"
                    leave-from-class="translate-y-0 sm:scale-100"
                    leave-to-class="translate-y-full sm:translate-y-4 sm:scale-95"
                >
                    <div
                        v-if="show"
                        class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-lg shadow-2xl max-h-[90vh] overflow-y-auto"
                    >
                        <!-- Handle bar (mobile) -->
                        <div class="sm:hidden flex justify-center pt-3 pb-1">
                            <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                        </div>

                        <!-- Close Button (desktop) -->
                        <button
                            @click="resetAndClose"
                            class="hidden sm:flex absolute top-3 end-3 p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-all z-10"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <!-- Product Image -->
                        <div class="relative aspect-[16/9] bg-gray-50 overflow-hidden">
                            <img
                                v-if="product.media?.[0]?.original_url"
                                :src="product.media[0].original_url"
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <!-- Discount Badge -->
                            <span
                                v-if="hasDiscount"
                                class="absolute top-3 start-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-lg shadow-sm"
                            >
                                {{ discountLabel }}
                            </span>
                            <!-- Type Badge -->
                            <span
                                v-if="product.type === 'measured'"
                                class="absolute top-3 end-3 bg-white/90 backdrop-blur-sm text-xs font-medium text-gray-600 px-2 py-1 rounded-lg"
                            >
                                بالوزن
                            </span>
                            <span
                                v-else-if="product.type === 'variant'"
                                class="absolute top-3 end-3 bg-white/90 backdrop-blur-sm text-xs font-medium text-gray-600 px-2 py-1 rounded-lg"
                            >
                                متعدد
                            </span>
                        </div>

                        <!-- Product Info -->
                        <div class="p-5 pb-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 text-lg">{{ product.name }}</h3>
                                    <p v-if="product.store" class="text-sm text-gray-400 mt-0.5">{{ product.store.name }}</p>
                                </div>
                            </div>

                            <!-- Full Description -->
                            <p
                                v-if="product.description"
                                class="text-sm text-gray-600 mt-3 leading-relaxed"
                            >
                                {{ product.description }}
                            </p>

                            <!-- Price Display -->
                            <div class="mt-4 flex items-baseline gap-2">
                                <span class="text-2xl font-bold text-primary-900">
                                    {{ formatPrice(displayPrice) }}
                                    <span class="text-sm font-normal text-gray-500">جنيه</span>
                                </span>
                                <span
                                    v-if="hasDiscount"
                                    class="text-sm text-gray-300 line-through"
                                >
                                    {{ formatPrice(hasDiscount ? unitPrice : unitEffectivePrice) }}
                                </span>
                            </div>
                            <p v-if="product.type === 'variant' && !selectedVariantId" class="text-xs text-red-500 mt-1">
                                يرجى اختيار الحجم
                            </p>
                        </div>

                        <!-- Variant Selection -->
                        <div v-if="product.type === 'variant' && product.variants?.length" class="px-5 pb-4">
                            <p class="text-sm font-semibold text-gray-700 mb-3">اختر الحجم</p>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="variant in product.variants"
                                    :key="variant.id"
                                    type="button"
                                    @click="selectedVariantId = variant.id"
                                    class="p-3 rounded-xl border-2 text-start transition-all"
                                    :class="selectedVariantId === variant.id ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <p class="text-sm font-semibold" :class="selectedVariantId === variant.id ? 'text-primary-900' : 'text-gray-700'">
                                        {{ variant.name }}
                                    </p>
                                    <p class="text-xs mt-0.5" :class="selectedVariantId === variant.id ? 'text-primary-600' : 'text-gray-400'">
                                        <template v-if="variant.pricing?.has_discount">
                                            <span class="font-bold">{{ formatPrice(variant.pricing.effective_price) }}</span>
                                            <span class="line-through text-gray-300 ms-1">{{ formatPrice(variant.price) }}</span>
                                        </template>
                                        <template v-else>
                                            {{ formatPrice(variant.pricing?.effective_price ?? variant.price) }}
                                        </template>
                                        جنيه
                                    </p>
                                </button>
                            </div>
                        </div>

                        <!-- Measured Quantity Selector -->
                        <div v-if="product.type === 'measured'" class="px-5 pb-4">
                            <p class="text-sm font-semibold text-gray-700 mb-3">اختر الكمية ({{ unitLabel }})</p>
                            <div class="flex items-center justify-center gap-4">
                                <button
                                    @click="decreaseQty"
                                    :disabled="quantity <= minQty"
                                    class="w-10 h-10 rounded-full border-2 border-gray-200 flex items-center justify-center text-gray-600 hover:border-primary-500 hover:text-primary-600 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>
                                <div class="text-center min-w-[80px]">
                                    <p class="text-2xl font-bold text-gray-900">{{ quantity }}</p>
                                    <p class="text-xs text-gray-400">{{ unitLabel }}</p>
                                </div>
                                <button
                                    @click="increaseQty"
                                    :disabled="quantity >= maxQty"
                                    class="w-10 h-10 rounded-full border-2 border-gray-200 flex items-center justify-center text-gray-600 hover:border-primary-500 hover:text-primary-600 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 text-center mt-2">
                                {{ formatPrice(unitEffectivePrice) }} جنيه / {{ unitLabel }}
                            </p>
                        </div>

                        <!-- Error Message -->
                        <div v-if="cartError" class="px-5 pb-2">
                            <p class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-center">
                                {{ cartError }}
                            </p>
                        </div>

                        <!-- Add Button -->
                        <div class="p-5 pt-3 border-t border-gray-100">
                            <button
                                @click="addToCart"
                                :disabled="adding || (product.type === 'variant' && !selectedVariantId)"
                                class="w-full flex items-center justify-between bg-primary-900 hover:bg-primary-800 text-white py-3.5 px-5 rounded-xl font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
                            >
                                <span>أضف للسلة</span>
                                <span v-if="product.type !== 'variant' || selectedVariantId" class="text-sm">
                                    {{ formatPrice(displayPrice) }} جنيه
                                </span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
