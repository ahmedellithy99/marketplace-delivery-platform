<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const form = useForm({
    name: "",
    phone: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post("/register", {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <AuthLayout>
        <h1 class="text-2xl font-bold text-primary-900 text-center mb-2">
            إنشاء حساب
        </h1>
        <p class="text-sm text-gray-500 text-center mb-6">
            أنشئ حسابك للبدء بالتسوق والطلب
        </p>

        <form @submit.prevent="submit">
            <!-- Name -->
            <div class="mb-4">
                <label
                    for="name"
                    class="block text-sm font-medium text-gray-700 mb-1.5"
                >
                    الاسم الكامل
                </label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="أدخل اسمك الكامل"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                    required
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                    {{ form.errors.name }}
                </p>
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

            <!-- Email (Optional) -->
            <div class="mb-4">
                <label
                    for="email"
                    class="block text-sm font-medium text-gray-700 mb-1.5"
                >
                    البريد الإلكتروني
                    <span class="text-gray-400 font-normal">(اختياري)</span>
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    dir="ltr"
                    placeholder="example@email.com"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                    {{ form.errors.email }}
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
                <p
                    v-if="form.errors.password"
                    class="mt-1 text-sm text-red-600"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Password Confirmation -->
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
                    جاري التسجيل...
                </span>
                <span v-else>إنشاء حساب</span>
            </button>

            <!-- Login Link -->
            <p class="mt-6 text-center text-sm text-gray-600">
                لديك حساب بالفعل؟
                <Link
                    href="/login"
                    class="text-primary-900 hover:text-primary-700 font-medium"
                >
                    تسجيل الدخول
                </Link>
            </p>
        </form>
    </AuthLayout>
</template>
