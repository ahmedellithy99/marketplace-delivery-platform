<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    show: { type: Boolean, default: false },
    product: { type: Object, default: null },
});

const emit = defineEmits(["close"]);

const selectedVariantId = ref(null);
const quantity = ref(1);
const adding = ref(false);

// Measured product helpers
const step = computed(() => Number(props.product?.quantity_step) || 0.25);
const minQty = computed(
    () => Number(props.product?.min_quantity) || step.value,
);
const maxQty = computed(() => Number(props.product?.max_quantity) || 10);

const unitLabel = computed(() => {
    const units = { kg: "كيلو", g: "جرام", liter: "لتر", piece: "قطعة" };
    return (
        units[props.product?.measurement_unit] ||
        props.product?.measurement_unit ||
        ""
    );
});

// Price display
const displayPrice = computed(() => {
    if (!props.product) return 0;
    if (props.product.type === "variant") {
        const variant = props.product.variants?.find(
            (v) => v.id === selectedVariantId.value,
        );
        if (!variant) return 0;
        return Number(variant.pricing?.effective_price ?? variant.price);
    }
    if (props.product.type === "measured") {
        return (
            Number(
                props.product.pricing?.effective_price ||
                    props.product.base_price ||
                    0,
            ) * quantity.value
        );
    }
    return Number(
        props.product.pricing?.effective_price || props.product.base_price || 0,
    );
});

function formatPrice(price) {
    return Number(price).toFixed(2);
}

function increaseQty() {
    if (quantity.value + step.value <= maxQty.value) {
        quantity.value =
            Math.round((quantity.value + step.value) * 1000) / 1000;
    }
}

function decreaseQty() {
    if (quantity.value - step.value >= minQty.value) {
        quantity.value =
            Math.round((quantity.value - step.value) * 1000) / 1000;
    }
}

function resetAndClose() {
    selectedVariantId.value = null;
    quantity.value = minQty.value || 1;
    adding.value = false;
    emit("close");
}

function addToCart() {
    if (adding.value) return;
    adding.value = true;

    const data = {
        product_id: props.product.id,
        quantity: quantity.value,
    };

    if (props.product.type === "variant" && selectedVariantId.value) {
        data.variant_id = selectedVariantId.value;
    }

    router.post("/cart", data, {
        preserveScroll: true,
        onSuccess: () => resetAndClose(),
        onError: () => {
            adding.value = false;
        },
    });
}

// Set defaults when product changes
function initModal() {
    if (props.product?.type === "variant" && props.product.variants?.length) {
        const defaultV =
            props.product.variants.find((v) => v.is_default) ||
            props.product.variants[0];
        selectedVariantId.value = defaultV.id;
    }
    if (props.product?.type === "measured") {
        quantity.value = minQty.value || step.value;
    } else {
        quantity.value = 1;
    }
}

// Watch for show changes
import { watch } from "vue";
watch(
    () => props.show,
    (val) => {
        if (val) initModal();
    },
);
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
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                    @click="resetAndClose"
                />

                <!-- Modal (bottom sheet on mobile, centered on desktop) -->
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
                        class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md shadow-2xl max-h-[80vh] overflow-y-auto"
                    >
                        <!-- Handle bar (mobile) -->
                        <div class="sm:hidden flex justify-center pt-3 pb-1">
                            <div
                                class="w-10 h-1 bg-gray-300 rounded-full"
                            ></div>
                        </div>

                        <!-- Product Header -->
                        <div class="p-5 pb-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-14 h-14 rounded-xl bg-gray-100 overflow-hidden shrink-0"
                                >
                                    <img
                                        v-if="product.media?.[0]?.original_url"
                                        :src="product.media[0].original_url"
                                        :alt="product.name"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3
                                        class="font-bold text-gray-900 text-base truncate"
                                    >
                                        {{ product.name }}
                                    </h3>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ product.store?.name }}
                                    </p>
                                </div>
                                <button
                                    @click="resetAndClose"
                                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-all"
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
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Variant Selection -->
                        <div
                            v-if="
                                product.type === 'variant' &&
                                product.variants?.length
                            "
                            class="px-5 pb-4"
                        >
                            <p class="text-sm font-semibold text-gray-700 mb-3">
                                اختر الحجم
                            </p>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="variant in product.variants"
                                    :key="variant.id"
                                    type="button"
                                    @click="selectedVariantId = variant.id"
                                    class="p-3 rounded-xl border-2 text-start transition-all"
                                    :class="
                                        selectedVariantId === variant.id
                                            ? 'border-primary-500 bg-primary-50'
                                            : 'border-gray-200 hover:border-gray-300'
                                    "
                                >
                                    <p
                                        class="text-sm font-semibold"
                                        :class="
                                            selectedVariantId === variant.id
                                                ? 'text-primary-900'
                                                : 'text-gray-700'
                                        "
                                    >
                                        {{ variant.name }}
                                    </p>
                                    <p
                                        class="text-xs mt-0.5"
                                        :class="
                                            selectedVariantId === variant.id
                                                ? 'text-primary-600'
                                                : 'text-gray-400'
                                        "
                                    >
                                        <template
                                            v-if="variant.pricing?.has_discount"
                                        >
                                            <span class="font-bold">{{
                                                formatPrice(
                                                    variant.pricing
                                                        .effective_price,
                                                )
                                            }}</span>
                                            <span
                                                class="line-through text-gray-300 ms-1"
                                                >{{
                                                    formatPrice(variant.price)
                                                }}</span
                                            >
                                            جنيه
                                        </template>
                                        <template v-else>
                                            {{
                                                formatPrice(
                                                    variant.pricing
                                                        ?.effective_price ??
                                                        variant.price,
                                                )
                                            }}
                                            جنيه
                                        </template>
                                    </p>
                                </button>
                            </div>
                        </div>

                        <!-- Measured Quantity Selector -->
                        <div
                            v-if="product.type === 'measured'"
                            class="px-5 pb-4"
                        >
                            <p class="text-sm font-semibold text-gray-700 mb-3">
                                اختر الكمية ({{ unitLabel }})
                            </p>
                            <div class="flex items-center justify-center gap-4">
                                <button
                                    @click="decreaseQty"
                                    :disabled="quantity <= minQty"
                                    class="w-10 h-10 rounded-full border-2 border-gray-200 flex items-center justify-center text-gray-600 hover:border-primary-500 hover:text-primary-600 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
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
                                            d="M20 12H4"
                                        />
                                    </svg>
                                </button>
                                <div class="text-center min-w-[80px]">
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ quantity }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ unitLabel }}
                                    </p>
                                </div>
                                <button
                                    @click="increaseQty"
                                    :disabled="quantity >= maxQty"
                                    class="w-10 h-10 rounded-full border-2 border-gray-200 flex items-center justify-center text-gray-600 hover:border-primary-500 hover:text-primary-600 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
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
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 text-center mt-2">
                                {{
                                    formatPrice(
                                        product.pricing?.effective_price ||
                                            product.base_price,
                                    )
                                }}
                                جنيه / {{ unitLabel }}
                            </p>
                        </div>

                        <!-- Add Button -->
                        <div class="p-5 pt-3 border-t border-gray-100">
                            <button
                                @click="addToCart"
                                :disabled="
                                    adding ||
                                    (product.type === 'variant' &&
                                        !selectedVariantId)
                                "
                                class="w-full flex items-center justify-between bg-primary-900 hover:bg-primary-800 text-white py-3.5 px-5 rounded-xl font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
                            >
                                <span>أضف للسلة</span>
                                <span class="text-sm"
                                    >{{ formatPrice(displayPrice) }} جنيه</span
                                >
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
