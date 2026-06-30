<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    ticket: { type: Object, required: true },
    logs:   { type: Array,  default: () => [] },
})

const actionForm = useForm({ remarks: '' })
const cancelForm = useForm({})
const showAllLogs = ref(false)
const displayedLogs = computed(() => showAllLogs.value ? props.logs : props.logs.slice(0, 3))

function approve() {
    actionForm.patch(route('admin.reservations.approve', props.ticket.ticket_number))
}
function disapprove() {
    actionForm.patch(route('admin.reservations.disapprove', props.ticket.ticket_number))
}
function complete() {
    actionForm.patch(route('admin.reservations.complete', props.ticket.ticket_number))
}
function cancel() {
    if (confirm('Cancel this reservation?')) {
        cancelForm.delete(route('reservations.cancel', props.ticket.ticket_number))
    }
}

function openPrint() {
    window.open(route('reservations.print', props.ticket.ticket_number), '_blank')
}
function downloadPdf() {
    window.location.href = route('reservations.pdf', props.ticket.ticket_number)
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
</script>

<template>
    <Head :title="`Reservation — ${ticket.ticket_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ ticket.ticket_number }}
                </h2>
                <StatusBadge :status="ticket.status" />
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl space-y-8 px-4 sm:px-6 lg:px-8">

                <!-- ── Actions ── -->
                <div class="flex flex-wrap items-center justify-between gap-3">

                    <!-- Action buttons (left) -->
                    <div class="flex flex-wrap gap-2">
                        <Link v-if="$page.props.auth.user.role === 'admin'
                                  ? ['pending', 'approved'].includes(ticket.status)
                                  : ticket.status === 'pending'"
                              :href="route('reservations.edit', ticket.ticket_number)"
                              class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                            Edit
                        </Link>
                        <button v-if="['pending', 'approved'].includes(ticket.status)" type="button"
                                class="inline-flex items-center gap-1.5 rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50"
                                @click="cancel">
                            Cancel Reservation
                        </button>
                        <p v-if="cancelForm.errors.status" class="w-full text-sm text-red-600">{{ cancelForm.errors.status }}</p>
                        <Link v-if="$page.props.auth.user.role === 'admin' && ticket.status === 'approved' && !ticket.travel_order_number"
                              :href="route('admin.travel-orders.generate-form', ticket.ticket_number)"
                              class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700">
                            Generate Travel Order
                        </Link>
                        <Link v-if="ticket.travel_order_number"
                              :href="route('travel-orders.show', ticket.travel_order_number)"
                              class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300 bg-white px-4 py-2 text-sm font-medium text-emerald-700 shadow-sm hover:bg-emerald-50">
                            View Travel Order
                        </Link>
                    </div>

                    <!-- Print / PDF (right) -->
                    <div v-if="['approved', 'completed'].includes(ticket.status)" class="flex gap-2">
                        <button type="button"
                                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                @click="openPrint">
                            Print
                        </button>
                        <button type="button"
                                class="inline-flex items-center gap-1.5 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                                @click="downloadPdf">
                            Download PDF
                        </button>
                    </div>

                </div>

                <!-- ── Ticket details ── -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-800">Reservation Details</h3>
                    </div>
                    <dl class="divide-y divide-gray-100 text-sm">
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Ticket No.</dt>
                            <dd class="col-span-2 font-mono text-gray-900">{{ ticket.ticket_number }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Date Filed</dt>
                            <dd class="col-span-2 text-gray-900">{{ ticket.date_filed }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Vehicle</dt>
                            <dd class="col-span-2 text-gray-900">
                                {{ ticket.vehicle?.name }} &mdash; {{ ticket.vehicle?.plate_number }}
                            </dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Travel Date(s)</dt>
                            <dd class="col-span-2 flex items-center gap-2 text-gray-900">
                                {{ ticket.travel_date_label }}
                                <span v-if="ticket.is_multi_day"
                                      class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                    Multi-day
                                </span>
                            </dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Departure / Return</dt>
                            <dd class="col-span-2 text-gray-900">
                                {{ ticket.time_departure ?? '—' }} &ndash; {{ ticket.time_return ?? '—' }}
                            </dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Destination</dt>
                            <dd class="col-span-2 text-gray-900">{{ ticket.destination }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Purpose</dt>
                            <dd class="col-span-2 text-gray-900">{{ ticket.purpose }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Requested By</dt>
                            <dd class="col-span-2 text-gray-900">{{ ticket.requester?.name }}</dd>
                        </div>
                        <div v-if="ticket.approved_by" class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Approved By</dt>
                            <dd class="col-span-2 text-gray-900">{{ ticket.approver?.name }}</dd>
                        </div>
                        <div v-if="ticket.remarks" class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Remarks</dt>
                            <dd class="col-span-2 text-gray-900 italic">{{ ticket.remarks }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- ── Passengers ── -->
                <div v-if="ticket.passengers?.length"
                     class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-800">Passengers</h3>
                    </div>
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">#</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Name</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Designation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(p, i) in ticket.passengers" :key="p.id">
                                <td class="px-6 py-3 text-gray-500">{{ i + 1 }}</td>
                                <td class="px-6 py-3 text-gray-900">{{ p.name }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ p.designation ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- ── Admin actions ── -->
                <div v-if="$page.props.auth.user.role === 'admin' && (ticket.status === 'pending' || ticket.status === 'approved')"
                     class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-base font-semibold text-gray-800">Admin Actions</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Remarks (optional)</label>
                        <textarea v-model="actionForm.remarks" rows="2"
                                  class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button v-if="ticket.status === 'pending'" type="button"
                                class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                                :disabled="actionForm.processing" @click="approve">
                            Approve
                        </button>
                        <button v-if="ticket.status === 'pending'" type="button"
                                class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                                :disabled="actionForm.processing" @click="disapprove">
                            Disapprove
                        </button>
                        <button v-if="ticket.status === 'approved'" type="button"
                                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                                :disabled="actionForm.processing" @click="complete">
                            Mark Completed
                        </button>
                    </div>
                    <p v-if="Object.keys(actionForm.errors).length" class="mt-3 text-sm text-red-600">
                        {{ Object.values(actionForm.errors)[0] }}
                    </p>
                </div>

                <!-- ── Activity log ── -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-800">Activity Log</h3>
                    </div>
                    <div v-if="logs.length" class="px-6 py-4">
                        <ol class="relative border-l border-gray-200">
                            <li v-for="(log, i) in displayedLogs" :key="log.id" class="mb-6 ml-4 last:mb-0">
                                <span class="absolute -left-1.5 mt-1 h-3 w-3 rounded-full border-2 border-white"
                                      :class="i === displayedLogs.length - 1 ? 'bg-blue-500' : 'bg-gray-400'"></span>
                                <div v-if="!['edited', 'printed', 'pdf_downloaded'].includes(log.to_status)"
                                     class="flex flex-wrap items-center gap-2">
                                    <StatusBadge v-if="log.from_status" :status="log.from_status" />
                                    <span v-if="log.from_status" class="text-xs text-gray-400">&rarr;</span>
                                    <StatusBadge :status="log.to_status" />
                                </div>
                                <div v-else class="text-xs font-medium text-gray-500">
                                    <span v-if="log.to_status === 'edited'">&#9998; Edited</span>
                                    <span v-else-if="log.to_status === 'printed'">&#128438; Printed</span>
                                    <span v-else-if="log.to_status === 'pdf_downloaded'">&#8659; PDF Downloaded</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-700">
                                    <span class="font-medium">{{ log.actor?.name ?? 'System' }}</span>
                                    <span v-if="!log.from_status && log.to_status !== 'edited' && log.to_status !== 'printed' && log.to_status !== 'pdf_downloaded'"> filed this reservation.</span>
                                    <span v-else-if="log.to_status === 'edited'"> edited this reservation.</span>
                                    <span v-else-if="log.to_status === 'printed'"> printed this reservation.</span>
                                    <span v-else-if="log.to_status === 'pdf_downloaded'"> downloaded the PDF.</span>
                                    <span v-else> changed status from <em>{{ log.from_status }}</em> to <em>{{ log.to_status }}</em>.</span>
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
