<script setup>
import { onMounted, ref } from 'vue'

const tokens = ref([])
const tokenName = ref('')
const plainToken = ref('')
const error = ref('')
const busy = ref(false)

async function loadTokens() {
    const { data } = await window.axios.get('/api/tokens')
    tokens.value = data
}

async function createToken() {
    busy.value = true
    error.value = ''
    try {
        const { data } = await window.axios.post('/api/tokens/create', { token_name: tokenName.value })
        plainToken.value = data.token
        tokenName.value = ''
        await loadTokens()
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Unable to create token.'
    } finally {
        busy.value = false
    }
}

async function revokeToken(id) {
    if (!window.confirm('Revoke this API token?')) return
    await window.axios.delete(`/api/tokens/${id}`)
    await loadTokens()
}

onMounted(loadTokens)
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">API Tokens</h2>
            <p class="mt-1 text-sm text-gray-600">Read-only tokens expire automatically after {{ $page.props.api.token_expiration_days }} days.</p>
        </header>
        <form class="mt-5 flex gap-2" @submit.prevent="createToken">
            <input v-model="tokenName" required maxlength="100" placeholder="Token name"
                   class="block w-full rounded-md border-gray-300 text-sm shadow-sm" />
            <button :disabled="busy" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">
                Create
            </button>
        </form>
        <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
        <div v-if="plainToken" class="mt-4 rounded-md border border-amber-300 bg-amber-50 p-3">
            <p class="text-xs font-semibold text-amber-800">Copy this token now; it will not be shown again.</p>
            <code class="mt-2 block break-all text-xs text-gray-800">{{ plainToken }}</code>
        </div>
        <div class="mt-4 divide-y rounded-md border">
            <div v-for="token in tokens" :key="token.id" class="flex items-center justify-between gap-3 p-3 text-sm">
                <div>
                    <p class="font-medium text-gray-800">{{ token.name }}</p>
                    <p class="text-xs text-gray-500">Expires {{ token.expires_at ? new Date(token.expires_at).toLocaleDateString() : 'never' }}</p>
                </div>
                <button type="button" @click="revokeToken(token.id)" class="text-xs text-red-600 hover:underline">Revoke</button>
            </div>
            <p v-if="!tokens.length" class="p-3 text-sm text-gray-500">No API tokens.</p>
        </div>
    </section>
</template>
