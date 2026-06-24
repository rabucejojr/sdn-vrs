<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    vehicle: { type: Object, required: true },
})

const form = useForm({
    name:         props.vehicle.name,
    plate_number: props.vehicle.plate_number,
    is_active:    props.vehicle.is_active,
})

function submit() {
    form.put(route('admin.vehicles.update', props.vehicle.id))
}
</script>

<template>
    <Head title="Edit Vehicle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Vehicle — {{ vehicle.name }}
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
                <form
                    class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    @submit.prevent="submit"
                >
                    <div>
                        <InputLabel for="name" value="Vehicle Name" />
                        <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="plate_number" value="Plate Number" />
                        <TextInput id="plate_number" v-model="form.plate_number" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.plate_number" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-3">
                        <input id="is_active" type="checkbox" v-model="form.is_active"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                        <InputLabel for="is_active" value="Active" class="mb-0" />
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <PrimaryButton :disabled="form.processing">Update</PrimaryButton>
                        <Link :href="route('admin.vehicles.index')" class="text-sm text-gray-500 hover:underline">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
