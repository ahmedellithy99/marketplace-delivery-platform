<script setup>
import { Link, router } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { ref, computed, watch } from "vue";

const props = defineProps({
    stores: { type: Object, default: () => ({ data: [], links: [] }) },
    filters: { type: Object, default: () => ({}) },
    storeTypes: { type: Array, default: () => [] },
});

const search = ref(props.filters.search || "");
const type = ref(props.filters.type || "");

let searchTimeout = null;

function applyFilters() {
    const params = {};
    if (search.value) params.search = search.value;
    if (type.value) params.type = type.value;
    router.get("/stores", params, {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetFilters() {
    search.value = "";
    type.value = "";
    router.get("/stores", {}, { preserveState: false });
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});
watch(type, applyFilters);

const hasActiveFilters = computed(() => search.value || type.value);

function getStoreImage(store) {
    return (
        store.media?.find((m) => m.collection_name === "logo")?.original_url ||
        null
    );
}

function getStoreCover(store) {
    return (
        store.media?.find((m) => m.collection_name === "cover")?.original_url ||
        null
    );
}
</script>

<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
            <!-- Page Header -->
            <div class="flex items-end justify-between mb-6">
                <div>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-gray-900"
                    >
                        المتاجر
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ stores.total || 0 }} متجر
                    </p>
                </div>
                <button
                    v-if="hasActiveFilters"
                    @click="resetFilters"
                    class="text-sm text-red-500 hover:text-red-600 font-medium transition-colors"
                >
                    مسح الفلاتر
                </button>
            </div>

            <!-- Search Bar -->
            <div class="relative mb-6">
                <svg
                    class="absolute top-1/2 -translate-y-1/2 start-4 w-5 h-5 text-gray-400 pointer-events-none"
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
                    v-model="search"
                    type="text"
                    placeholder="ابحث عن متجر..."
                    class="w-full ps-12 pe-4 py-3.5 bg-white border border-gray-200 rounded-2xl text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none shadow-sm transition-all"
                />
            </div>

            <!-- Type Chips -->
            <div
                class="flex gap-2 overflow-x-auto pb-3 scrollbar-hide -mx-4 px-4 sm:mx-0 sm:px-0 mb-6"
            >
                <button
                    @click="
                        type = '';
                        applyFilters();
                    "
                    class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all"
                    :class="
                        !type
                            ? 'bg-primary-900 text-white shadow-sm'
                            : 'bg-white border border-gray-200 text-gray-600 hover:border-gray-300'
                    "
                >
                    الكل
                </button>
                <button
                    v-for="st in storeTypes"
                    :key="st.id"
                    @click="
                        type = String(st.id);
                        applyFilters();
                    "
                    class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all"
                    :class="
                        type === String(st.id)
                            ? 'bg-primary-900 text-white shadow-sm'
                            : 'bg-white border border-gray-200 text-gray-600 hover:border-gray-300'
                    "
                >
                    {{ st.name }}
                </button>
            </div>

            <!-- Stores Grid -->
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5"
            >
                <Link
                    v-for="store in stores.data"
                    :key="store.id"
                    :href="`/stores/${store.slug}`"
                    class="group bg-white rounded-2xl border border-gray-100 overflow-hidden transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5"
                >
                    <!-- Cover -->
                    <div
                        class="h-32 sm:h-36 bg-gradient-to-br from-primary-50 to-primary-100 relative overflow-hidden"
                    >
                        <img
                            v-if="getStoreCover(store)"
                            :src="getStoreCover(store)"
                            :alt="store.name"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
                        <!-- Status badge -->
                        <span
                            class="absolute top-3 end-3 inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full backdrop-blur-sm"
                            :class="
                                store.is_open
                                    ? 'bg-green-500/90 text-white'
                                    : 'bg-gray-800/70 text-white'
                            "
                        >
                            <span
                                class="w-1.5 h-1.5 rounded-full"
                                :class="
                                    store.is_open
                                        ? 'bg-white animate-pulse'
                                        : 'bg-gray-400'
                                "
                            ></span>
                            {{ store.is_open ? "مفتوح" : "مغلق" }}
                        </span>
                    </div>

                    <!-- Info -->
                    <div class="p-4 relative">
                        <!-- Logo (overlapping cover) -->
                        <div
                            class="absolute -top-7 start-4 w-14 h-14 bg-white rounded-xl shadow-md border-2 border-white overflow-hidden"
                        >
                            <img
                                v-if="getStoreImage(store)"
                                :src="getStoreImage(store)"
                                :alt="store.name"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                class="w-full h-full bg-primary-50 flex items-center justify-center"
                            >
                                <svg
                                    class="w-6 h-6 text-primary-300"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                            </div>
                        </div>

                        <div class="pt-5">
                            <h3
                                class="font-bold text-gray-900 text-base group-hover:text-primary-900 transition-colors truncate"
                            >
                                {{ store.name }}
                            </h3>
                            <p
                                v-if="store.store_type"
                                class="text-xs text-gray-400 mt-0.5"
                            >
                                {{ store.store_type.name }}
                            </p>

                            <p
                                v-if="store.address"
                                class="text-xs text-gray-500 mt-2 flex items-center gap-1.5 line-clamp-1"
                            >
                                <svg
                                    class="w-3.5 h-3.5 shrink-0 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                                {{ store.address }}
                            </p>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div
                v-if="stores.data && stores.data.length === 0"
                class="text-center py-20"
            >
                <div
                    class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4"
                >
                    <svg
                        class="w-10 h-10 text-gray-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                        />
                    </svg>
                </div>
                <p class="text-gray-700 font-semibold text-lg">
                    لا توجد متاجر مطابقة
                </p>
                <p class="text-sm text-gray-400 mt-1">جرب تغيير كلمات البحث</p>
                <button
                    @click="resetFilters"
                    class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-primary-900 hover:text-primary-700 transition-colors"
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
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        />
                    </svg>
                    إعادة تعيين
                </button>
            </div>

            <!-- Pagination -->
            <nav
                v-if="stores.links && stores.links.length > 3"
                class="mt-10 flex justify-center"
            >
                <div class="flex items-center gap-1">
                    <template v-for="link in stores.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3.5 py-2 text-sm rounded-lg transition-all font-medium"
                            :class="
                                link.active
                                    ? 'bg-primary-900 text-white shadow-sm'
                                    : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'
                            "
                            v-html="link.label"
                            preserve-scroll
                        />
                        <span
                            v-else
                            class="px-3.5 py-2 text-sm text-gray-300"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </nav>
        </div>
    </PublicLayout>
</template>
