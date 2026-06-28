<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { BellIcon, XIcon } from 'lucide-vue-next';

const props = defineProps({
    toast: { type: Object, required: true },
});

const emit = defineEmits(['dismiss']);

let timer = null;

function dismiss() {
    emit('dismiss', props.toast.id);
}

const buttonLabel = computed(() => {
    const a = props.toast.action
    if (a === 'deactivated') return 'Go to Login →'
    if (a === 'email_verified' || a === 'password_changed' || a === 'account_created' || a === 'role_changed') return 'Go to Dashboard →'
    if (a === 'travel_order_issued') return 'View Travel Order →'
    return 'View →'
})

function navigate() {
    dismiss();
    router.visit(props.toast.url);
}

onMounted(() => {
    timer = setTimeout(dismiss, 5000);
});

onUnmounted(() => {
    clearTimeout(timer);
});
</script>

<template>
    <div
        class="flex w-[calc(100vw-1.5rem)] items-start gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-lg sm:w-80"
        role="alert"
    >
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
            <BellIcon class="h-4 w-4" />
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-sm text-gray-800 leading-snug">{{ toast.message }}</p>
            <p v-if="toast.remarks" class="mt-0.5 text-xs italic text-gray-500 leading-snug">
                Remarks: {{ toast.remarks }}
            </p>
            <button
                type="button"
                @click="navigate"
                class="mt-1 text-xs font-medium text-blue-600 hover:text-blue-800"
            >
                {{ buttonLabel }}
            </button>
        </div>

        <button
            type="button"
            @click="dismiss"
            class="shrink-0 text-gray-400 hover:text-gray-600"
            aria-label="Dismiss"
        >
            <XIcon class="h-4 w-4" />
        </button>
    </div>
</template>
