<script setup>
import { ref, computed } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import LocationPicker from "@/Components/LocationPicker.vue";

const props = defineProps({
    store: { type: Object, required: true },
    storeTypes: { type: Array, default: () => [] },
});

function formatTime(time) {
    if (!time) return "";
    if (typeof time === "string" && time.length <= 5) return time;
    const match = String(time).match(/(\d{2}):(\d{2})/);
    return match ? `${match[1]}:${match[2]}` : "";
}

const currentLogo = computed(() => {
    const media = props.store.media || [];
    const m = media.find((i) => i.collection_name === "logo");
    return m ? m.original_url : null;
});

const currentCover = computed(() => {
    const media = props.store.media || [];
    const m = media.find((i) => i.collection_name === "cover");
    return m ? m.original_url : null;
});

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

const logoPreview = ref(null);
const coverPreview = ref(null);
const logoInput = ref(null);
const coverInput = ref(null);

function handleLogo(e) {
    const file = e.target.files[0] || null;
    form.logo = file;
    if (file) {
        logoPreview.value = URL.createObjectURL(file);
    } else {
        logoPreview.value = null;
    }
}

function handleCover(e) {
    const file = e.target.files[0] || null;
    form.cover = file;
    if (file) {
        coverPreview.value = URL.createObjectURL(file);
    } else {
        coverPreview.value = null;
    }
}

function removeLogo() {
    form.logo = null;
    logoPreview.value = null;
    if (logoInput.value) logoInput.value.value = "";
}

function removeCover() {
    form.cover = null;
    coverPreview.value = null;
    if (coverInput.value) coverInput.value.value = "";
}

function submit() {
    form.post(`/admin/stores/${props.store.slug}`, {
        forceFormData: true,
    });
}
</script>

