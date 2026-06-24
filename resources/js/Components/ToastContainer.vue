<script setup>
import ToastNotification from '@/Components/ToastNotification.vue';

defineProps({
    toasts: { type: Array, default: () => [] },
});

const emit = defineEmits(['dismiss']);
</script>

<template>
    <div class="fixed bottom-6 right-3 z-50 flex flex-col-reverse gap-3 sm:right-6">
        <TransitionGroup
            tag="div"
            name="toast"
            class="flex flex-col-reverse gap-3"
        >
            <ToastNotification
                v-for="toast in toasts"
                :key="toast.id"
                :toast="toast"
                @dismiss="(id) => emit('dismiss', id)"
            />
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>
