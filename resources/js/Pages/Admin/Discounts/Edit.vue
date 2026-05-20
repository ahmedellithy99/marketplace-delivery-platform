<script setup>
import { ref, watch } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    discount: { type: Object, required: true },
    targetOptions: { type: Array, default: () => [] },
});

// Get current target IDs based on scope
function getCurrentTargetIds() {
    const d = props.discount;
    const targets = d.products || d.variants || d.stores || d.categories || [];
    return targets.map((t) => t.id);
}

const form = useForm({
    _method: "PUT",
    name: props.discount.name,
    type: props.discount.type,
    value: props.discount.value,
    scope: props.discount.scope,
    starts_at: props.discount.starts_at
        ? props.discount.starts_at.slice(0, 16)
        : "",
    ends_at: props.discount.ends_at ? props.discount.ends_at.slice(0, 16) : "",
    usage_limit: props.discount.usage_limit || "",
    is_active: props.discount.is_active,
    target_ids: getCurrentTargetIds(),
});

const targets = ref(props.targetOptions);

watch(
    () => form.scope,
    (newScope) => {
        form.target_ids = [];
        fetch(`/admin/discounts-targets?scope=${newScope}`)
            .then((r) => r.json())
            .then((data) => {
                targets.value = data;
            });
    },
);

function getTargetLabel(target) {
    if (form.scope === "variant" && target.product) {
        return `${target.product.name} — ${target.name} (${target.price} جنيه)`;
    }
    return target.name;
}

function submit() {
    form.post(`/admin/discounts/${props.discount.id}`);
}
</script>

<template>
    <AdminLayout title="تعديل الخصم">
        <div class="max-w-3xl mx-auto">
            <Link
                href="/admin/discounts"
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
                العودة للخصومات
            </Link>

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-primary-900">
                    تعديل: {{ discount.name }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                        >اسم الخصم <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                        :class="{ 'border-red-300': form.errors.name }"
                    />
                    <p
                        v-if="form.errors.name"
                        class="text-sm text-red-600 mt-1.5"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700 mb-2"
                            >نوع الخصم</label
                        >
                        <select
                            v-model="form.type"
                            class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none appearance-none bg-white transition-all"
                        >
                            <option value="percentage">نسبة مئوية (%)</option>
                            <option value="fixed">مبلغ ثابت (جنيه)</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700 mb-2"
                            >القيمة</label
                        >
                        <input
                            v-model="form.value"
                            type="number"
                            step="0.01"
                            min="0"
                            dir="ltr"
                            class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                            :class="{ 'border-red-300': form.errors.value }"
                        />
                        <p
                            v-if="form.errors.value"
                            class="text-sm text-red-600 mt-1.5"
                        >
                            {{ form.errors.value }}
                        </p>
                    </div>
                </div>

                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                        >نطاق الخصم</label
                    >
                    <div class="grid grid-cols-4 gap-3">
                        <button
                            type="button"
                            @click="form.scope = 'product'"
                            class="p-3 rounded-xl border-2 text-center transition-all text-sm font-medium"
                            :class="
                                form.scope === 'product'
                                    ? 'border-primary-500 bg-primary-50 text-primary-900'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'
                            "
                        >
                            منتج
                        </button>
                        <button
                            type="button"
                            @click="form.scope = 'variant'"
                            class="p-3 rounded-xl border-2 text-center transition-all text-sm font-medium"
                            :class="
                                form.scope === 'variant'
                                    ? 'border-primary-500 bg-primary-50 text-primary-900'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'
                            "
                        >
                            متغير
                        </button>
                        <button
                            type="button"
                            @click="form.scope = 'store'"
                            class="p-3 rounded-xl border-2 text-center transition-all text-sm font-medium"
                            :class="
                                form.scope === 'store'
                                    ? 'border-primary-500 bg-primary-50 text-primary-900'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'
                            "
                        >
                            متجر
                        </button>
                        <button
                            type="button"
                            @click="form.scope = 'category'"
                            class="p-3 rounded-xl border-2 text-center transition-all text-sm font-medium"
                            :class="
                                form.scope === 'category'
                                    ? 'border-primary-500 bg-primary-50 text-primary-900'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'
                            "
                        >
                            قسم
                        </button>
                    </div>
                </div>

                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                        >اختر الأهداف</label
                    >
                    <div
                        class="border border-gray-200 rounded-xl max-h-60 overflow-y-auto p-3 space-y-1"
                    >
                        <label
                            v-for="target in targets"
                            :key="target.id"
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                        >
                            <input
                                type="checkbox"
                                :value="target.id"
                                v-model="form.target_ids"
                                class="w-4 h-4 rounded border-gray-300 text-primary-900 focus:ring-primary-500"
                            />
                            <span class="text-sm text-gray-700">{{
                                getTargetLabel(target)
                            }}</span>
                        </label>
                        <p
                            v-if="targets.length === 0"
                            class="text-sm text-gray-400 text-center py-4"
                        >
                            لا توجد عناصر
                        </p>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        تم اختيار {{ form.target_ids.length }} عنصر
                    </p>
                    <p
                        v-if="form.errors.target_ids"
                        class="text-sm text-red-600 mt-1"
                    >
                        {{ form.errors.target_ids }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700 mb-2"
                            >تاريخ البداية</label
                        >
                        <input
                            v-model="form.starts_at"
                            type="datetime-local"
                            dir="ltr"
                            class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700 mb-2"
                            >تاريخ الانتهاء</label
                        >
                        <input
                            v-model="form.ends_at"
                            type="datetime-local"
                            dir="ltr"
                            class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <label
                        class="relative inline-flex items-center cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            v-model="form.is_active"
                            class="sr-only peer"
                        />
                        <div
                            class="w-11 h-6 bg-gray-300 peer-focus:ring-2 peer-focus:ring-primary-500/20 rounded-full peer peer-checked:bg-green-500 transition-colors"
                        ></div>
                        <div
                            class="absolute start-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:-translate-x-5"
                        ></div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">مفعّل</span>
                </div>

                <div
                    class="flex items-center gap-4 pt-4 border-t border-gray-100"
                >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-primary-900 hover:bg-primary-800 text-white px-8 py-3 rounded-xl font-semibold shadow-sm hover:shadow-md transition-all disabled:opacity-50 flex items-center gap-2"
                    >
                        {{
                            form.processing ? "جاري الحفظ..." : "حفظ التعديلات"
                        }}
                    </button>
                    <Link
                        href="/admin/discounts"
                        class="text-gray-500 hover:text-gray-700 transition-colors"
                        >إلغاء</Link
                    >
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
