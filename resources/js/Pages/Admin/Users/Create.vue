<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name:     '',
    position: '',
    email:    '',
    role:     'staff',
})

function submit() {
    form.post(route('admin.users.store'))
}
</script>

<template>
    <Head title="Add User" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Add User</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">

                <div class="mb-4 flex items-start gap-3 rounded-md border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <p>The user will receive an email with a link to set their own password. No password is required from you.</p>
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
                        <PrimaryButton :disabled="form.processing">Create User &amp; Send Setup Email</PrimaryButton>
                        <Link
                            :href="route('admin.users.index')"
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
