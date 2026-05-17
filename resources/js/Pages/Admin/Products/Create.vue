<script setup>
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

function handleImages(e) {
    form.images = Array.from(e.target.files);
}

function submit() {
    form.post("/admin/products", {
        forceFormData: true,
    });
}
</script>

<template>
    <AdminLayout title="إضافة منتج">
        <div class="max-w-3xl">
            <!-- Back Link -->
            <Link
                href="/admin/products"
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
                العودة للمنتجات
            </Link>

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
            >
                <h2 class="text-lg font-bold text-primary-900 mb-6">
                    إضافة منتج جديد
                </h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Store -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >المتجر</label
                        >
                        <select
                            v-model="form.store_id"
                            class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
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
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.store_id }}
                        </p>
                    </div>

                    <!-- Category -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >القسم</label
                        >
                        <select
                            v-model="form.category_id"
                            class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
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
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.category_id }}
                        </p>
                    </div>

                    <!-- Name -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >اسم المنتج</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >الوصف</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                        ></textarea>
                        <p
                            v-if="form.errors.description"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Price / Discounted Price -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >السعر (جنيه)</label
                            >
                            <input
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                dir="ltr"
                                class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                            />
                            <p
                                v-if="form.errors.price"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.price }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >السعر بعد الخصم (جنيه)</label
                            >
                            <input
                                v-model="form.discounted_price"
                                type="number"
                                step="0.01"
                                min="0"
                                dir="ltr"
                                class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                            />
                            <p
                                v-if="form.errors.discounted_price"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.discounted_price }}
                            </p>
                        </div>
                    </div>

                    <!-- Images Upload -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >صور المنتج</label
                        >
                        <input
                            type="file"
                            accept="image/*"
                            multiple
                            @change="handleImages"
                            class="w-full text-sm text-gray-600 file:me-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-900 hover:file:bg-primary-100"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            يمكنك اختيار أكثر من صورة
                        </p>
                        <p
                            v-if="form.errors.images"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.images }}
                        </p>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-primary-900 text-white px-6 py-2.5 rounded-lg hover:bg-primary-800 transition-colors text-sm font-medium disabled:opacity-50"
                        >
                            {{
                                form.processing ? "جاري الحفظ..." : "حفظ المنتج"
                            }}
                        </button>
                        <Link
                            href="/admin/products"
                            class="text-sm text-gray-600 hover:text-gray-800"
                            >إلغاء</Link
                        >
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
