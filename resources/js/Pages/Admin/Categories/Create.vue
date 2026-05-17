<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    parentCategories: { type: Array, default: () => [] },
});

const form = useForm({
    name: "",
    parent_id: "",
    image: null,
});

function handleImage(e) {
    form.image = e.target.files[0] || null;
}

function submit() {
    form.post("/admin/categories", {
        forceFormData: true,
    });
}
</script>

<template>
    <AdminLayout title="إضافة قسم">
        <div class="max-w-2xl">
            <Link
                href="/admin/categories"
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
                العودة للأقسام
            </Link>

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
            >
                <h2 class="text-lg font-bold text-primary-900 mb-6">
                    إضافة قسم جديد
                </h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >اسم القسم</label
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

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >القسم الأب (اختياري)</label
                        >
                        <select
                            v-model="form.parent_id"
                            class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                        >
                            <option value="">بدون قسم أب (قسم رئيسي)</option>
                            <option
                                v-for="cat in parentCategories"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.parent_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.parent_id }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >صورة القسم</label
                        >
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleImage"
                            class="w-full text-sm text-gray-600 file:me-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-900 hover:file:bg-primary-100"
                        />
                        <p
                            v-if="form.errors.image"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.image }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-primary-900 text-white px-6 py-2.5 rounded-lg hover:bg-primary-800 transition-colors text-sm font-medium disabled:opacity-50"
                        >
                            {{
                                form.processing ? "جاري الحفظ..." : "حفظ القسم"
                            }}
                        </button>
                        <Link
                            href="/admin/categories"
                            class="text-sm text-gray-600 hover:text-gray-800"
                            >إلغاء</Link
                        >
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
