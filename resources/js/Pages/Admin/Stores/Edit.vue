<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    store: { type: Object, required: true },
    storeTypes: { type: Array, default: () => [] },
});

function formatTime(time) {
    if (!time) return "";
    if (typeof time === "string" && time.length <= 5) return time;
    // Handle datetime strings like "2026-01-01T08:00:00.000000Z" or "08:00:00"
    const match = String(time).match(/(\d{2}):(\d{2})/);
    return match ? `${match[1]}:${match[2]}` : "";
}

const form = useForm({
    _method: "PUT",
    name: props.store.name || "",
    store_type_id: props.store.store_type_id || "",
    phone: props.store.phone || "",
    address: props.store.address || "",
    latitude: props.store.latitude || "",
    longitude: props.store.longitude || "",
    opening_time: formatTime(props.store.opening_time),
    closing_time: formatTime(props.store.closing_time),
    logo: null,
    cover: null,
});

function handleLogo(e) {
    form.logo = e.target.files[0] || null;
}

function handleCover(e) {
    form.cover = e.target.files[0] || null;
}

function submit() {
    form.post(`/admin/stores/${props.store.slug}`, {
        forceFormData: true,
    });
}
</script>

<template>
    <AdminLayout title="تعديل المتجر">
        <div class="max-w-3xl">
            <!-- Back Link -->
            <Link
                href="/admin/stores"
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
                العودة للمتاجر
            </Link>

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
            >
                <h2 class="text-lg font-bold text-primary-900 mb-6">
                    تعديل المتجر: {{ store.name }}
                </h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Name -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >اسم المتجر</label
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

                    <!-- Store Type -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >نوع المتجر</label
                        >
                        <select
                            v-model="form.store_type_id"
                            class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                        >
                            <option value="">اختر النوع</option>
                            <option
                                v-for="type in storeTypes"
                                :key="type.id"
                                :value="type.id"
                            >
                                {{ type.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.store_type_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.store_type_id }}
                        </p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >الهاتف</label
                        >
                        <input
                            v-model="form.phone"
                            type="text"
                            dir="ltr"
                            class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                        />
                        <p
                            v-if="form.errors.phone"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <!-- Address -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >العنوان</label
                        >
                        <input
                            v-model="form.address"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                        />
                        <p
                            v-if="form.errors.address"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.address }}
                        </p>
                    </div>

                    <!-- Lat/Lng -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >خط العرض</label
                            >
                            <input
                                v-model="form.latitude"
                                type="text"
                                dir="ltr"
                                class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                            />
                            <p
                                v-if="form.errors.latitude"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.latitude }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >خط الطول</label
                            >
                            <input
                                v-model="form.longitude"
                                type="text"
                                dir="ltr"
                                class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                            />
                            <p
                                v-if="form.errors.longitude"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.longitude }}
                            </p>
                        </div>
                    </div>

                    <!-- Opening/Closing Time -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >وقت الفتح</label
                            >
                            <input
                                v-model="form.opening_time"
                                type="time"
                                dir="ltr"
                                class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                            />
                            <p
                                v-if="form.errors.opening_time"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.opening_time }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >وقت الإغلاق</label
                            >
                            <input
                                v-model="form.closing_time"
                                type="time"
                                dir="ltr"
                                class="w-full rounded-lg border-gray-300 focus:ring-primary-900 focus:border-primary-900"
                            />
                            <p
                                v-if="form.errors.closing_time"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.closing_time }}
                            </p>
                        </div>
                    </div>

                    <!-- Logo Upload -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >الشعار</label
                        >
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleLogo"
                            class="w-full text-sm text-gray-600 file:me-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-900 hover:file:bg-primary-100"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            اتركه فارغاً للإبقاء على الشعار الحالي
                        </p>
                        <p
                            v-if="form.errors.logo"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.logo }}
                        </p>
                    </div>

                    <!-- Cover Upload -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >صورة الغلاف</label
                        >
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleCover"
                            class="w-full text-sm text-gray-600 file:me-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-900 hover:file:bg-primary-100"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            اتركه فارغاً للإبقاء على الغلاف الحالي
                        </p>
                        <p
                            v-if="form.errors.cover"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.cover }}
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
                                form.processing
                                    ? "جاري الحفظ..."
                                    : "تحديث المتجر"
                            }}
                        </button>
                        <Link
                            href="/admin/stores"
                            class="text-sm text-gray-600 hover:text-gray-800"
                            >إلغاء</Link
                        >
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
