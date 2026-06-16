<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const form = useForm({
    phone: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post("/login", {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <AuthLayout title="تسجيل الدخول">
        <h1 class="text-2xl font-bold text-primary-900 text-center mb-2">
            تسجيل الدخول
        </h1>
        <p class="text-sm text-gray-500 text-center mb-6">
            أدخل بياناتك للوصول إلى حسابك
        </p>

        <form @submit.prevent="submit">
            <!-- Credentials Error -->
            <div
                v-if="form.errors.credentials"
                class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm flex items-center gap-2"
            >
                <svg
                    class="w-4 h-4 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                {{ form.errors.credentials }}
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label
                    for="phone"
                    class="block text-sm font-medium text-gray-700 mb-1.5"
                >
                    رقم الهاتف
                </label>
                <input
                    id="phone"
                    v-model="form.phone"
                    type="text"
                    dir="ltr"
                    placeholder="05xxxxxxxx"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                    required
                />
                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                    {{ form.errors.phone }}
                </p>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label
                    for="password"
                    class="block text-sm font-medium text-gray-700 mb-1.5"
                >
                    كلمة المرور
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                    required
                />
            </div>

            <!-- Remember Me -->
            <div class="mb-6 flex items-center">
                <input
                    id="remember"
                    v-model="form.remember"
                    type="checkbox"
                    class="h-4 w-4 text-primary-900 border-gray-300 rounded focus:ring-primary-900"
                />
                <label for="remember" class="ms-2 text-sm text-gray-600"
                    >تذكرني</label
                >
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
                    جاري التحميل...
                </span>
                <span v-else>تسجيل الدخول</span>
            </button>

            <!-- Register Link -->
            <p class="mt-6 text-center text-sm text-gray-600">
                ليس لديك حساب؟
                <Link
                    href="/register"
                    class="text-primary-900 hover:text-primary-700 font-medium"
                >
                    إنشاء حساب جديد
                </Link>
            </p>
        </form>
    </AuthLayout>
</template>
