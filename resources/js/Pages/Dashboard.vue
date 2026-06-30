<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    calendar: {
        type: Object,
        required: true,
    },
    upcoming: {
        type: Array,
        default: () => [],
    },
    userStats: {
        type: Object,
        default: null,
    },
})

// ── Stat cards ──────────────────────────────────────────────────────────────

const statCards = computed(() => [
    {
        label: 'Pending Reservations',
        value: props.stats.pending,
        bg: 'bg-yellow-50',
        border: 'border-yellow-300',
        text: 'text-yellow-800',
        badge: 'bg-yellow-100 text-yellow-700',
    },
    {
        label: 'Approved This Month',
        value: props.stats.approvedThisMonth,
        bg: 'bg-green-50',
        border: 'border-green-300',
        text: 'text-green-800',
        badge: 'bg-green-100 text-green-700',
    },
    {
        label: 'Completed This Month',
        value: props.stats.completedThisMonth,
        bg: 'bg-blue-50',
        border: 'border-blue-300',
        text: 'text-blue-800',
        badge: 'bg-blue-100 text-blue-700',
    },
])

// ── Calendar ─────────────────────────────────────────────────────────────────

const calYear  = ref(props.calendar.year)
const calMonth = ref(props.calendar.month)   // 1-based

watch(() => props.calendar, (cal) => {
    calYear.value  = cal.year
    calMonth.value = cal.month
})

const MONTH_NAMES = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
]
const DAY_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

// bookedDates is now Record<string, string> — date ISO → requester name
const bookedDates = computed(() => props.calendar.bookedDates ?? {})

const calendarTitle = computed(() => `${MONTH_NAMES[calMonth.value - 1]} ${calYear.value}`)

// Build the grid: leading blanks + day cells + trailing blanks
const calendarCells = computed(() => {
    const firstDay = new Date(calYear.value, calMonth.value - 1, 1).getDay() // 0=Sun
    const daysInMonth = new Date(calYear.value, calMonth.value, 0).getDate()

    const cells = []

    for (let i = 0; i < firstDay; i++) {
        cells.push({ blank: true, key: `b-${i}` })
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const iso = `${calYear.value}-${String(calMonth.value).padStart(2, '0')}-${String(d).padStart(2, '0')}`
        const today = new Date()
        const isToday =
            today.getFullYear() === calYear.value &&
            today.getMonth() + 1 === calMonth.value &&
            today.getDate() === d

        const requester = bookedDates.value[iso] ?? null

        cells.push({
            blank:     false,
            key:       iso,
            day:       d,
            iso,
            booked:    requester !== null,
            requester,
            isToday,
        })
    }

    return cells
})

function navigate(delta) {
    let m = calMonth.value + delta
    let y = calYear.value
    if (m < 1)  { m = 12; y-- }
    if (m > 12) { m = 1;  y++ }
    router.get(route('dashboard'), { year: y, month: m }, { preserveState: true, replace: true })
}

