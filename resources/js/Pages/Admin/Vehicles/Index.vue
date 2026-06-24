<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

defineProps({
    vehicles: { type: Array, required: true },
})

function toggleActive(vehicle) {
    router.put(route('admin.vehicles.update', vehicle.id), {
        name:         vehicle.name,
        plate_number: vehicle.plate_number,
        is_active:    !vehicle.is_active,
    }, { preserveScroll: true })
}
</script>

<template>
    <Head title="Vehicles" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Vehicles</h2>
                <Link
                    :href="route('admin.vehicles.create')"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                >
                    Add Vehicle
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Name</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Plate Number</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="v in vehicles" :key="v.id">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ v.name }}</td>
                                <td class="px-6 py-4 font-mono text-gray-700">{{ v.plate_number }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="v.is_active
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-100 text-gray-500'"
                                        class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    >
                                        {{ v.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="route('admin.vehicles.edit', v.id)"
                                        class="text-sm font-medium text-blue-600 hover:underline"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!vehicles.length">
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-400">
                                    No vehicles registered.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
