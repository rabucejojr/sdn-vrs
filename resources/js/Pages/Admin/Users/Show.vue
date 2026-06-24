<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    user:        { type: Object, required: true },
    tickets:     { type: Object, required: true },
    ticketStats: { type: Object, required: true },
})

const page   = usePage()
const isSelf = computed(() => page.props.auth.user.id === props.user.id)

const toggleForm = useForm({})

function toggleActive() {
    const action = props.user.is_active ? 'deactivate' : 'activate'
    if (!confirm(`Are you sure you want to ${action} ${props.user.name}'s account?`)) return
    toggleForm.patch(route('admin.users.toggle-active', props.user.id))
}

function formatDate(iso) {
    if (!iso) return '—'
    return new Date(iso).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}

function formatLastActivity(unixTs) {
    if (!unixTs) return 'Never'
    return new Date(unixTs * 1000).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}
</script>

<template>
    <Head :title="`User — ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ user.name }}</h2>
                <span
                    :class="user.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                    class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                >
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl space-y-8 px-4 sm:px-6 lg:px-8">

                <!-- Profile card -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-800">Profile Information</h3>
                    </div>
                    <dl class="divide-y divide-gray-100 text-sm">
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Full Name</dt>
                            <dd class="col-span-2 text-gray-900">{{ user.name }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Email</dt>
                            <dd class="col-span-2 text-gray-900">{{ user.email }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Role</dt>
                            <dd class="col-span-2">
                                <span
                                    :class="user.role === 'admin'
                                        ? 'bg-indigo-100 text-indigo-700'
                                        : 'bg-gray-100 text-gray-600'"
                                    class="rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                >
                                    {{ user.role }}
                                </span>
                            </dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Email Verified</dt>
                            <dd class="col-span-2 text-gray-900">
                                <span v-if="user.email_verified_at" class="text-green-600">
                                    ✓ Verified on {{ formatDate(user.email_verified_at) }}
                                </span>
                                <span v-else class="text-gray-400">✗ Not verified</span>
                            </dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Member Since</dt>
                            <dd class="col-span-2 text-gray-900">{{ formatDate(user.created_at) }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
                            <dt class="font-medium text-gray-500">Last Active</dt>
                            <dd class="col-span-2 text-gray-900">{{ formatLastActivity(user.last_activity) }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Reservation stats -->
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    <div class="rounded-lg border border-gray-200 bg-white p-4 text-center shadow-sm">
                        <p class="text-xs font-medium text-gray-500">Total</p>
                        <p class="mt-1 text-2xl font-bold text-gray-800">{{ ticketStats.total }}</p>
                    </div>
                    <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-center shadow-sm">
                        <p class="text-xs font-medium text-yellow-700">Pending</p>
                        <p class="mt-1 text-2xl font-bold text-yellow-800">{{ ticketStats.pending }}</p>
                    </div>
                    <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-center shadow-sm">
                        <p class="text-xs font-medium text-green-700">Approved</p>
                        <p class="mt-1 text-2xl font-bold text-green-800">{{ ticketStats.approved }}</p>
                    </div>
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-center shadow-sm">
                        <p class="text-xs font-medium text-blue-700">Completed</p>
                        <p class="mt-1 text-2xl font-bold text-blue-800">{{ ticketStats.completed }}</p>
                    </div>
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-center shadow-sm">
                        <p class="text-xs font-medium text-red-700">Disapproved</p>
                        <p class="mt-1 text-2xl font-bold text-red-800">{{ ticketStats.disapproved }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-center shadow-sm">
                        <p class="text-xs font-medium text-gray-500">Cancelled</p>
                        <p class="mt-1 text-2xl font-bold text-gray-800">{{ ticketStats.cancelled }}</p>
                    </div>
                </div>

                <!-- Reservation history -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-800">Reservation History</h3>
                    </div>

                    <template v-if="tickets.data?.length">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500">Ticket No.</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500">Travel Date(s)</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500">Destination</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500">Filed</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="t in tickets.data" :key="t.ticket_number" class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                            <Link
                                                :href="route('reservations.show', t.ticket_number)"
                                                class="text-blue-600 hover:underline"
                                            >
                                                {{ t.ticket_number }}
                                            </Link>
                                        </td>
                                        <td class="px-4 py-3 text-gray-900">
                                            {{ t.travel_date_label }}
                                            <span
                                                v-if="t.is_multi_day"
                                                class="ml-1 rounded-full bg-indigo-100 px-1.5 py-0.5 text-xs font-semibold text-indigo-700"
                                            >
                                                multi-day
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">{{ t.destination }}</td>
                                        <td class="px-4 py-3"><StatusBadge :status="t.status" /></td>
                                        <td class="px-4 py-3 text-gray-500">{{ t.date_filed }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="tickets.links?.length > 3"
                            class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 px-4 py-3 text-sm"
                        >
                            <span class="text-gray-500">
                                Showing {{ tickets.from }}–{{ tickets.to }} of {{ tickets.total }}
                            </span>
                            <div class="flex flex-wrap gap-1">
                                <template v-for="link in tickets.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="[
                                            'rounded px-3 py-1',
                                            link.active
                                                ? 'bg-blue-600 text-white'
                                                : 'text-gray-600 hover:bg-gray-100',
                                        ]"
                                        v-html="link.label"
                                    />
                                    <span
                                        v-else
                                        class="rounded px-3 py-1 text-gray-300"
                                        v-html="link.label"
                                    />
                                </template>
                            </div>
                        </div>
                    </template>

                    <p v-else class="px-6 py-8 text-center text-sm text-gray-400">
                        No reservations filed yet.
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        :href="route('admin.users.edit', user.id)"
                        class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    >
                        Edit Profile
                    </Link>
                    <button
                        v-if="!isSelf"
                        type="button"
                        :class="user.is_active
                            ? 'text-red-600 ring-red-200 hover:bg-red-50'
                            : 'text-green-600 ring-green-200 hover:bg-green-50'"
                        class="rounded-md bg-white px-4 py-2 text-sm font-medium shadow-sm ring-1 ring-inset disabled:opacity-50"
                        :disabled="toggleForm.processing"
                        @click="toggleActive"
                    >
                        {{ user.is_active ? 'Deactivate Account' : 'Activate Account' }}
                    </button>
                    <Link
                        :href="route('admin.users.index')"
                        class="text-sm text-gray-500 hover:underline"
                    >
                        ← Back to Users
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
