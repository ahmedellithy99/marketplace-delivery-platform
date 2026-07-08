<script setup>
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed, ref } from "vue";

const props = defineProps({
    customers: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
});

const customersList = computed(() => props.customers?.data || []);
const paginationLinks = computed(() => props.customers?.links || []);

let filterTimer = null;
const filters = ref({
    search: new URLSearchParams(window.location.search).get("search") || "",
});

function applyFilters() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        const query = Object.fromEntries(
            Object.entries(filters.value).filter(([, v]) => v !== ""),
        );
        router.get("/admin/customers/trash", query, {
            preserveState: true,
            replace: true,
        });
    }, 300);
}

function restoreCustomer(customer) {
    if (confirm(`هل أنت متأكد من استعادة العميل "${customer.name}"؟`)) {
        router.patch(`/admin/customers/${customer.id}/restore`);
    }
}

function forceDeleteCustomer(customer) {
    if (
        confirm(
            `سيتم حذف العميل "${customer.name}" وجميع طلباته نهائياً. هل أنت متأكد؟`,
        )
    ) {
        router.delete(`/admin/customers/${customer.id}/force`);
    }
}
</script>

<template>
    <AdminLayout title="سلة المحذوفين — العملاء">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    سلة المحذوفين — العملاء
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    العملاء المحذوفون مؤقتاً. يمكن استعادتهم أو حذفهم نهائياً.
                </p>
            </div>
            <Link
                href="/admin/customers"
                class="inline-flex items-center gap-2 text-sm font-medium text-primary-900 hover:text-secondary-500 transition-colors"
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
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
                العودة للعملاء
            </Link>
        </div>

        <!-- Search Filter -->
        <div class="mb-4">
            <input
                v-model="filters.search"
                @input="applyFilters"
                type="text"
                placeholder="بحث بالاسم أو الهاتف أو البريد الإلكتروني..."
                class="w-full sm:w-80 py-2.5 px-4 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all text-sm"
            />
        </div>

        <!-- Table -->
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الاسم
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الهاتف
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                البريد الإلكتروني
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                تاريخ الحذف
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="customer in customersList"
                            :key="customer.id"
                            class="border-t border-gray-100 hover:bg-gray-50/50 transition-colors"
                        >
                            <td class="px-5 py-4">
                                <span class="font-medium text-gray-900">{{
                                    customer.name
                                }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-gray-600">{{
                                    customer.phone
                                }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-gray-600">{{
                                    customer.email || "—"
                                }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-gray-500 text-xs">{{
                                    new Date(
                                        customer.deleted_at,
                                    ).toLocaleDateString("ar-EG")
                                }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="restoreCustomer(customer)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-green-700 bg-green-50 border border-green-200 hover:bg-green-100 transition-all duration-200"
                                    >
                                        <svg
                                            class="w-3.5 h-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                            />
                                        </svg>
                                        استعادة
                                    </button>
                                    <button
                                        @click="forceDeleteCustomer(customer)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition-all duration-200"
                                    >
                                        <svg
                                            class="w-3.5 h-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                        حذف نهائي
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Empty State -->
                        <tr v-if="customersList.length === 0">
                            <td colspan="5" class="px-5 py-16 text-center">
                                <div
                                    class="flex flex-col items-center gap-4"
                                >
                                    <div
                                        class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center"
                                    >
                                        <svg
                                            class="w-8 h-8 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p
                                            class="text-gray-700 font-medium"
                                        >
                                            سلة المحذوفين فارغة
                                        </p>
                                        <p
                                            class="text-sm text-gray-400 mt-1"
                                        >
                                            لا يوجد عملاء محذوفون
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav
                v-if="paginationLinks.length > 3"
                class="flex items-center justify-center gap-1 px-5 py-4 border-t border-gray-100"
                aria-label="التنقل بين الصفحات"
            >
                <template v-for="(link, index) in paginationLinks" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="px-3.5 py-2 text-sm rounded-lg transition-all duration-200 font-medium"
                        :class="
                            link.active
                                ? 'bg-primary-900 text-white shadow-sm'
                                : 'text-gray-600 hover:bg-gray-100'
                        "
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="px-3.5 py-2 text-sm text-gray-300"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </div>
    </AdminLayout>
</template>
