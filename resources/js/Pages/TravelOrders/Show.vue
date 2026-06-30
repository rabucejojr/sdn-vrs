<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    order: { type: Object, required: true },
    logs:  { type: Array,  default: () => [] },
})
const showAllLogs = ref(false)
const displayedLogs = computed(() => showAllLogs.value ? props.logs : props.logs.slice(0, 3))

// Cancel form
const cancelForm = useForm({})
function cancelOrder() {
    if (! confirm('Cancel this Travel Order?')) return
    cancelForm.patch(route('admin.travel-orders.cancel', props.order.travel_order_number))
}

// Issue form
const issueForm = useForm({})
function issueOrder() {
    if (! confirm('Issue this Travel Order? Passengers will be notified.')) return
    issueForm.patch(route('admin.travel-orders.issue', props.order.travel_order_number))
}

function formatDate(value) {
    if (!value) return '—'
    try {
        const date = new Date(value)
        if (isNaN(date.getTime())) return String(value)
        return new Intl.DateTimeFormat('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: 'numeric', minute: '2-digit', hour12: true,
            timeZone: 'Asia/Manila',
        }).format(date)
    } catch {
        return String(value)
    }
}

const ACTION_LABELS = {
    created:                'Filed',
    updated:                'Updated',
    issued:                 'Issued',
    cancelled:              'Cancelled',
    generated_from_ticket:  'Generated from Trip Ticket',
    printed:                'Printed',
    pdf_downloaded:         'PDF Downloaded',
}
</script>

