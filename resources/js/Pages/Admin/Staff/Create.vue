<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const form = useForm({
    name: "",
    phone: "",
    email: "",
    role: "admin",
    password: "",
    password_confirmation: "",
});

function submit() {
    form.post("/admin/staff");
}
</script>

<template>
    <AdminLayout title="إضافة موظف">
        <div class="max-w-3xl mx-auto">
            <Link
                href="/admin/staff"
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
                العودة للموظفين
            </Link>

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-primary-900">
                    إضافة موظف جديد
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    أدخل بيانات الموظف الجديد وحدد دوره
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Role Selection -->
                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-3"
                        >الدور <span class="text-red-500">*</span></label
                    >
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            @click="form.role = 'admin'"
                            class="p-4 rounded-xl border-2 text-start transition-all"
                            :class="
                                form.role === 'admin'
                                    ? 'border-primary-500 bg-primary-50'
                                    : 'border-gray-200 hover:border-gray-300'
                            "
                        >
                            <p
                                class="font-semibold"
                                :class="
                                    form.role === 'admin'
                                        ? 'text-primary-900'
                                        : 'text-gray-700'
                                "
                            >
                                مدير
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                صلاحيات كاملة (ما عدا إدارة الموظفين)
                            </p>
                        </button>
                        <button
                            type="button"
                            @click="form.role = 'customer_service'"
                            class="p-4 rounded-xl border-2 text-start transition-all"
                            :class="
                                form.role === 'customer_service'
                                    ? 'border-secondary-500 bg-secondary-50'
                                    : 'border-gray-200 hover:border-gray-300'
                            "
                        >
                            <p
                                class="font-semibold"
                                :class="
                                    form.role === 'customer_service'
                                        ? 'text-secondary-700'
                                        : 'text-gray-700'
                                "
                            >
                                خدمة عملاء
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                إدارة الطلبات والتوصيلات فقط
                            </p>
                        </button>
                    </div>
                    <p
                        v-if="form.errors.role"
                        class="text-sm text-red-600 mt-1.5"
                    >
                        {{ form.errors.role }}
                    </p>
                </div>

                <!-- Name -->
                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                        >الاسم <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="أدخل اسم الموظف"
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

                <!-- Phone -->
                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                        >رقم الهاتف <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="form.phone"
                        type="tel"
                        dir="ltr"
                        placeholder="01xxxxxxxxx"
                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                        :class="{ 'border-red-300': form.errors.phone }"
                    />
                    <p
                        v-if="form.errors.phone"
                        class="text-sm text-red-600 mt-1.5"
                    >
                        {{ form.errors.phone }}
                    </p>
                </div>

                <!-- Email -->
                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                        >البريد الإلكتروني</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        dir="ltr"
                        placeholder="example@email.com (اختياري)"
                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                        :class="{ 'border-red-300': form.errors.email }"
                    />
                    <p
                        v-if="form.errors.email"
                        class="text-sm text-red-600 mt-1.5"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Password -->
                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                        >كلمة المرور <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="8 أحرف على الأقل"
                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                        :class="{ 'border-red-300': form.errors.password }"
                    />
                    <p
                        v-if="form.errors.password"
                        class="text-sm text-red-600 mt-1.5"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                        >تأكيد كلمة المرور
                        <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="أعد إدخال كلمة المرور"
                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                    />
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
                        {{ form.processing ? "جاري الحفظ..." : "حفظ الموظف" }}
                    </button>
                    <Link
                        href="/admin/staff"
                        class="text-gray-500 hover:text-gray-700 transition-colors"
                        >إلغاء</Link
                    >
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
