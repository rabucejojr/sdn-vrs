<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'

const props = defineProps({
    orders:  { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
})

const status = ref(props.filters.status ?? '')
const from   = ref(props.filters.from ?? '')
const to     = ref(props.filters.to ?? '')

const applyFilters = useDebounceFn(() => {
    router.get(route('travel-orders.index'), {
        status: status.value || undefined,
        from:   from.value   || undefined,
        to:     to.value     || undefined,
    }, { preserveState: true, replace: true })
}, 300)

watch([status, from, to], applyFilters)
</script>

<template>
    <Head title="Travel Orders" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Travel Orders</h2>
                <Link v-if="$page.props.auth.user.role === 'admin'"
                      :href="route('admin.travel-orders.create')"
                      class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    + New Travel Order
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Filters -->
                <div class="mb-4 flex flex-wrap gap-3">
                    <select v-model="status"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="issued">Issued</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <input type="date" v-model="from" placeholder="From"
                           class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    <input type="date" v-model="to" placeholder="To"
                           class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>

                <!-- Table -->
                <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">TO Number</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Issued To</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Travel Date</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-if="orders.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">No travel orders found.</td>
                            </tr>
                            <tr v-for="order in orders.data" :key="order.travel_order_number"
                                class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono font-medium text-gray-900">
                                    {{ order.travel_order_number }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ order.issued_to_name }}</td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ order.destination }}
                                    <span v-if="order.destination_scope === 'outside_sdn'"
                                          class="ml-1 rounded bg-orange-50 px-1.5 py-0.5 text-xs text-orange-600">Outside SDN</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ order.travel_date_label }}</td>
                                <td class="px-4 py-3"><StatusBadge :status="order.status" /></td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="route('travel-orders.show', order.travel_order_number)"
                                          class="text-blue-600 hover:underline text-sm">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="orders.links" class="mt-4 flex justify-center gap-1">
                    <template v-for="link in orders.links" :key="link.label">
                        <Link v-if="link.url"
                              :href="link.url"
                              class="rounded px-3 py-1 text-sm"
                              :class="link.active ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'"
                              v-html="link.label" />
                        <span v-else class="rounded px-3 py-1 text-sm text-gray-400" v-html="link.label" />
                    </template>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
