<script setup>
import { Link, router } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { computed } from "vue";

const props = defineProps({
    notifications: { type: Object, default: () => ({ data: [] }) },
});

const notificationsList = computed(() => props.notifications?.data || []);
const unreadCount = computed(
    () => notificationsList.value.filter((n) => !n.is_read).length,
);

function markAsRead(notification) {
    if (notification.is_read) return;
    router.patch(
        `/notifications/${notification.id}/read`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                if (notification.link) router.visit(notification.link);
            },
        },
    );
}

function handleClick(notification) {
    if (!notification.is_read) {
        markAsRead(notification);
    } else if (notification.link) {
        router.visit(notification.link);
    }
}

function timeAgo(date) {
    const now = new Date();
    const diff = Math.floor((now - new Date(date)) / 1000);
    if (diff < 60) return "الآن";
    if (diff < 3600) return `منذ ${Math.floor(diff / 60)} دقيقة`;
    if (diff < 86400) return `منذ ${Math.floor(diff / 3600)} ساعة`;
    return `منذ ${Math.floor(diff / 86400)} يوم`;
}
</script>

<template>
    <PublicLayout title="الإشعارات">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-primary-900">
                        الإشعارات
                    </h1>
                    <p
                        v-if="unreadCount > 0"
                        class="text-sm text-gray-500 mt-1"
                    >
                        {{ unreadCount }} إشعار غير مقروء
                    </p>
                </div>
            </div>

            <!-- Notifications List -->
            <div v-if="notificationsList.length > 0" class="space-y-3">
                <div
                    v-for="notification in notificationsList"
                    :key="notification.id"
                    @click="handleClick(notification)"
                    class="bg-white rounded-xl border p-4 transition-all cursor-pointer hover:shadow-sm"
                    :class="
                        notification.is_read
                            ? 'border-gray-100'
                            : 'border-primary-200 bg-primary-50/30'
                    "
                >
                    <div class="flex items-start gap-3">
                        <!-- Unread dot -->
                        <div class="mt-1.5 shrink-0">
                            <span
                                v-if="!notification.is_read"
                                class="w-2.5 h-2.5 bg-primary-500 rounded-full block"
                            ></span>
                            <span
                                v-else
                                class="w-2.5 h-2.5 bg-gray-200 rounded-full block"
                            ></span>
                        </div>
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p
                                class="text-sm font-medium text-gray-900"
                                :class="{ 'font-bold': !notification.is_read }"
                            >
                                {{ notification.title }}
                            </p>
                            <p
                                v-if="notification.body"
                                class="text-sm text-gray-500 mt-1 line-clamp-2"
                            >
                                {{ notification.body }}
                            </p>
                            <p class="text-xs text-gray-400 mt-2">
                                {{ timeAgo(notification.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="text-center py-16 bg-white rounded-xl border border-gray-200"
            >
                <div
                    class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"
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
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                        />
                    </svg>
                </div>
                <p class="text-gray-700 font-medium">لا توجد إشعارات</p>
                <p class="text-sm text-gray-400 mt-1">
                    ستظهر هنا عند وجود تحديثات جديدة
                </p>
            </div>
        </div>
    </PublicLayout>
</template>
