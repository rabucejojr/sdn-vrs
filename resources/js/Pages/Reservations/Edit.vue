<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ConflictAlert from '@/Components/ConflictAlert.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PassengerForm from '@/Components/PassengerForm.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import axios from 'axios'
import { ref, watch } from 'vue'

const props = defineProps({
    ticket:  { type: Object, required: true },
    vehicle: { type: Object, required: true },
})

const form = useForm({
    purpose:        props.ticket.purpose,
    date_start:     props.ticket.date_start,
    date_end:       props.ticket.date_end,
    time_departure: props.ticket.time_departure ?? '',
    time_return:    props.ticket.time_return ?? '',
    destination:    props.ticket.destination,
    driver_name:    props.ticket.driver_name ?? '',
    passengers:     props.ticket.passengers?.length
                        ? props.ticket.passengers.map(p => ({ name: p.name, designation: p.designation ?? '' }))
                        : [{ name: '', designation: '' }],
})

const conflict = ref(null)

const checkConflict = useDebounceFn(async () => {
    if (!form.date_start) { conflict.value = null; return }
    const { data } = await axios.get(route('api.reservations.check-conflict'), {
        params: {
            date_start: form.date_start,
            date_end:   form.date_end || form.date_start,
            exclude:    props.ticket.ticket_number,
        },
    })
    conflict.value = data
}, 300)

watch([() => form.date_start, () => form.date_end], checkConflict)

function submit() {
    form.put(route('reservations.update', props.ticket.ticket_number))
}
</script>

<template>
    <Head :title="`Edit — ${ticket.ticket_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Reservation — {{ ticket.ticket_number }}
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <form class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                      @submit.prevent="submit">

                    <!-- Vehicle (read-only) -->
                    <div class="rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-700 ring-1 ring-gray-200">
                        <span class="font-medium">Vehicle:</span>
                        {{ vehicle.name }} &mdash; {{ vehicle.plate_number }}
                    </div>

                    <!-- Purpose -->
                    <div>
                        <InputLabel for="purpose" value="Purpose of Travel" />
                        <textarea
                            id="purpose"
                            v-model="form.purpose"
                            rows="3"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                        <InputError :message="form.errors.purpose" class="mt-1" />
                    </div>

                    <!-- Date range -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="date_start" value="Date Start" />
                            <input id="date_start" type="date" v-model="form.date_start" required
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <InputError :message="form.errors.date_start" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="date_end" value="Date End" />
                            <input id="date_end" type="date" v-model="form.date_end" :min="form.date_start" required
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <InputError :message="form.errors.date_end" class="mt-1" />
                        </div>
                    </div>

                    <ConflictAlert :conflict="conflict" />

                    <!-- Times -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="time_departure" value="Departure Time (optional)" />
                            <input id="time_departure" type="time" v-model="form.time_departure"
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                            <InputLabel for="time_return" value="Return Time (optional)" />
                            <input id="time_return" type="time" v-model="form.time_return"
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                    </div>

                    <!-- Destination -->
                    <div>
                        <InputLabel for="destination" value="Destination" />
                        <input id="destination" type="text" v-model="form.destination" required
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <InputError :message="form.errors.destination" class="mt-1" />
                    </div>

                    <!-- Driver Name -->
                    <div>
                        <InputLabel for="driver_name" value="Driver Name (optional)" />
                        <input id="driver_name" type="text" v-model="form.driver_name"
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="e.g. Juan D. Cruz" />
                        <InputError :message="form.errors.driver_name" class="mt-1" />
                    </div>

                    <!-- Passengers -->
                    <div>
                        <InputLabel value="Passengers" />
                        <div class="mt-1">
                            <PassengerForm v-model="form.passengers" />
                        </div>
                        <InputError :message="form.errors.passengers" class="mt-1" />
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <PrimaryButton :disabled="form.processing || conflict?.conflict === true">Save Changes</PrimaryButton>
                        <Link :href="route('reservations.show', ticket.ticket_number)"
                              class="text-sm text-gray-500 hover:underline">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
