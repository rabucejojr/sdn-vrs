<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'

const props = defineProps({
    users:   { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    stats:   { type: Object, required: true },
})

const search = ref(props.filters.search ?? '')
const sort   = ref(props.filters.sort   ?? 'created_at_desc')

const applyFilters = useDebounceFn(() => {
    router.get(route('admin.users.index'), {
        ...(search.value ? { search: search.value } : {}),
        ...(sort.value !== 'created_at_desc' ? { sort: sort.value } : {}),
    }, { preserveState: true, replace: true })
}, 300)

watch([search, sort], applyFilters)

function formatLastActivity(unixTs) {
    if (!unixTs) return '—'
    return new Date(unixTs * 1000).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">User Management</h2>
                <Link
                    :href="route('admin.users.create')"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                >
                    Add User
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- Stat cards -->
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-medium text-gray-500">Total Users</p>
                        <p class="mt-1 text-3xl font-bold text-gray-800">{{ stats.total }}</p>
                    </div>
                    <div class="rounded-lg border border-green-200 bg-green-50 p-4 shadow-sm">
                        <p class="text-xs font-medium text-green-700">Active</p>
                        <p class="mt-1 text-3xl font-bold text-green-800">{{ stats.active }}</p>
                    </div>
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 shadow-sm">
                        <p class="text-xs font-medium text-red-700">Inactive</p>
                        <p class="mt-1 text-3xl font-bold text-red-800">{{ stats.inactive }}</p>
                    </div>
                    <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 shadow-sm">
                        <p class="text-xs font-medium text-indigo-700">Admins</p>
                        <p class="mt-1 text-3xl font-bold text-indigo-800">{{ stats.admins }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm">
                        <p class="text-xs font-medium text-gray-500">Staff</p>
                        <p class="mt-1 text-3xl font-bold text-gray-800">{{ stats.staff }}</p>
                    </div>
                </div>

                <!-- Filter bar -->
                <div class="flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="min-w-[180px] flex-1">
                        <label class="mb-1 block text-xs font-medium text-gray-500">Search</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Name or email…"
                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Sort</label>
                        <select
                            v-model="sort"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="created_at_desc">Newest first</option>
                            <option value="created_at_asc">Oldest first</option>
                            <option value="name_asc">Name A–Z</option>
                            <option value="name_desc">Name Z–A</option>
                            <option value="role_asc">Role</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Name / Email</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Role</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Verified</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Reservations</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Last Active</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ u.name }}</div>
                                        <div class="text-xs text-gray-500">{{ u.email }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="u.role === 'admin'
                                                ? 'bg-indigo-100 text-indigo-700'
                                                : 'bg-gray-100 text-gray-600'"
                                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                        >
                                            {{ u.role }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span v-if="u.email_verified_at" class="font-semibold text-green-600">✓</span>
                                        <span v-else class="text-gray-400">✗</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ u.reservation_count }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ formatLastActivity(u.last_activity) }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="u.is_active
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700'"
                                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        >
                                            {{ u.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="space-x-3">
                                            <Link
                                                :href="route('admin.users.show', u.id)"
                                                class="text-sm font-medium text-blue-600 hover:underline"
                                            >
                                                View
                                            </Link>
                                            <Link
                                                :href="route('admin.users.edit', u.id)"
                                                class="text-sm font-medium text-gray-600 hover:underline"
                                            >
                                                Edit
                                            </Link>
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!users.data?.length">
                                    <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-400">
                                        No users found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="users.links?.length > 3"
                        class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 px-4 py-3 text-sm"
                    >
                        <span class="text-gray-500">
                            Showing {{ users.from }}–{{ users.to }} of {{ users.total }}
                        </span>
                        <div class="flex flex-wrap gap-1">
                            <template v-for="link in users.links" :key="link.label">
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
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
