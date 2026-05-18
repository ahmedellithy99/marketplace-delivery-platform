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
    price: "",
    discounted_price: "",
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
    // Reset input if no files left
    if (newFiles.length === 0 && imagesInput.value) {
        imagesInput.value.value = "";
    }
}

function submit() {
    form.post("/admin/products", {
        forceFormData: true,
    });
}
</script>

<template>
    <AdminLayout title="إضافة منتج">
        <div class="max-w-7xl mx-auto">
            <!-- Back Link -->
            <Link
                href="/admin/products"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-primary-900 mb-6 transition-colors duration-200"
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

            <!-- Page Header -->
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
                    <!-- Main Form Fields (2/3) -->
                    <div class="lg:col-span-2 space-y-0">
                        <!-- Section: Basic Info -->
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
                                    >
                                        اسم المنتج
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        placeholder="أدخل اسم المنتج"
                                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none"
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                                form.errors.name,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="text-sm text-red-600 mt-1.5 flex items-center gap-1"
                                    >
                                        <svg
                                            class="w-4 h-4 shrink-0"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <!-- Store -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                    >
                                        المتجر
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.store_id"
                                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none appearance-none bg-white"
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500/20':
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
                                        class="text-sm text-red-600 mt-1.5 flex items-center gap-1"
                                    >
                                        <svg
                                            class="w-4 h-4 shrink-0"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        {{ form.errors.store_id }}
                                    </p>
                                </div>

                                <!-- Category -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                    >
                                        القسم
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.category_id"
                                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none appearance-none bg-white"
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                                form.errors.category_id,
                                        }"
                                    >
                                        <option value="">اختر القسم</option>
                                        <option
                                            v-for="category in categories"
                                            :key="category.id"
                                            :value="category.id"
                                        >
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.category_id"
                                        class="text-sm text-red-600 mt-1.5 flex items-center gap-1"
                                    >
                                        <svg
                                            class="w-4 h-4 shrink-0"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        {{ form.errors.category_id }}
                                    </p>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Section: Pricing & Description -->
                        <fieldset class="border-t border-gray-100 pt-6 mt-6">
                            <legend
                                class="text-base font-bold text-gray-800 mb-5"
                            >
                                السعر والوصف
                            </legend>
                            <div class="space-y-5">
                                <!-- Price / Discounted Price -->
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                                >
                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-gray-700 mb-2"
                                        >
                                            السعر (جنيه)
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            dir="ltr"
                                            placeholder="0.00"
                                            class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none"
                                            :class="{
                                                'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                                    form.errors.price,
                                            }"
                                        />
                                        <p
                                            v-if="form.errors.price"
                                            class="text-sm text-red-600 mt-1.5 flex items-center gap-1"
                                        >
                                            <svg
                                                class="w-4 h-4 shrink-0"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            {{ form.errors.price }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-gray-700 mb-2"
                                            >السعر بعد الخصم (جنيه)</label
                                        >
                                        <input
                                            v-model="form.discounted_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            dir="ltr"
                                            placeholder="0.00"
                                            class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none"
                                            :class="{
                                                'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                                    form.errors
                                                        .discounted_price,
                                            }"
                                        />
                                        <p class="text-xs text-gray-400 mt-1">
                                            اتركه فارغاً إذا لا يوجد خصم
                                        </p>
                                        <p
                                            v-if="form.errors.discounted_price"
                                            class="text-sm text-red-600 mt-1.5 flex items-center gap-1"
                                        >
                                            <svg
                                                class="w-4 h-4 shrink-0"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            {{ form.errors.discounted_price }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                        >الوصف</label
                                    >
                                    <textarea
                                        v-model="form.description"
                                        rows="5"
                                        placeholder="أدخل وصف المنتج..."
                                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none resize-none"
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                                form.errors.description,
                                        }"
                                    ></textarea>
                                    <p
                                        v-if="form.errors.description"
                                        class="text-sm text-red-600 mt-1.5 flex items-center gap-1"
                                    >
                                        <svg
                                            class="w-4 h-4 shrink-0"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        {{ form.errors.description }}
                                    </p>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Desktop Submit -->
                        <div
                            class="hidden lg:flex items-center gap-4 pt-8 mt-6 border-t border-gray-100"
                        >
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-primary-900 hover:bg-primary-800 text-white px-8 py-3 rounded-xl font-semibold shadow-sm hover:shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
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
                                class="text-gray-500 hover:text-gray-700 transition-colors duration-200"
                                >إلغاء</Link
                            >
                        </div>
                    </div>

                    <!-- Sidebar (1/3) -->
                    <div class="lg:col-span-1">
                        <div
                            class="bg-white rounded-xl border border-gray-200 shadow-sm p-5"
                        >
                            <h3 class="text-sm font-bold text-gray-800 mb-4">
                                الصور
                            </h3>
                            <!-- Upload Area -->
                            <div
                                @click="imagesInput?.click()"
                                class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-primary-500 hover:bg-primary-50/30 transition-all duration-200 cursor-pointer"
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
                                <p class="text-xs text-gray-400">
                                    يمكنك اختيار أكثر من صورة
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

                            <!-- Image Previews Grid -->
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
                                        class="absolute top-1 start-1 bg-red-500 text-white rounded-md p-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-md"
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

                            <p
                                v-if="form.errors.images"
                                class="text-sm text-red-600 mt-1.5 flex items-center gap-1"
                            >
                                <svg
                                    class="w-4 h-4 shrink-0"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                {{ form.errors.images }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Mobile Sticky Submit -->
                <div
                    class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] p-4 z-50"
                >
                    <div class="flex items-center gap-3 max-w-7xl mx-auto">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 bg-primary-900 hover:bg-primary-800 text-white px-8 py-3 rounded-xl font-semibold shadow-sm hover:shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
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
                            class="text-gray-500 hover:text-gray-700 px-4 py-3 transition-colors duration-200"
                            >إلغاء</Link
                        >
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
