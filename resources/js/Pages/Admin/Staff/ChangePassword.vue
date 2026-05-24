<script setup>
import { useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

function submit() {
    form.put("/admin/change-password", {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <AdminLayout title="تغيير كلمة المرور">
        <div class="max-w-3xl mx-auto">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-primary-900">
                    تغيير كلمة المرور
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    أدخل كلمة المرور الحالية ثم كلمة المرور الجديدة
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Current Password -->
                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                        >كلمة المرور الحالية
                        <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="form.current_password"
                        type="password"
                        placeholder="أدخل كلمة المرور الحالية"
                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                        :class="{
                            'border-red-300': form.errors.current_password,
                        }"
                    />
                    <p
                        v-if="form.errors.current_password"
                        class="text-sm text-red-600 mt-1.5"
                    >
                        {{ form.errors.current_password }}
                    </p>
                </div>

                <!-- New Password -->
                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                        >كلمة المرور الجديدة
                        <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="8 أحرف على الأقل"
                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                        :class="{
                            'border-red-300': form.errors.password,
                        }"
                    />
                    <p
                        v-if="form.errors.password"
                        class="text-sm text-red-600 mt-1.5"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                        >تأكيد كلمة المرور
                        <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="أعد إدخال كلمة المرور الجديدة"
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
                        {{
                            form.processing
                                ? "جاري الحفظ..."
                                : "تغيير كلمة المرور"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
