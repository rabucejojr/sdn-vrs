<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import NotificationItem from '@/Components/NotificationItem.vue';

const props = defineProps({
    onMarkRead:    { type: Function, required: true },
    onMarkAllRead: { type: Function, required: true },
});

const emit = defineEmits(['close']);

const notifications = ref([]);
const loading       = ref(true);

async function fetchNotifications() {
    try {
        const response = await window.axios.get('/notifications');
        notifications.value = response.data.data;
    } catch {
        // ignore
    } finally {
        loading.value = false;
    }
}

async function handleMarkAllRead() {
    await props.onMarkAllRead();
    notifications.value = notifications.value.map((n) => ({
        ...n,
        read_at: new Date().toISOString(),
    }));
}

async function handleMarkRead(id) {
    await props.onMarkRead(id);
    const n = notifications.value.find((n) => n.id === id);
    if (n) n.read_at = new Date().toISOString();
    emit('close');
}

function handleOutsideClick(e) {
    if (!e.target.closest('[data-notification-dropdown]')) {
        emit('close');
    }
}

onMounted(() => {
    fetchNotifications();
    setTimeout(() => document.addEventListener('click', handleOutsideClick), 0);
});

onUnmounted(() => {
    document.removeEventListener('click', handleOutsideClick);
});
</script>

<template>
    <div
        data-notification-dropdown
        class="absolute right-0 top-full z-50 mt-2 w-[calc(100vw-1rem)] origin-top-right rounded-md border border-gray-200 bg-white shadow-lg sm:w-80"
    >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2.5">
            <span class="text-sm font-semibold text-gray-700">Notifications</span>
            <button
                type="button"
                @click="handleMarkAllRead"
                class="text-xs text-blue-600 hover:text-blue-800"
            >
                Mark all as read
            </button>
        </div>

        <!-- List -->
        <div class="max-h-80 divide-y divide-gray-100 overflow-y-auto">
            <div v-if="loading" class="px-4 py-6 text-center text-sm text-gray-400">
                Loading…
            </div>
            <div v-else-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-400">
                No notifications yet.
            </div>
            <NotificationItem
                v-else
                v-for="notification in notifications"
                :key="notification.id"
                :notification="notification"
                :on-mark-read="handleMarkRead"
            />
        </div>
    </div>
</template>
