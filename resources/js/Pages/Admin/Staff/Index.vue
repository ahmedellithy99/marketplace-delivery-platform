<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    staff: { type: Object, default: () => ({ data: [], links: [] }) },
});

const staffList = computed(() => props.staff?.data || []);
const paginationLinks = computed(() => props.staff?.links || []);

let filterTimer;
const filters = ref({
    search: new URLSearchParams(window.location.search).get("search") || "",
});

function applyFilters() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        const query = Object.fromEntries(
            Object.entries(filters.value).filter(([, v]) => v !== ""),
        );
        router.get("/admin/staff", query, {
            preserveState: true,
            replace: true,
        });
    }, 300);
}

function deleteStaff(member) {
    if (confirm(`هل أنت متأكد من حذف "${member.name}"؟`)) {
        router.delete(`/admin/staff/${member.id}`);
    }
}

function roleLabel(role) {
    const labels = {
        admin: "مدير",
        customer_service: "خدمة عملاء",
    };
    return labels[role] || role;
}

function roleBadgeClass(role) {
    if (role === "admin")
        return "bg-primary-50 text-primary-700 border border-primary-200";
    return "bg-secondary-50 text-secondary-700 border border-secondary-200";
}
</script>

<template>
    <AdminLayout title="إدارة الموظفين">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">إدارة الموظفين</h2>
                <p class="text-sm text-gray-500 mt-1">
                    إضافة وتعديل المديرين وموظفي خدمة العملاء
                </p>
            </div>
            <Link
                href="/admin/staff/create"
                class="inline-flex items-center gap-2 bg-primary-900 text-white px-5 py-2.5 rounded-lg hover:bg-primary-800 shadow-sm transition-all text-sm font-medium"
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
                        d="M12 4v16m8-8H4"
                    />
                </svg>
                إضافة موظف
            </Link>
        </div>

        <!-- Search -->
        <div class="mb-5">
            <div class="relative max-w-md">
                <svg
                    class="absolute start-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                </svg>
                <input
                    v-model="filters.search"
                    @input="applyFilters"
                    type="text"
                    placeholder="بحث بالاسم أو الهاتف..."
                    class="w-full ps-10 pe-4 py-2.5 border border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none text-sm transition-all"
                />
            </div>
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
                                الدور
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                الهاتف
                            </th>
                            <th
                                class="px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500"
                            >
                                البريد
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
                            v-for="member in staffList"
                            :key="member.id"
                            class="border-t border-gray-100 hover:bg-gray-50/50 transition-colors"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-full bg-primary-50 border border-primary-200 flex items-center justify-center text-primary-700 text-sm font-bold shrink-0"
                                    >
                                        {{
                                            member.name
                                                ? member.name.charAt(0)
                                                : "م"
                                        }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{
                                        member.name
                                    }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                    :class="roleBadgeClass(member.role)"
                                >
                                    {{ roleLabel(member.role) }}
                                </span>
                            </td>
                            <td
                                class="px-5 py-4 text-gray-600 font-mono text-xs"
                            >
                                {{ member.phone }}
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ member.email || "—" }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="`/admin/staff/${member.id}/edit`"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 transition-all"
                                    >
                                        تعديل
                                    </Link>
                                    <button
                                        @click="deleteStaff(member)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition-all"
                                    >
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="staffList.length === 0">
                            <td colspan="5" class="px-5 py-16 text-center">
                                <p class="text-gray-500">لا يوجد موظفين</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav
                v-if="paginationLinks.length > 3"
                class="flex items-center justify-center gap-1 px-5 py-4 border-t border-gray-100"
            >
                <template v-for="(link, index) in paginationLinks" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="px-3.5 py-2 text-sm rounded-lg transition-all font-medium"
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
