<script setup>
import { ref } from "vue";
import { useForm, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    product: { type: Object, required: true },
    stores: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const form = useForm({
    _method: "PUT",
    store_id: props.product.store_id || "",
    category_id: props.product.category_id || "",
    name: props.product.name || "",
    description: props.product.description || "",
    type: props.product.type || "simple",
    base_price: props.product.base_price || "",
    measurement_unit: props.product.measurement_unit || "",
    min_quantity: props.product.min_quantity || "",
    max_quantity: props.product.max_quantity || "",
    quantity_step: props.product.quantity_step || "",
    images: [],
});

const imagePreviews = ref([]);
const imagesInput = ref(null);

function handleImages(e) {
    const files = Array.from(e.target.files);
    form.images = files;
    imagePreviews.value = files.map((file) => URL.createObjectURL(file));
}

function removeImage(index) {
    const newFiles = [...form.images];
    newFiles.splice(index, 1);
    form.images = newFiles;
    imagePreviews.value.splice(index, 1);
    if (newFiles.length === 0 && imagesInput.value)
        imagesInput.value.value = "";
}

function submit() {
    form.post(`/admin/products/${props.product.slug}`, { forceFormData: true });
}

// ── Variants Management ───────────────────────────────────────────────
const variants = ref([...(props.product.variants || [])]);
const showVariantForm = ref(false);
const editingVariant = ref(null);

const variantForm = useForm({ name: "", price: "" });

// ── Discount Management ───────────────────────────────────────────────
const productDiscounts = ref([...(props.product.discounts || [])]);
const showDiscountForm = ref(false);
const discountForm = useForm({
    name: "",
    type: "percentage",
    value: "",
    starts_at: "",
    ends_at: "",
});

function submitDiscount() {
    discountForm.post(`/admin/products/${props.product.slug}/discounts`, {
        preserveScroll: true,
        onSuccess: (page) => {
            productDiscounts.value = page.props.product.discounts || [];
            showDiscountForm.value = false;
            discountForm.reset();
        },
    });
}

function deleteDiscount(discount) {
    if (!confirm(`هل أنت متأكد من حذف الخصم "${discount.name}"؟`)) return;
    router.delete(
        `/admin/products/${props.product.slug}/discounts/${discount.id}`,
        {
            preserveScroll: true,
            onSuccess: (page) => {
                productDiscounts.value = page.props.product.discounts || [];
            },
        },
    );
}

function openAddVariant() {
    editingVariant.value = null;
    variantForm.reset();
    showVariantForm.value = true;
}
function openEditVariant(variant) {
    editingVariant.value = variant;
    variantForm.name = variant.name;
    variantForm.price = variant.price;
    showVariantForm.value = true;
}
function cancelVariantForm() {
    showVariantForm.value = false;
    editingVariant.value = null;
    variantForm.reset();
}
function submitVariant() {
    if (editingVariant.value) {
        variantForm.put(
            `/admin/products/${props.product.slug}/variants/${editingVariant.value.id}`,
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    variants.value = page.props.product.variants || [];
                    cancelVariantForm();
                },
            },
        );
    } else {
        variantForm.post(`/admin/products/${props.product.slug}/variants`, {
            preserveScroll: true,
            onSuccess: (page) => {
                variants.value = page.props.product.variants || [];
                cancelVariantForm();
            },
        });
    }
}
function deleteVariant(variant) {
    if (!confirm(`هل أنت متأكد من حذف "${variant.name}"؟`)) return;
    router.delete(
        `/admin/products/${props.product.slug}/variants/${variant.id}`,
        {
            preserveScroll: true,
            onSuccess: (page) => {
                variants.value = page.props.product.variants || [];
            },
        },
    );
}

function setDefaultVariant(variant) {
    router.patch(
        `/admin/products/${props.product.slug}/variants/${variant.id}/set-default`,
        {},
        {
            preserveScroll: true,
            onSuccess: (page) => {
                variants.value = page.props.product.variants || [];
            },
        },
    );
}
</script>

