<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    deliveryMan: { type: Object, required: true },
});

const form = useForm({
    _method: "PUT",
    name: props.deliveryMan.name || "",
    phone: props.deliveryMan.phone || "",
    email: props.deliveryMan.email || "",
    password: "",
    password_confirmation: "",
});

function submit() {
    form.post(`/admin/delivery-men/${props.deliveryMan.id}`);
}
</script>

<template>
    <AdminLayout title="تعديل مندوب">
        <div class="max-w-3xl mx-auto">
            <!-- Back Link -->
            <Link
                href="/admin/delivery-men"
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
                العودة لمناديب التوصيل
            </Link>

            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-primary-900">
                    تعديل المندوب: {{ deliveryMan.name }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    تعديل بيانات مندوب التوصيل
                </p>
            </div>

            <form @submit.prevent="submit">
                <div class="space-y-0">
                    <fieldset>
                        <legend class="text-base font-bold text-gray-800 mb-5">
                            البيانات الأساسية
                        </legend>
                        <div class="space-y-5">
                            <!-- Name -->
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                >
                                    الاسم
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="أدخل اسم المندوب"
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

                            <!-- Phone -->
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                >
                                    رقم الهاتف
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    dir="ltr"
                                    placeholder="01xxxxxxxxx"
                                    class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                            form.errors.phone,
                                    }"
                                />
                                <p
                                    v-if="form.errors.phone"
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
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                >
                                    البريد الإلكتروني
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    dir="ltr"
                                    placeholder="example@email.com"
                                    class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                            form.errors.email,
                                    }"
                                />
                                <p
                                    v-if="form.errors.email"
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
                                    {{ form.errors.email }}
                                </p>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Password Section -->
                    <fieldset class="mt-8">
                        <legend class="text-base font-bold text-gray-800 mb-5">
                            كلمة المرور
                        </legend>
                        <p class="text-xs text-gray-400 mb-4">
                            اتركها فارغة إذا لم ترد تغيير كلمة المرور
                        </p>
                        <div class="space-y-5">
                            <!-- Password -->
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                >
                                    كلمة المرور الجديدة
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="أدخل كلمة المرور الجديدة"
                                    class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                            form.errors.password,
                                    }"
                                />
                                <p
                                    v-if="form.errors.password"
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
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- Password Confirmation -->
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                >
                                    تأكيد كلمة المرور
                                </label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="أعد إدخال كلمة المرور"
                                    class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none"
                                />
                            </div>
                        </div>
                    </fieldset>

                    <!-- Submit -->
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
                                    : "تحديث المندوب"
                            }}
                        </button>
                        <Link
                            href="/admin/delivery-men"
                            class="text-gray-500 hover:text-gray-700 transition-colors duration-200"
                            >إلغاء</Link
                        >
                    </div>
                </div>

                <!-- Mobile Sticky Submit -->
                <div
                    class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] p-4 z-50"
                >
                    <div class="flex items-center gap-3 max-w-3xl mx-auto">
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
                                form.processing
                                    ? "جاري الحفظ..."
                                    : "تحديث المندوب"
                            }}
                        </button>
                        <Link
                            href="/admin/delivery-men"
                            class="text-gray-500 hover:text-gray-700 px-4 py-3 transition-colors duration-200"
                            >إلغاء</Link
                        >
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
