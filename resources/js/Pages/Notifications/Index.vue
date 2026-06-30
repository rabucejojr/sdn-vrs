<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

defineProps({ notifications: { type: Object, required: true } })

function markRead(notification) {
    router.patch(route('notifications.read', notification.id), {}, {
        preserveScroll: true,
        onSuccess: () => notification.read_at = new Date().toISOString(),
    })
}

function openNotification(notification) {
    router.patch(route('notifications.read', notification.id), {}, {
        onSuccess: () => router.visit(notification.data.url),
    })
}
</script>

<template>
    <Head title="Notifications" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Notifications</h2></template>
        <div class="py-10">
            <div class="mx-auto max-w-3xl space-y-3 px-4 sm:px-6">
                <div v-for="notification in notifications.data" :key="notification.id"
                     class="rounded-lg border bg-white p-4 shadow-sm"
                     :class="notification.read_at ? 'border-gray-200' : 'border-blue-300'">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-gray-800">{{ notification.data.message }}</p>
                            <p v-if="notification.data.remarks" class="mt-1 text-xs text-gray-500">{{ notification.data.remarks }}</p>
                            <button v-if="notification.data.url" type="button"
                                    class="mt-2 inline-block text-sm text-blue-600 hover:underline"
                                    @click="openNotification(notification)">View details</button>
                        </div>
                        <button v-if="!notification.read_at" type="button" @click="markRead(notification)"
                                class="shrink-0 text-xs text-blue-600 hover:underline">Mark read</button>
                    </div>
                </div>
                <p v-if="!notifications.data.length" class="rounded-lg bg-white p-8 text-center text-sm text-gray-500">
                    No notifications yet.
                </p>
                <div class="flex justify-center gap-1">
                    <template v-for="link in notifications.links" :key="link.label">
                        <Link v-if="link.url" :href="link.url" v-html="link.label"
                              class="rounded border px-3 py-1 text-sm"
                              :class="link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-600'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