<template>
    <AdminLayout title="تعديل المنتج">
        <div class="max-w-7xl mx-auto">
            <Link
                href="/admin/products"
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
                العودة للمنتجات
            </Link>

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-primary-900">
                    تعديل: {{ product.name }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-600"
                    >
                        {{
                            product.type === "simple"
                                ? "بسيط"
                                : product.type === "variant"
                                  ? "متغيرات"
                                  : "بالوزن"
                        }}
                    </span>
                </p>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-0">
                        <!-- Basic Info -->
                        <fieldset>
                            <legend
                                class="text-base font-bold text-gray-800 mb-5"
                            >
                                المعلومات الأساسية
                            </legend>
                            <div class="space-y-5">
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                        >اسم المنتج
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                        :class="{
                                            'border-red-300': form.errors.name,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="text-sm text-red-600 mt-1.5"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                        >المتجر
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <select
                                        v-model="form.store_id"
                                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none appearance-none bg-white transition-all"
                                        :class="{
                                            'border-red-300':
                                                form.errors.store_id,
                                        }"
                                    >
                                        <option value="">اختر المتجر</option>
                                        <option
                                            v-for="store in stores"
                                            :key="store.id"
                                            :value="store.id"
                                        >
                                            {{ store.name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.store_id"
                                        class="text-sm text-red-600 mt-1.5"
                                    >
                                        {{ form.errors.store_id }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                        >القسم
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <select
                                        v-model="form.category_id"
                                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none appearance-none bg-white transition-all"
                                        :class="{
                                            'border-red-300':
                                                form.errors.category_id,
                                        }"
                                    >
                                        <option value="">اختر القسم</option>
                                        <option
                                            v-for="cat in categories"
                                            :key="cat.id"
                                            :value="cat.id"
                                        >
                                            {{ cat.name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.category_id"
                                        class="text-sm text-red-600 mt-1.5"
                                    >
                                        {{ form.errors.category_id }}
                                    </p>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Pricing — Simple & Measured -->
                        <fieldset
                            v-if="form.type !== 'variant'"
                            class="border-t border-gray-100 pt-6 mt-6"
                        >
                            <legend
                                class="text-base font-bold text-gray-800 mb-5"
                            >
                                السعر
                            </legend>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                >
                                    {{
                                        form.type === "measured"
                                            ? "السعر لكل وحدة (جنيه)"
                                            : "السعر (جنيه)"
                                    }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.base_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    dir="ltr"
                                    placeholder="0.00"
                                    class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                    :class="{
                                        'border-red-300':
                                            form.errors.base_price,
                                    }"
                                />
                                <p
                                    v-if="form.errors.base_price"
                                    class="text-sm text-red-600 mt-1.5"
                                >
                                    {{ form.errors.base_price }}
                                </p>
                            </div>
                        </fieldset>

                        <!-- Measured Fields -->
                        <fieldset
                            v-if="form.type === 'measured'"
                            class="border-t border-gray-100 pt-6 mt-6"
                        >
                            <legend
                                class="text-base font-bold text-gray-800 mb-5"
                            >
                                إعدادات القياس
                            </legend>
                            <div class="space-y-5">
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                        >وحدة القياس
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <select
                                        v-model="form.measurement_unit"
                                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none appearance-none bg-white transition-all"
                                    >
                                        <option value="">اختر الوحدة</option>
                                        <option value="kg">
                                            كيلوجرام (kg)
                                        </option>
                                        <option value="g">جرام (g)</option>
                                        <option value="liter">لتر</option>
                                        <option value="piece">قطعة</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-gray-700 mb-2"
                                            >الحد الأدنى</label
                                        >
                                        <input
                                            v-model="form.min_quantity"
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            dir="ltr"
                                            placeholder="0.25"
                                            class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-gray-700 mb-2"
                                            >الحد الأقصى</label
                                        >
                                        <input
                                            v-model="form.max_quantity"
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            dir="ltr"
                                            placeholder="10"
                                            class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-gray-700 mb-2"
                                            >خطوة الزيادة</label
                                        >
                                        <input
                                            v-model="form.quantity_step"
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            dir="ltr"
                                            placeholder="0.25"
                                            class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                        />
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Variants Section (for variant-type products) -->
                        <fieldset
                            v-if="form.type === 'variant'"
                            class="border-t border-gray-100 pt-6 mt-6"
                        >
                            <legend
                                class="text-base font-bold text-gray-800 mb-5"
                            >
                                المتغيرات
                            </legend>
                            <!-- Existing Variants -->
                            <div
                                v-if="variants.length > 0"
                                class="space-y-2 mb-4"
                            >
                                <div
                                    v-for="variant in variants"
                                    :key="variant.id"
                                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200"
                                >
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="font-medium text-gray-900 text-sm"
                                            >{{ variant.name }}</span
                                        >
                                        <span
                                            class="text-sm text-gray-500 tabular-nums"
                                            dir="ltr"
                                            >{{
                                                Number(variant.price).toFixed(2)
                                            }}
                                            جنيه</span
                                        >
                                        <span
                                            v-if="variant.is_default"
                                            class="text-[10px] bg-primary-100 text-primary-700 px-1.5 py-0.5 rounded font-medium"
                                            >افتراضي</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button
                                            v-if="!variant.is_default"
                                            type="button"
                                            @click="setDefaultVariant(variant)"
                                            class="p-1.5 rounded-md text-primary-600 hover:bg-primary-50 transition-colors"
                                            title="تعيين كافتراضي"
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
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="openEditVariant(variant)"
                                            class="p-1.5 rounded-md text-blue-600 hover:bg-blue-50 transition-colors"
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
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteVariant(variant)"
                                            class="p-1.5 rounded-md text-red-600 hover:bg-red-50 transition-colors"
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
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-sm text-gray-400 mb-4">
                                لا توجد متغيرات — أضف أحجام أو أنواع مختلفة
                            </p>
                            <!-- Add/Edit Form -->
                            <div
                                v-if="showVariantForm"
                                class="p-4 bg-white rounded-lg border border-primary-200 space-y-3 mb-4"
                            >
                                <p
                                    class="text-sm font-semibold text-primary-900"
                                >
                                    {{
                                        editingVariant
                                            ? "تعديل المتغير"
                                            : "إضافة متغير"
                                    }}
                                </p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <input
                                            v-model="variantForm.name"
                                            type="text"
                                            placeholder="الاسم"
                                            class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                            :class="{
                                                'border-red-300':
                                                    variantForm.errors.name,
                                            }"
                                        />
                                        <p
                                            v-if="variantForm.errors.name"
                                            class="text-xs text-red-600 mt-1"
                                        >
                                            {{ variantForm.errors.name }}
                                        </p>
                                    </div>
                                    <div>
                                        <input
                                            v-model="variantForm.price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            dir="ltr"
                                            placeholder="السعر"
                                            class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                            :class="{
                                                'border-red-300':
                                                    variantForm.errors.price,
                                            }"
                                        />
                                        <p
                                            v-if="variantForm.errors.price"
                                            class="text-xs text-red-600 mt-1"
                                        >
                                            {{ variantForm.errors.price }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="submitVariant"
                                        :disabled="variantForm.processing"
                                        class="px-4 py-2 text-sm font-medium rounded-lg bg-primary-900 text-white hover:bg-primary-800 transition-all disabled:opacity-50"
                                    >
                                        {{ editingVariant ? "تحديث" : "إضافة" }}
                                    </button>
                                    <button
                                        type="button"
                                        @click="cancelVariantForm"
                                        class="px-4 py-2 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-100 transition-all"
                                    >
                                        إلغاء
                                    </button>
                                </div>
                            </div>
                            <button
                                v-if="!showVariantForm"
                                type="button"
                                @click="openAddVariant"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-dashed border-gray-300 text-gray-600 hover:border-primary-500 hover:text-primary-900 hover:bg-primary-50/30 transition-all"
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
                                إضافة متغير
                            </button>
                        </fieldset>

                        <!-- Discounts Section -->
                        <fieldset class="border-t border-gray-100 pt-6 mt-6">
                            <legend
                                class="text-base font-bold text-gray-800 mb-5"
                            >
                                الخصومات
                            </legend>

                            <!-- Existing Discounts -->
                            <div
                                v-if="productDiscounts.length > 0"
                                class="space-y-2 mb-4"
                            >
                                <div
                                    v-for="discount in productDiscounts"
                                    :key="discount.id"
                                    class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200"
                                >
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center"
                                        >
                                            <svg
                                                class="w-4 h-4 text-green-600"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"
                                                />
                                            </svg>
                                        </span>
                                        <div>
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ discount.name }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{
                                                    discount.type ===
                                                    "percentage"
                                                        ? discount.value + "%"
                                                        : discount.value +
                                                          " جنيه"
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="deleteDiscount(discount)"
                                        class="p-1.5 rounded-md text-red-600 hover:bg-red-50 transition-colors"
                                        title="حذف"
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
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p v-else class="text-sm text-gray-400 mb-4">
                                لا توجد خصومات على هذا المنتج
                            </p>

                            <!-- Add Discount Form -->
                            <div
                                v-if="showDiscountForm"
                                class="p-4 bg-white rounded-lg border border-secondary-200 space-y-3 mb-4"
                            >
                                <p
                                    class="text-sm font-semibold text-secondary-600"
                                >
                                    إضافة خصم جديد
                                </p>
                                <div>
                                    <input
                                        v-model="discountForm.name"
                                        type="text"
                                        placeholder="اسم الخصم (مثال: خصم الصيف)"
                                        class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                        :class="{
                                            'border-red-300':
                                                discountForm.errors.name,
                                        }"
                                    />
                                    <p
                                        v-if="discountForm.errors.name"
                                        class="text-xs text-red-600 mt-1"
                                    >
                                        {{ discountForm.errors.name }}
                                    </p>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <select
                                            v-model="discountForm.type"
                                            class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none appearance-none bg-white transition-all"
                                        >
                                            <option value="percentage">
                                                نسبة مئوية (%)
                                            </option>
                                            <option value="fixed">
                                                مبلغ ثابت (جنيه)
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <input
                                            v-model="discountForm.value"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            dir="ltr"
                                            :placeholder="
                                                discountForm.type ===
                                                'percentage'
                                                    ? '15'
                                                    : '20'
                                            "
                                            class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                            :class="{
                                                'border-red-300':
                                                    discountForm.errors.value,
                                            }"
                                        />
                                        <p
                                            v-if="discountForm.errors.value"
                                            class="text-xs text-red-600 mt-1"
                                        >
                                            {{ discountForm.errors.value }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1"
                                            >تاريخ البداية</label
                                        >
                                        <input
                                            v-model="discountForm.starts_at"
                                            type="datetime-local"
                                            dir="ltr"
                                            class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1"
                                            >تاريخ الانتهاء</label
                                        >
                                        <input
                                            v-model="discountForm.ends_at"
                                            type="datetime-local"
                                            dir="ltr"
                                            class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                                        />
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="submitDiscount"
                                        :disabled="discountForm.processing"
                                        class="px-4 py-2 text-sm font-medium rounded-lg bg-secondary-500 text-white hover:bg-secondary-600 transition-all disabled:opacity-50"
                                    >
                                        إضافة
                                    </button>
                                    <button
                                        type="button"
                                        @click="
                                            showDiscountForm = false;
                                            discountForm.reset();
                                        "
                                        class="px-4 py-2 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-100 transition-all"
                                    >
                                        إلغاء
                                    </button>
                                </div>
                            </div>

                            <button
                                v-if="!showDiscountForm"
                                type="button"
                                @click="showDiscountForm = true"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-dashed border-secondary-300 text-secondary-600 hover:border-secondary-500 hover:text-secondary-700 hover:bg-secondary-50/30 transition-all"
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
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"
                                    />
                                </svg>
                                إضافة خصم
                            </button>
                        </fieldset>

                        <!-- Description -->
                        <fieldset class="border-t border-gray-100 pt-6 mt-6">
                            <legend
                                class="text-base font-bold text-gray-800 mb-5"
                            >
                                الوصف
                            </legend>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                placeholder="أدخل وصف المنتج..."
                                class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none resize-none transition-all"
                            ></textarea>
                        </fieldset>

                        <!-- Submit -->
                        <div
                            class="hidden lg:flex items-center gap-4 pt-8 mt-6 border-t border-gray-100"
                        >
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-primary-900 hover:bg-primary-800 text-white px-8 py-3 rounded-xl font-semibold shadow-sm hover:shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <svg
                                    v-if="form.processing"
                                    class="w-5 h-5 animate-spin"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    />
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                    />
                                </svg>
                                {{
                                    form.processing
                                        ? "جاري الحفظ..."
                                        : "حفظ التعديلات"
                                }}
                            </button>
                            <Link
                                href="/admin/products"
                                class="text-gray-500 hover:text-gray-700 transition-colors"
                                >إلغاء</Link
                            >
                        </div>
                    </div>

                    <!-- Sidebar — Images -->
                    <div class="lg:col-span-1">
                        <div
                            class="bg-white rounded-xl border border-gray-200 shadow-sm p-5"
                        >
                            <h3 class="text-sm font-bold text-gray-800 mb-4">
                                الصور
                            </h3>
                            <div
                                @click="imagesInput?.click()"
                                class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-primary-500 hover:bg-primary-50/30 transition-all cursor-pointer"
                            >
                                <svg
                                    class="w-10 h-10 mx-auto text-gray-400 mb-3"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                    />
                                </svg>
                                <p class="text-sm text-gray-600 font-medium">
                                    إضافة صور جديدة
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    PNG, JPG, WebP — حد أقصى 5MB
                                </p>
                            </div>
                            <input
                                ref="imagesInput"
                                type="file"
                                accept="image/*"
                                multiple
                                @change="handleImages"
                                class="hidden"
                            />
                            <!-- New image previews -->
                            <div
                                v-if="imagePreviews.length > 0"
                                class="grid grid-cols-3 gap-2 mt-4"
                            >
                                <div
                                    v-for="(preview, index) in imagePreviews"
                                    :key="index"
                                    class="relative group aspect-square"
                                >
                                    <img
                                        :src="preview"
                                        alt="معاينة"
                                        class="w-full h-full object-cover rounded-lg"
                                    />
                                    <button
                                        type="button"
                                        @click="removeImage(index)"
                                        class="absolute top-1 start-1 bg-red-500 text-white rounded-md p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-md"
                                    >
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
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <!-- Existing images -->
                            <div
                                v-if="product.media && product.media.length > 0"
                                class="mt-4"
                            >
                                <p class="text-xs text-gray-500 mb-2">
                                    الصور الحالية
                                </p>
                                <div class="grid grid-cols-3 gap-2">
                                    <div
                                        v-for="media in product.media"
                                        :key="media.id"
                                        class="aspect-square rounded-lg overflow-hidden border border-gray-200"
                                    >
                                        <img
                                            :src="media.original_url"
                                            :alt="product.name"
                                            class="w-full h-full object-cover"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Submit -->
                <div
                    class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] p-4 z-50"
                >
                    <div class="flex items-center gap-3 max-w-7xl mx-auto">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 bg-primary-900 hover:bg-primary-800 text-white px-8 py-3 rounded-xl font-semibold shadow-sm transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            {{
                                form.processing
                                    ? "جاري الحفظ..."
                                    : "حفظ التعديلات"
                            }}
                        </button>
                        <Link
                            href="/admin/products"
                            class="text-gray-500 hover:text-gray-700 px-4 py-3 transition-colors"
                            >إلغاء</Link
                        >
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
