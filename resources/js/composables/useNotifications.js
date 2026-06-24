import { ref, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useIntervalFn } from '@vueuse/core';

export function useNotifications() {
    const page = usePage();

    const unreadCount = ref(page.props.notifications?.unread_count ?? 0);
    const toasts      = ref([]);
    const lastPollAt  = ref(new Date().toISOString());

    const isAdmin = () => page.props.auth?.user?.role === 'admin';

    async function poll() {
        try {
            const response = await window.axios.get('/notifications/poll', {
                params: { after: lastPollAt.value },
            });

            const { notifications, unread_count } = response.data;

            lastPollAt.value  = new Date().toISOString();
            unreadCount.value = unread_count;

            notifications.forEach((n) => {
                toasts.value.push({
                    id:         n.id,
                    message:    n.data.message,
                    url:        n.data.url,
                    action:     n.data.action ?? null,
                    remarks:    n.data.remarks ?? null,
                    created_at: n.created_at,
                });

                // Cap toast stack at 4
                if (toasts.value.length > 4) {
                    toasts.value.shift();
                }
            });
        } catch {
            // Silently ignore poll errors (e.g. session expired)
        }
    }

    const { pause, resume } = useIntervalFn(poll, 15_000, { immediate: false });

    onMounted(() => {
        // Sync count from page props in case it changed after mount
        unreadCount.value = page.props.notifications?.unread_count ?? 0;
        // Poll for all authenticated users (admins get new-reservation toasts;
        // staff get status-change toasts from Phase 4 notifications)
        resume();
    });

    onUnmounted(() => {
        pause();
    });

    function dismissToast(id) {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    }

    async function markRead(id) {
        try {
            const response = await window.axios.patch(`/notifications/${id}/read`);
            unreadCount.value = response.data.unread_count;
        } catch {
            // ignore
        }
        dismissToast(id);
    }

    async function markAllRead() {
        try {
            await window.axios.patch('/notifications/read-all');
            unreadCount.value = 0;
        } catch {
            // ignore
        }
    }

    return {
        unreadCount,
        toasts,
        dismissToast,
        markRead,
        markAllRead,
    };
}
