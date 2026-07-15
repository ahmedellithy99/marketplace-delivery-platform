<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const page = usePage();
const flash = page.props.flash || {};

const form = useForm({
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post("/reset-password");
};
</script>

<template>
    <AuthLayout title="تعيين كلمة مرور جديدة">
        <h1 class="text-2xl font-bold text-primary-900 text-center mb-2">
            تعيين كلمة مرور جديدة
        </h1>
        <p class="text-sm text-gray-500 text-center mb-6">
            أدخل كلمة المرور الجديدة
        </p>

        <form @submit.prevent="submit">
            <!-- Error -->
            <div
                v-if="flash.error"
                class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm"
            >
                {{ flash.error }}
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label
                    for="password"
                    class="block text-sm font-medium text-gray-700 mb-1.5"
                >
                    كلمة المرور الجديدة
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                    required
                />
                <p
                    v-if="form.errors.password"
                    class="mt-1 text-sm text-red-600"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Confirm Password -->
            <div class="mb-6">
                <label
                    for="password_confirmation"
                    class="block text-sm font-medium text-gray-700 mb-1.5"
                >
                    تأكيد كلمة المرور
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                    required
                />
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full bg-primary-900 hover:bg-primary-800 text-white py-3 px-4 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span
                    v-if="form.processing"
                    class="flex items-center justify-center gap-2"
                >
                    <svg
                        class="animate-spin h-4 w-4"
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
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    جاري الحفظ...
                </span>
                <span v-else>حفظ كلمة المرور</span>
            </button>
        </form>
    </AuthLayout>
</template>