<template>
    <Head :title="`Travel Order — ${order.travel_order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        {{ order.travel_order_number }}
                    </h2>
                    <p v-if="order.trip_ticket_number" class="mt-0.5 text-sm text-gray-500">
                        Generated from
                        <Link :href="route('reservations.show', order.trip_ticket_number)"
                              class="text-blue-600 hover:underline">
                            {{ order.trip_ticket_number }}
                        </Link>
                    </p>
                </div>
                <StatusBadge :status="order.status" />
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

                <div v-if="order.source_is_newer && $page.props.auth.user.role === 'admin'"
                     class="rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    The source reservation changed after this Travel Order was created. This order remains an independent snapshot; review it before issuing.
                </div>

                <!-- Action bar -->
                <div class="flex flex-wrap items-center justify-between gap-3">

                    <!-- Admin actions (left) -->
                    <div v-if="$page.props.auth.user.role === 'admin'"
                         class="flex flex-wrap gap-2">
                        <Link v-if="order.status === 'draft'"
                              :href="route('admin.travel-orders.edit', order.travel_order_number)"
                              class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                            Edit
                        </Link>
                        <button v-if="order.status === 'draft'"
                                @click="issueOrder"
                                :disabled="issueForm.processing"
                                class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50">
                            Issue
                        </button>
                        <button v-if="order.status !== 'cancelled'"
                                @click="cancelOrder"
                                :disabled="cancelForm.processing"
                                class="inline-flex items-center gap-1.5 rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50 disabled:opacity-50">
                            Cancel
                        </button>
                    </div>
                    <div v-else></div>

                    <!-- Print / PDF (right) -->
                    <div v-if="order.status === 'issued' || ($page.props.auth.user.role === 'admin' && order.status === 'draft')" class="flex gap-2">
                        <a :href="route('travel-orders.print', order.travel_order_number)"
                           target="_blank"
                           class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                            Print
                        </a>
                        <a :href="route('travel-orders.pdf', order.travel_order_number)"
                           class="inline-flex items-center gap-1.5 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                            Download PDF
                        </a>
                    </div>

                </div>

                <!-- Detail card -->
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm divide-y divide-gray-100">

                    <!-- Travel info -->
                    <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="font-medium text-gray-500">Issued To</div>
                            <div class="mt-1 text-gray-900">{{ order.issued_to?.name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="font-medium text-gray-500">Destination</div>
                            <div class="mt-1 text-gray-900">
                                {{ order.destination }}
                                <span v-if="order.is_outside_sdn"
                                      class="ml-1.5 rounded bg-orange-50 px-1.5 py-0.5 text-xs text-orange-600">Outside SDN</span>
                            </div>
                        </div>
                        <div>
                            <div class="font-medium text-gray-500">Date of Travel</div>
                            <div class="mt-1 text-gray-900">{{ order.travel_date_label }}</div>
                        </div>
                        <div>
                            <div class="font-medium text-gray-500">Transportation</div>
                            <div class="mt-1 text-gray-900">{{ order.transportation_label }}</div>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="font-medium text-gray-500">Purpose</div>
                            <div class="mt-1 text-gray-900 whitespace-pre-line">{{ order.purpose }}</div>
                        </div>
                        <div v-if="order.fund_source">
                            <div class="font-medium text-gray-500">Fund Source</div>
                            <div class="mt-1 text-gray-900">{{ order.fund_source }}</div>
                        </div>
                        <div v-if="order.issued_at_formatted">
                            <div class="font-medium text-gray-500">Issued On</div>
                            <div class="mt-1 text-gray-900">{{ order.issued_at_formatted }}</div>
                        </div>
                        <div>
                            <div class="font-medium text-gray-500">Approving Officer</div>
                            <div class="mt-1 text-gray-900">{{ order.approving_officer }}</div>
                            <div class="text-xs text-gray-500">{{ order.approving_position }}</div>
                        </div>
                        <div v-if="order.is_outside_sdn">
                            <div class="font-medium text-gray-500">Regional Director</div>
                            <div class="mt-1 text-gray-900">{{ order.regional_director }}</div>
                            <div class="text-xs text-gray-500">{{ order.regional_director_position }}</div>
                        </div>
                    </div>

                    <!-- Passengers -->
                    <div class="px-6 py-4">
                        <div class="font-medium text-gray-500 text-sm mb-2">Personnel</div>
                        <div class="overflow-x-auto">
                        <table class="min-w-full text-sm divide-y divide-gray-100">
                            <thead>
                                <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                                    <th class="pb-2 pr-4">Name</th>
                                    <th class="pb-2">Designation / Position</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="p in order.passengers" :key="p.id">
                                    <td class="py-1.5 pr-4 font-medium text-gray-900">{{ p.name }}</td>
                                    <td class="py-1.5 text-gray-600">{{ p.designation ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>

                </div>

                <!-- Activity Log -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-800">Activity Log</h3>
                    </div>
                    <div v-if="logs.length" class="px-6 py-4">
                        <ol class="relative border-l border-gray-200">
                            <li v-for="(log, i) in displayedLogs" :key="log.id" class="mb-6 ml-4 last:mb-0">
                                <span class="absolute -left-1.5 mt-1 h-3 w-3 rounded-full border-2 border-white"
                                      :class="i === displayedLogs.length - 1 ? 'bg-blue-500' : 'bg-gray-400'"></span>
                                <div v-if="['created', 'issued', 'cancelled'].includes(log.action)"
                                     class="flex flex-wrap items-center gap-2">
                                    <StatusBadge :status="log.action === 'created' ? 'draft' : log.action" />
                                </div>
                                <div v-else class="text-xs font-medium text-gray-500">
                                    <span v-if="log.action === 'updated'">&#9998; Updated</span>
                                    <span v-else-if="log.action === 'generated_from_ticket'">&#128196; Generated from Trip Ticket</span>
                                    <span v-else-if="log.action === 'printed'">&#128438; Printed</span>
                                    <span v-else-if="log.action === 'pdf_downloaded'">&#8659; PDF Downloaded</span>
                                    <span v-else>{{ ACTION_LABELS[log.action] ?? log.action }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-700">
                                    <span class="font-medium">{{ log.actor?.name ?? 'System' }}</span>
                                    <span v-if="log.action === 'created'"> filed this Travel Order.</span>
                                    <span v-else-if="log.action === 'updated'"> updated this Travel Order.</span>
                                    <span v-else-if="log.action === 'issued'"> issued this Travel Order.</span>
                                    <span v-else-if="log.action === 'cancelled'"> cancelled this Travel Order.</span>
                                    <span v-else-if="log.action === 'generated_from_ticket'"> generated this Travel Order from a Trip Ticket.</span>
                                    <span v-else-if="log.action === 'printed'"> printed this Travel Order.</span>
                                    <span v-else-if="log.action === 'pdf_downloaded'"> downloaded the PDF.</span>
                                </p>
                                <p v-if="log.remarks" class="mt-0.5 text-xs italic text-gray-500">"{{ log.remarks }}"</p>
                                <time class="mt-0.5 block text-xs text-gray-400">{{ formatDate(log.created_at) }}</time>
                            </li>
                        </ol>
                        <button v-if="logs.length > 3" type="button" @click="showAllLogs = !showAllLogs"
                                class="mt-3 text-sm font-medium text-blue-600 hover:underline">
                            {{ showAllLogs ? 'Show recent only' : `Show all ${logs.length} events` }}
                        </button>
                    </div>
                    <p v-else class="px-6 py-4 text-sm text-gray-400">No activity recorded yet.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
