<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { BellIcon } from 'lucide-vue-next';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';

const props = defineProps({
    unreadCount: { type: Number, default: 0 },
    onMarkRead:  { type: Function, required: true },
    onMarkAllRead: { type: Function, required: true },
});

const open = ref(false);

function toggle() {
    open.value = !open.value;
}

function close() {
    open.value = false;
}
</script>

<template>
    <div class="relative">
        <button
            type="button"
            @click="toggle"
            class="relative inline-flex items-center rounded-md p-2.5 text-gray-500 hover:text-gray-700 focus:outline-none"
            aria-label="Notifications"
        >
            <BellIcon class="h-5 w-5" />
            <span
                v-if="unreadCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold leading-none text-white"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <NotificationDropdown
            v-if="open"
            :on-mark-read="onMarkRead"
            :on-mark-all-read="onMarkAllRead"
            @close="close"
        />
    </div>
</template>
