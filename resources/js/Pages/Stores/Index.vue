<script setup>
import { Link, router } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { ref, watch } from "vue";

const props = defineProps({
    stores: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
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

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

watch(type, applyFilters);

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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <h1 class="text-2xl font-bold text-primary-900 mb-6">المتاجر</h1>

            <!-- Filter Bar -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-8"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Search -->
                    <div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="ابحث عن متجر..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                        />
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <select
                            v-model="type"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-900 focus:border-primary-900 text-sm"
                        >
                            <option value="">كل الأنواع</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Stores Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link
                    v-for="store in stores.data"
                    :key="store.id"
                    :href="`/stores/${store.slug}`"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group"
                >
                    <!-- Store Cover -->
                    <div class="h-36 bg-gray-100 relative">
                        <img
                            v-if="getStoreCover(store)"
                            :src="getStoreCover(store)"
                            :alt="store.name"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full bg-linear-to-bl from-primary-100 to-primary-50"
                        ></div>

                        <!-- Logo overlay -->
                        <div
                            class="absolute -bottom-6 inset-s-4 w-14 h-14 bg-white rounded-xl shadow-md border border-gray-200 flex items-center justify-center overflow-hidden"
                        >
                            <img
                                v-if="getStoreImage(store)"
                                :src="getStoreImage(store)"
                                :alt="store.name"
                                class="w-full h-full object-cover"
                            />
                            <svg
                                v-else
                                class="w-7 h-7 text-primary-300"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                />
                            </svg>
                        </div>
                    </div>

                    <!-- Store Info -->
                    <div class="p-4 pt-8">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3
                                    class="font-semibold text-gray-900 group-hover:text-primary-900 transition-colors"
                                >
                                    {{ store.name }}
                                </h3>
                                <p
                                    v-if="store.store_type"
                                    class="text-xs text-gray-500 mt-0.5"
                                >
                                    {{ store.store_type.name }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full shrink-0"
                                :class="
                                    store.is_open
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                "
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full"
                                    :class="
                                        store.is_open
                                            ? 'bg-green-500'
                                            : 'bg-red-500'
                                    "
                                ></span>
                                {{ store.is_open ? "مفتوح" : "مغلق" }}
                            </span>
                        </div>

                        <p
                            v-if="store.address"
                            class="text-xs text-gray-500 mt-2 flex items-center gap-1"
                        >
                            <svg
                                class="w-3.5 h-3.5 shrink-0"
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
                            <span class="line-clamp-1">{{
                                store.address
                            }}</span>
                        </p>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div
                v-if="stores.data && stores.data.length === 0"
                class="text-center py-16"
            >
                <svg
                    class="w-16 h-16 text-gray-300 mx-auto mb-4"
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
                <p class="text-gray-500 text-lg">لا توجد متاجر مطابقة للبحث</p>
            </div>

            <!-- Pagination -->
            <nav
                v-if="stores.links && stores.links.length > 3"
                class="mt-8 flex justify-center"
            >
                <div class="flex items-center gap-1">
                    <template v-for="link in stores.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-2 text-sm rounded-lg transition-colors"
                            :class="
                                link.active
                                    ? 'bg-primary-900 text-white'
                                    : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'
                            "
                            v-html="link.label"
                            preserve-scroll
                        />
                        <span
                            v-else
                            class="px-3 py-2 text-sm text-gray-400"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </nav>
        </div>
    </PublicLayout>
</template>