<template>
    <AdminLayout title="تعديل المتجر">
        <div class="max-w-7xl mx-auto">
            <!-- Back Link -->
            <Link
                href="/admin/stores"
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
                العودة للمتاجر
            </Link>

            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-primary-900">
                    تعديل المتجر: {{ store.name }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">تعديل بيانات المتجر</p>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Form Fields (2/3) -->
                    <div class="lg:col-span-2 space-y-0">
                        <!-- Section: Basic Info -->
                        <fieldset>
                            <legend
                                class="text-base font-bold text-gray-800 mb-5"
                            >
                                المعلومات الأساسية
                            </legend>
                            <div class="space-y-5">
                                <!-- Name -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                    >
                                        اسم المتجر
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        placeholder="أدخل اسم المتجر"
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

                                <!-- Store Type -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                    >
                                        نوع المتجر
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.store_type_id"
                                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none appearance-none bg-white"
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                                form.errors.store_type_id,
                                        }"
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
                                        {{ form.errors.store_type_id }}
                                    </p>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                    >
                                        الهاتف
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.phone"
                                        type="text"
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
                            </div>
                        </fieldset>

                        <!-- Section: Location -->
                        <fieldset class="border-t border-gray-100 pt-6 mt-6">
                            <legend
                                class="text-base font-bold text-gray-800 mb-5"
                            >
                                الموقع والعنوان
                            </legend>
                            <div class="space-y-5">
                                <!-- Address -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                    >
                                        العنوان
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.address"
                                        type="text"
                                        placeholder="أدخل عنوان المتجر"
                                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none"
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                                form.errors.address,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.address"
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
                                        {{ form.errors.address }}
                                    </p>
                                </div>

                                <!-- Lat/Lng with Map -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                        >الموقع على الخريطة</label
                                    >
                                    <LocationPicker
                                        :latitude="form.latitude"
                                        :longitude="form.longitude"
                                        @update:latitude="
                                            form.latitude = $event
                                        "
                                        @update:longitude="
                                            form.longitude = $event
                                        "
                                    />
                                    <div class="grid grid-cols-2 gap-4 mt-3">
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-500 mb-1"
                                                >خط العرض</label
                                            >
                                            <input
                                                v-model="form.latitude"
                                                type="text"
                                                dir="ltr"
                                                placeholder="30.0444"
                                                readonly
                                                class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none"
                                                :class="{
                                                    'border-red-300':
                                                        form.errors.latitude,
                                                }"
                                            />
                                            <p
                                                v-if="form.errors.latitude"
                                                class="text-xs text-red-600 mt-1"
                                            >
                                                {{ form.errors.latitude }}
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-500 mb-1"
                                                >خط الطول</label
                                            >
                                            <input
                                                v-model="form.longitude"
                                                type="text"
                                                dir="ltr"
                                                placeholder="31.2357"
                                                readonly
                                                class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none"
                                                :class="{
                                                    'border-red-300':
                                                        form.errors.longitude,
                                                }"
                                            />
                                            <p
                                                v-if="form.errors.longitude"
                                                class="text-xs text-red-600 mt-1"
                                            >
                                                {{ form.errors.longitude }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Section: Working Hours -->
                        <fieldset class="border-t border-gray-100 pt-6 mt-6">
                            <legend
                                class="text-base font-bold text-gray-800 mb-5"
                            >
                                أوقات العمل
                            </legend>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                    >
                                        <span class="flex items-center gap-2">
                                            <svg
                                                class="w-4 h-4 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            وقت الفتح
                                        </span>
                                    </label>
                                    <input
                                        v-model="form.opening_time"
                                        type="time"
                                        dir="ltr"
                                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none"
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                                form.errors.opening_time,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.opening_time"
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
                                        {{ form.errors.opening_time }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2"
                                    >
                                        <span class="flex items-center gap-2">
                                            <svg
                                                class="w-4 h-4 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            وقت الإغلاق
                                        </span>
                                    </label>
                                    <input
                                        v-model="form.closing_time"
                                        type="time"
                                        dir="ltr"
                                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 outline-none"
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500/20':
                                                form.errors.closing_time,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.closing_time"
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
                                        {{ form.errors.closing_time }}
                                    </p>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Desktop Submit -->
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
                                        : "حفظ المتجر"
                                }}
                            </button>
                            <Link
                                href="/admin/stores"
                                class="text-gray-500 hover:text-gray-700 transition-colors duration-200"
                                >إلغاء</Link
                            >
                        </div>
                    </div>

                    <!-- Sidebar (1/3) -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Logo Upload -->
                        <div
                            class="bg-white rounded-xl border border-gray-200 shadow-sm p-5"
                        >
                            <h3 class="text-sm font-bold text-gray-800 mb-4">
                                الشعار
                            </h3>
                            <div
                                v-if="!logoPreview"
                                @click="logoInput?.click()"
                                class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-primary-500 hover:bg-primary-50/30 transition-all duration-200 cursor-pointer"
                            >
                                <svg
                                    class="w-10 h-10 mx-auto text-gray-400 mb-3"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                    />
                                </svg>
                                <p class="text-sm text-gray-600 font-medium">
                                    اسحب الصورة هنا أو اضغط للاختيار
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    PNG, JPG, WebP — حد أقصى 5MB
                                </p>
                            </div>
                            <div v-else class="relative group">
                                <img
                                    :src="logoPreview"
                                    alt="معاينة الشعار"
                                    class="w-full h-40 object-cover rounded-xl"
                                />
                                <button
                                    type="button"
                                    @click="removeLogo"
                                    class="absolute top-2 start-2 bg-red-500 text-white rounded-lg p-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-md"
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
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <input
                                ref="logoInput"
                                type="file"
                                accept="image/*"
                                @change="handleLogo"
                                class="hidden"
                            />
                            <p
                                v-if="form.errors.logo"
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
                                {{ form.errors.logo }}
                            </p>
                        </div>

                        <!-- Cover Upload -->
                        <div
                            class="bg-white rounded-xl border border-gray-200 shadow-sm p-5"
                        >
                            <h3 class="text-sm font-bold text-gray-800 mb-4">
                                صورة الغلاف
                            </h3>
                            <div
                                v-if="!coverPreview"
                                @click="coverInput?.click()"
                                class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-primary-500 hover:bg-primary-50/30 transition-all duration-200 cursor-pointer"
                            >
                                <svg
                                    class="w-10 h-10 mx-auto text-gray-400 mb-3"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                    />
                                </svg>
                                <p class="text-sm text-gray-600 font-medium">
                                    اسحب الصورة هنا أو اضغط للاختيار
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    PNG, JPG, WebP — حد أقصى 5MB
                                </p>
                            </div>
                            <div v-else class="relative group">
                                <img
                                    :src="coverPreview"
                                    alt="معاينة الغلاف"
                                    class="w-full h-40 object-cover rounded-xl"
                                />
                                <button
                                    type="button"
                                    @click="removeCover"
                                    class="absolute top-2 start-2 bg-red-500 text-white rounded-lg p-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-md"
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
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <input
                                ref="coverInput"
                                type="file"
                                accept="image/*"
                                @change="handleCover"
                                class="hidden"
                            />
                            <p
                                v-if="form.errors.cover"
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
                                {{ form.errors.cover }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Mobile Sticky Submit -->
                <div
                    class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] p-4 z-50"
                >
                    <div class="flex items-center gap-3 max-w-7xl mx-auto">
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
                                form.processing ? "جاري الحفظ..." : "حفظ المتجر"
                            }}
                        </button>
                        <Link
                            href="/admin/stores"
                            class="text-gray-500 hover:text-gray-700 px-4 py-3 transition-colors duration-200"
                            >إلغاء</Link
                        >
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