function goToToday() {
    const now = new Date()
    router.get(route('dashboard'), { year: now.getFullYear(), month: now.getMonth() + 1 }, { preserveState: true, replace: true })
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- ── Reservation stat cards ── -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div
                        v-for="card in statCards"
                        :key="card.label"
                        :class="['rounded-lg border p-6 shadow-sm', card.bg, card.border]"
                    >
                        <p :class="['text-sm font-medium', card.text]">{{ card.label }}</p>
                        <p :class="['mt-2 text-4xl font-bold', card.text]">{{ card.value }}</p>
                        <span :class="['mt-3 inline-block rounded-full px-2 py-0.5 text-xs font-semibold', card.badge]">
                            {{ card.label.toLowerCase().includes('month') ? 'this month' : 'total' }}
                        </span>
                    </div>
                </div>

                <!-- ── User stats (admin only) ── -->
                <div
                    v-if="$page.props.auth.user.role === 'admin' && userStats"
                    class="grid grid-cols-2 gap-4 sm:grid-cols-4"
                >
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-medium text-gray-500">Total Users</p>
                        <p class="mt-1 text-3xl font-bold text-gray-800">{{ userStats.total }}</p>
                    </div>
                    <div class="rounded-lg border border-green-200 bg-green-50 p-4 shadow-sm">
                        <p class="text-xs font-medium text-green-700">Active</p>
                        <p class="mt-1 text-3xl font-bold text-green-800">{{ userStats.active }}</p>
                    </div>
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 shadow-sm">
                        <p class="text-xs font-medium text-red-700">Inactive</p>
                        <p class="mt-1 text-3xl font-bold text-red-800">{{ userStats.inactive }}</p>
                    </div>
                    <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 shadow-sm">
                        <p class="text-xs font-medium text-indigo-700">Admins</p>
                        <p class="mt-1 text-3xl font-bold text-indigo-800">{{ userStats.admins }}</p>
                    </div>
                </div>

                <!-- ── Monthly calendar ── -->
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <!-- Calendar header -->
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <button
                            type="button"
                            class="rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                            @click="navigate(-1)"
                        >
                            &#8592;
                        </button>
                        <div class="flex items-center gap-3">
                            <h3 class="text-base font-semibold text-gray-800">{{ calendarTitle }}</h3>
                            <button
                                v-if="calYear !== new Date().getFullYear() || calMonth !== new Date().getMonth() + 1"
                                type="button"
                                class="rounded border border-gray-300 px-2 py-0.5 text-xs font-medium text-gray-600 hover:bg-gray-100"
                                @click="goToToday"
                            >
                                Today
                            </button>
                        </div>
                        <button
                            type="button"
                            class="rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                            @click="navigate(1)"
                        >
                            &#8594;
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                    <div class="min-w-[280px]">
                    <!-- Day-of-week labels -->
                    <div class="grid grid-cols-7 border-b border-gray-100 bg-gray-50 text-center">
                        <div
                            v-for="label in DAY_LABELS"
                            :key="label"
                            class="py-2 text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            {{ label }}
                        </div>
                    </div>

                    <!-- Day cells -->
                    <div class="grid grid-cols-7">
                        <div
                            v-for="cell in calendarCells"
                            :key="cell.key"
                            class="relative min-h-[64px] border-b border-r border-gray-100 p-1 last:border-r-0"
                        >
                            <template v-if="!cell.blank">
                                <!-- Day number -->
                                <span
                                    :class="[
                                        'flex h-7 w-7 items-center justify-center rounded-full text-sm font-medium',
                                        cell.isToday
                                            ? 'bg-blue-600 text-white'
                                            : 'text-gray-700',
                                    ]"
                                >
                                    {{ cell.day }}
                                </span>

                                <!-- Booked indicator with requester name -->
                                <template v-if="cell.booked">
                                    <span class="mt-1 block truncate rounded bg-green-100 px-1 py-0.5 text-center text-xs font-semibold text-green-700">
                                        Booked
                                    </span>
                                    <span v-if="cell.requester" class="mt-0.5 block truncate text-center text-xs text-gray-500">
                                        {{ cell.requester }}
                                    </span>
                                </template>
                            </template>
                        </div>
                    </div>

                    </div>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center gap-4 border-t border-gray-100 px-6 py-3 text-xs text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block h-3 w-3 rounded-full bg-blue-600"></span>
                            Today
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block h-3 w-3 rounded bg-green-100 ring-1 ring-green-300"></span>
                            Approved reservation
                        </span>
                    </div>
                </div>

                <!-- ── Upcoming Trips (next 7 days) ── -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-800">Upcoming Trips — Next 7 Days</h3>
                    </div>

                    <template v-if="upcoming.length">
                        <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Ticket No.</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Travel Date(s)</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Destination</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Requested By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="trip in upcoming" :key="trip.ticket_number" class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-mono text-xs text-gray-700">
                                        <Link
                                            :href="route('reservations.show', trip.ticket_number)"
                                            class="text-blue-600 hover:underline"
                                        >
                                            {{ trip.ticket_number }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-3 text-gray-900">
                                        {{ trip.travel_date_label }}
                                        <span v-if="trip.is_multi_day"
                                              class="ml-1 rounded-full bg-indigo-100 px-1.5 py-0.5 text-xs font-semibold text-indigo-700">
                                            multi-day
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-700">{{ trip.destination }}</td>
                                    <td class="px-6 py-3 text-gray-500">{{ trip.requester_name }}</td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </template>

                    <p v-else class="px-6 py-8 text-center text-sm text-gray-400">
                        No approved trips scheduled in the next 7 days.
                    </p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
