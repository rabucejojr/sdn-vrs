<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'

const props = defineProps({
    tickets: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
})

const status = ref(props.filters.status ?? '')
const from   = ref(props.filters.from ?? '')
const to     = ref(props.filters.to ?? '')

const applyFilters = useDebounceFn(() => {
    router.get(route('reservations.index'), {
        ...(status.value ? { status: status.value } : {}),
        ...(from.value   ? { from: from.value }     : {}),
        ...(to.value     ? { to: to.value }         : {}),
    }, { preserveState: true, replace: true })
}, 300)

watch([status, from, to], applyFilters)
</script>

<template>
    <Head title="Reservations" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Reservations</h2>
                <div class="flex gap-2">
                    <a v-if="$page.props.auth.user.role === 'admin'"
                       :href="route('admin.reservations.export', { status: status || undefined, from: from || undefined, to: to || undefined })"
                       class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Export</a>
                    <Link :href="route('reservations.create')"
                          class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                        New Reservation
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">

                <!-- ── Filters ── -->
                <div class="flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select v-model="status"
                                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="disapproved">Disapproved</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                        <input type="date" v-model="from"
                               class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                        <input type="date" v-model="to"
                               class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>

                <!-- ── Table ── -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Ticket No.</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Travel Date(s)</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Destination</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Requested By</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Filed</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="t in tickets.data" :key="t.ticket_number" class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ t.ticket_number }}</td>
                                <td class="px-4 py-3 text-gray-900">
                                    {{ t.travel_date_label }}
                                    <span v-if="t.is_multi_day"
                                          class="ml-1 rounded-full bg-indigo-100 px-1.5 py-0.5 text-xs font-semibold text-indigo-700">
                                        multi-day
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ t.destination }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ t.requester_name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ t.date_filed }}</td>
                                <td class="px-4 py-3"><StatusBadge :status="t.status" /></td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="route('reservations.show', t.ticket_number)"
                                          class="text-sm font-medium text-blue-600 hover:underline">
                                        View
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!tickets.data?.length">
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-400">
                                    No reservations found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="tickets.links?.length > 3"
                         class="flex items-center justify-between border-t border-gray-100 px-4 py-3 text-sm">
                        <span class="text-gray-500">
                            Showing {{ tickets.from }}–{{ tickets.to }} of {{ tickets.total }}
                        </span>
                        <div class="flex gap-1">
                            <template v-for="link in tickets.links" :key="link.label">
                                <Link v-if="link.url"
                                      :href="link.url"
                                      :class="[
                                          'rounded px-3 py-1',
                                          link.active
                                              ? 'bg-blue-600 text-white'
                                              : 'text-gray-600 hover:bg-gray-100',
                                      ]"
                                      v-html="link.label"
                                />
                                <span v-else
                                      class="rounded px-3 py-1 text-gray-300"
                                      v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
