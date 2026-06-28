<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    user: { type: Object, required: true },
})

const page   = usePage()
const isSelf = computed(() => page.props.auth.user.id === props.user.id)

const form = useForm({
    name:     props.user.name,
    position: props.user.position ?? '',
    email:    props.user.email,
    role:     props.user.role,
})

function submit() {
    form.put(route('admin.users.update', props.user.id))
}
</script>

<template>
    <Head :title="`Edit — ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit User — {{ user.name }}
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-xl space-y-4 px-4 sm:px-6 lg:px-8">

                <!-- Self-edit warning -->
                <div
                    v-if="isSelf"
                    class="flex items-start gap-3 rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800"
                >
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                            clip-rule="evenodd" />
                    </svg>
                    <p>You are editing your own account. Changing your role will immediately restrict your access.</p>
                </div>

                <form
                    class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    @submit.prevent="submit"
                >

                    <div>
                        <InputLabel for="name" value="Full Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            class="mt-1 block w-full"
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="position" value="Position / Designation (optional)" />
                        <TextInput
                            id="position"
                            v-model="form.position"
                            class="mt-1 block w-full"
                            placeholder="e.g. Science Research Specialist II"
                        />
                        <InputError :message="form.errors.position" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email Address" />
                        <TextInput
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.email" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Role" />
                        <div class="mt-2 flex gap-6">
                            <label class="flex cursor-pointer items-center gap-2">
                                <input
                                    type="radio"
                                    v-model="form.role"
                                    value="staff"
                                    class="border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                />
                                <span class="text-sm text-gray-700">Staff</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2">
                                <input
                                    type="radio"
                                    v-model="form.role"
                                    value="admin"
                                    class="border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                <span class="text-sm text-gray-700">Admin</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.role" class="mt-1" />
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <PrimaryButton :disabled="form.processing">Save Changes</PrimaryButton>
                        <Link
                            :href="route('admin.users.show', user.id)"
                            class="text-sm text-gray-500 hover:underline"
                        >
                            Cancel
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
