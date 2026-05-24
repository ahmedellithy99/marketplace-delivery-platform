<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    storeType: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.storeType.name,
});

function submit() {
    form.put(`/admin/store-types/${props.storeType.id}`);
}
</script>

<template>
    <AdminLayout title="تعديل نوع متجر">
        <div class="max-w-3xl mx-auto">
            <Link
                href="/admin/store-types"
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
                العودة لأنواع المتاجر
            </Link>

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-primary-900">
                    تعديل نوع المتجر
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    تعديل بيانات نوع المتجر
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Name -->
                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                        >الاسم <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="أدخل اسم نوع المتجر"
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

                <!-- Submit -->
                <div
                    class="flex items-center gap-4 pt-4 border-t border-gray-100"
                >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-primary-900 hover:bg-primary-800 text-white px-8 py-3 rounded-xl font-semibold shadow-sm transition-all disabled:opacity-50"
                    >
                        {{ form.processing ? "جاري الحفظ..." : "تحديث النوع" }}
                    </button>
                    <Link
                        href="/admin/store-types"
                        class="text-gray-500 hover:text-gray-700 transition-colors"
                        >إلغاء</Link
                    >
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
