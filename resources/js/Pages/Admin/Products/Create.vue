<script setup>
import { ref } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    stores: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const form = useForm({
    store_id: "",
    category_id: "",
    name: "",
    description: "",
    type: "simple",
    base_price: "",
    measurement_unit: "",
    min_quantity: "",
    max_quantity: "",
    quantity_step: "",
    variants: [{ name: "", price: "" }],
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

function addVariant() {
    form.variants.push({ name: "", price: "" });
}

function removeVariant(index) {
    if (form.variants.length > 1) form.variants.splice(index, 1);
}

function submit() {
    form.post("/admin/products", { forceFormData: true });
}
</script>

<template>
    <AdminLayout title="إضافة منتج">
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
                    إضافة منتج جديد
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    أضف منتج جديد إلى المتجر
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
                                <!-- Name -->
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
                                        placeholder="أدخل اسم المنتج"
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
                                <!-- Store -->
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
                                <!-- Category -->
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

                        <!-- Product Type -->
                        <fieldset class="border-t border-gray-100 pt-6 mt-6">
                            <legend
                                class="text-base font-bold text-gray-800 mb-5"
                            >
                                نوع المنتج
                            </legend>
                            <div class="grid grid-cols-3 gap-3">
                                <button
                                    type="button"
                                    @click="form.type = 'simple'"
                                    class="p-4 rounded-xl border-2 text-center transition-all"
                                    :class="
                                        form.type === 'simple'
                                            ? 'border-primary-500 bg-primary-50'
                                            : 'border-gray-200 hover:border-gray-300'
                                    "
                                >
                                    <svg
                                        class="w-6 h-6 mx-auto mb-2"
                                        :class="
                                            form.type === 'simple'
                                                ? 'text-primary-600'
                                                : 'text-gray-400'
                                        "
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                        />
                                    </svg>
                                    <p
                                        class="text-sm font-semibold"
                                        :class="
                                            form.type === 'simple'
                                                ? 'text-primary-900'
                                                : 'text-gray-700'
                                        "
                                    >
                                        بسيط
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        سعر ثابت
                                    </p>
                                </button>
                                <button
                                    type="button"
                                    @click="form.type = 'variant'"
                                    class="p-4 rounded-xl border-2 text-center transition-all"
                                    :class="
                                        form.type === 'variant'
                                            ? 'border-primary-500 bg-primary-50'
                                            : 'border-gray-200 hover:border-gray-300'
                                    "
                                >
                                    <svg
                                        class="w-6 h-6 mx-auto mb-2"
                                        :class="
                                            form.type === 'variant'
                                                ? 'text-primary-600'
                                                : 'text-gray-400'
                                        "
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M4 6h16M4 10h16M4 14h16M4 18h16"
                                        />
                                    </svg>
                                    <p
                                        class="text-sm font-semibold"
                                        :class="
                                            form.type === 'variant'
                                                ? 'text-primary-900'
                                                : 'text-gray-700'
                                        "
                                    >
                                        متغيرات
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        أحجام / أنواع
                                    </p>
                                </button>
                                <button
                                    type="button"
                                    @click="form.type = 'measured'"
                                    class="p-4 rounded-xl border-2 text-center transition-all"
                                    :class="
                                        form.type === 'measured'
                                            ? 'border-primary-500 bg-primary-50'
                                            : 'border-gray-200 hover:border-gray-300'
                                    "
                                >
                                    <svg
                                        class="w-6 h-6 mx-auto mb-2"
                                        :class="
                                            form.type === 'measured'
                                                ? 'text-primary-600'
                                                : 'text-gray-400'
                                        "
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"
                                        />
                                    </svg>
                                    <p
                                        class="text-sm font-semibold"
                                        :class="
                                            form.type === 'measured'
                                                ? 'text-primary-900'
                                                : 'text-gray-700'
                                        "
                                    >
                                        بالوزن
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        كيلو / جرام
                                    </p>
                                </button>
                            </div>
                            <p
                                v-if="form.errors.type"
                                class="text-sm text-red-600 mt-2"
                            >
                                {{ form.errors.type }}
                            </p>
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
                            <div class="space-y-5">
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
                                إعدادات الوزن / القياس
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
                                        :class="{
                                            'border-red-300':
                                                form.errors.measurement_unit,
                                        }"
                                    >
                                        <option value="">اختر الوحدة</option>
                                        <option value="kg">
                                            كيلوجرام (kg)
                                        </option>
                                        <option value="g">جرام (g)</option>
                                        <option value="liter">لتر</option>
                                        <option value="piece">قطعة</option>
                                    </select>
                                    <p
                                        v-if="form.errors.measurement_unit"
                                        class="text-sm text-red-600 mt-1.5"
                                    >
                                        {{ form.errors.measurement_unit }}
                                    </p>
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

                        <!-- Variants -->
                        <fieldset
                            v-if="form.type === 'variant'"
                            class="border-t border-gray-100 pt-6 mt-6"
                        >
                            <legend
                                class="text-base font-bold text-gray-800 mb-5"
                            >
                                المتغيرات (الأحجام / الأنواع)
                            </legend>
                            <div class="space-y-3">
                                <div
                                    v-for="(variant, index) in form.variants"
                                    :key="index"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-1">
                                        <input
                                            v-model="variant.name"
                                            type="text"
                                            :placeholder="`مثال: ${index === 0 ? 'صغير' : index === 1 ? 'وسط' : 'كبير'}`"
                                            class="w-full py-2.5 px-4 border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none text-sm transition-all"
                                            :class="{
                                                'border-red-300':
                                                    form.errors[
                                                        `variants.${index}.name`
                                                    ],
                                            }"
                                        />
                                        <p
                                            v-if="
                                                form.errors[
                                                    `variants.${index}.name`
                                                ]
                                            "
                                            class="text-xs text-red-600 mt-1"
                                        >
                                            {{
                                                form.errors[
                                                    `variants.${index}.name`
                                                ]
                                            }}
                                        </p>
                                    </div>
                                    <div class="w-32">
                                        <input
                                            v-model="variant.price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            dir="ltr"
                                            placeholder="السعر"
                                            class="w-full py-2.5 px-4 border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none text-sm transition-all"
                                            :class="{
                                                'border-red-300':
                                                    form.errors[
                                                        `variants.${index}.price`
                                                    ],
                                            }"
                                        />
                                        <p
                                            v-if="
                                                form.errors[
                                                    `variants.${index}.price`
                                                ]
                                            "
                                            class="text-xs text-red-600 mt-1"
                                        >
                                            {{
                                                form.errors[
                                                    `variants.${index}.price`
                                                ]
                                            }}
                                        </p>
                                    </div>
                                    <button
                                        v-if="form.variants.length > 1"
                                        type="button"
                                        @click="removeVariant(index)"
                                        class="mt-2 p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
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
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="addVariant"
                                class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-dashed border-gray-300 text-gray-600 hover:border-primary-500 hover:text-primary-900 hover:bg-primary-50/30 transition-all"
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
                            <p
                                v-if="form.errors.variants"
                                class="text-sm text-red-600 mt-2"
                            >
                                {{ form.errors.variants }}
                            </p>
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
                                        : "حفظ المنتج"
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
                                    اسحب الصورة هنا أو اضغط للاختيار
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
                                form.processing ? "جاري الحفظ..." : "حفظ المنتج"
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
