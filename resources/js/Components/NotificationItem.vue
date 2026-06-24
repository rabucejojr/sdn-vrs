<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    notification: { type: Object, required: true },
    onMarkRead:   { type: Function, required: true },
});

function formatRelative(isoString) {
    const diff = Math.floor((Date.now() - new Date(isoString)) / 1000);
    if (diff < 60)   return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
}

function handleClick() {
    props.onMarkRead(props.notification.id);
    router.visit(props.notification.data.url);
}
</script>

<template>
    <button
        type="button"
        @click="handleClick"
        class="flex w-full items-start gap-3 px-4 py-3 text-left hover:bg-gray-50 focus:outline-none"
        :class="{ 'bg-blue-50': !notification.read_at }"
    >
        <span
            v-if="!notification.read_at"
            class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-blue-500"
        ></span>
        <span v-else class="mt-1.5 h-2 w-2 shrink-0"></span>

        <div class="min-w-0 flex-1">
            <p class="text-sm text-gray-800 leading-snug">{{ notification.data.message }}</p>
            <p v-if="notification.data.remarks" class="mt-1 text-xs italic text-gray-500 leading-snug">
                Remarks: {{ notification.data.remarks }}
            </p>
            <p class="mt-0.5 text-xs text-gray-400">{{ formatRelative(notification.created_at) }}</p>
        </div>
    </button>
</template>
