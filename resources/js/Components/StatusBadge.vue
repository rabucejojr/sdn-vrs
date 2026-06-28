<script setup>
defineProps({
    status: { type: String, required: true },
})

const CLASSES = {
    pending:     'bg-yellow-100 text-yellow-800',
    approved:    'bg-green-100 text-green-800',
    disapproved: 'bg-red-100 text-red-800',
    completed:   'bg-blue-100 text-blue-800',
    cancelled:   'bg-gray-100 text-gray-500',
    draft:       'bg-slate-100 text-slate-600',
    issued:      'bg-emerald-100 text-emerald-800',
}
</script>

<template>
    <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize', CLASSES[status] ?? 'bg-gray-100 text-gray-500']">
        {{ status }}
    </span>
</template>
