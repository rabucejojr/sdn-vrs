<script setup>
const passengers = defineModel({ type: Array, required: true })

function addRow() {
    passengers.value.push({ name: '', designation: '' })
}

function removeRow(index) {
    passengers.value.splice(index, 1)
}
</script>

<template>
    <div class="space-y-3">
        <div
            v-for="(p, i) in passengers"
            :key="i"
            class="flex items-start gap-2"
        >
            <div class="flex flex-1 flex-col gap-2 sm:flex-row sm:gap-3">
                <div class="flex-1">
                    <input
                        v-model="p.name"
                        type="text"
                        placeholder="Full name"
                        class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
                <div class="flex-1">
                    <input
                        v-model="p.designation"
                        type="text"
                        placeholder="Designation (optional)"
                        class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
            </div>
            <button
                type="button"
                class="mt-0.5 shrink-0 rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-500"
                @click="removeRow(i)"
                title="Remove"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <button
            type="button"
            class="text-sm font-medium text-blue-600 hover:underline"
            @click="addRow"
        >
            + Add passenger
        </button>
    </div>
</template>
