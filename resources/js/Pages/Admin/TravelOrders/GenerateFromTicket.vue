<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PassengerForm from '@/Components/PassengerForm.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    ticket:              { type: Object, required: true },  // { ticket_number, purpose, destination }
    prefill:             { type: Object, required: true },  // { purpose, date_start, date_end, passengers }
    users:               { type: Array,  required: true },
    vehicles:            { type: Array,  required: true },
    transportationModes: { type: Object, required: true },
})

const form = useForm({
    issued_to:                      '',
    purpose:                        props.prefill.purpose,
    destination:                    '',
    destination_scope:              'within_sdn',
    date_start:                     props.prefill.date_start,
    date_end:                       props.prefill.date_end,
    time_departure:                 '',
    time_return:                    '',
    transportation_mode:            'government_vehicle',
    vehicle_id:                     props.vehicles[0]?.id ?? '',
    fund_source:                    '',
    fund_type:                      'general',
    fund_project_name:              '',
    expense_actual:                 false,
    expense_per_diem:               false,
    expense_per_diem_accommodation: false,
    expense_per_diem_subsistence:   false,
    expense_per_diem_incidental:    false,
    expense_transportation:                      true,
    expense_transportation_official_vehicle:     true,
    expense_transportation_public_conveyance:    false,
    expense_transportation_others:               false,
    approving_officer:              '',
    approving_position:             '',
    remarks:                        '',
    passengers:                     props.prefill.passengers ?? [],
})

const needsVehicle = computed(() => form.transportation_mode === 'government_vehicle')

function buildFundSource() {
    if (form.fund_type === 'general')  return 'General Fund'
    if (form.fund_type === 'project')  return form.fund_project_name ? `Project Funds (${form.fund_project_name})` : 'Project Funds'
    return form.fund_project_name || 'Others'
}

function submit() {
    form.fund_source = buildFundSource()
    form.post(route('admin.travel-orders.generate', props.ticket.ticket_number))
}
</script>

<template>
    <Head :title="`Generate TO from ${ticket.ticket_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Generate Travel Order</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Pre-filled from
                    <Link :href="route('reservations.show', ticket.ticket_number)"
                          class="text-blue-600 hover:underline">{{ ticket.ticket_number }}</Link>
                    — complete the remaining fields below.
                </p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

                <!-- Pre-fill notice -->
                <div class="mb-6 rounded-md bg-blue-50 px-4 py-3 text-sm text-blue-700 ring-1 ring-blue-200">
                    <strong>Pre-filled:</strong> Purpose, dates, and personnel from {{ ticket.ticket_number }}.
                    Destination, fund source, expenses, and approving officer must be filled in manually.
                </div>

                <form class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                      @submit.prevent="submit">

                    <!-- Issued To -->
                    <div>
                        <InputLabel for="issued_to" value="Issued To" />
                        <select id="issued_to" v-model="form.issued_to" required
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">— Select personnel —</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <InputError :message="form.errors.issued_to" class="mt-1" />
                    </div>

                    <!-- Purpose (pre-filled, editable) -->
                    <div>
                        <InputLabel for="purpose" value="Purpose of Travel" />
                        <textarea id="purpose" v-model="form.purpose" rows="3" required
                                  class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <InputError :message="form.errors.purpose" class="mt-1" />
                    </div>

                    <!-- Destination + Scope -->
                    <div>
                        <InputLabel for="destination" value="Destination" />
                        <input id="destination" type="text" v-model="form.destination" required
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <InputError :message="form.errors.destination" class="mt-1" />
                        <div class="mt-2 flex gap-6 text-sm">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.destination_scope" value="within_sdn" />
                                Within Surigao del Norte
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.destination_scope" value="outside_sdn" />
                                Outside Surigao del Norte
                            </label>
                        </div>
                        <p v-if="form.destination_scope === 'outside_sdn'"
                           class="mt-1 text-xs text-orange-600">
                            Outside SDN requires Regional Director approval (Engr. Noel M. Ajoc).
                        </p>
                    </div>

                    <!-- Date range (pre-filled, editable) -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="date_start" value="Date Start" />
                            <input id="date_start" type="date" v-model="form.date_start" required
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                            <InputLabel for="date_end" value="Date End" />
                            <input id="date_end" type="date" v-model="form.date_end" :min="form.date_start" required
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                    </div>

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

                    <!-- Transportation Mode -->
                    <div>
                        <InputLabel for="transportation_mode" value="Mode of Transportation" />
                        <select id="transportation_mode" v-model="form.transportation_mode" required
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">— Select mode —</option>
                            <option v-for="(label, key) in transportationModes" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <InputError :message="form.errors.transportation_mode" class="mt-1" />
                    </div>

                    <!-- Vehicle (conditional) -->
                    <div v-if="needsVehicle">
                        <InputLabel for="vehicle_id" value="Vehicle" />
                        <select id="vehicle_id" v-model="form.vehicle_id"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">— Select vehicle —</option>
                            <option v-for="v in vehicles" :key="v.id" :value="v.id">{{ v.name }} ({{ v.plate_number }})</option>
                        </select>
                        <InputError :message="form.errors.vehicle_id" class="mt-1" />
                    </div>

                    <!-- Fund Source -->
                    <div>
                        <InputLabel value="Fund Source" />
                        <div class="mt-1 flex flex-wrap gap-4 text-sm">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.fund_type" value="general" /> General Fund
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.fund_type" value="project" /> Project Funds
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.fund_type" value="others" /> Others
                            </label>
                        </div>
                        <input v-if="form.fund_type === 'project' || form.fund_type === 'others'"
                               type="text" v-model="form.fund_project_name"
                               :placeholder="form.fund_type === 'project' ? 'Project name (e.g. Roadmapping)' : 'Specify fund source'"
                               class="mt-2 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>

                    <!-- Expense Types -->
                    <div>
                        <InputLabel value="Travel Expenses" />
                        <div class="mt-2 space-y-2 text-sm">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="form.expense_actual" />
                                Actual (Accommodation, Meals/Food, Incidental)
                            </label>
                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="form.expense_per_diem" /> Per Diem
                                </label>
                                <div v-if="form.expense_per_diem" class="ml-6 mt-1 space-y-1">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="form.expense_per_diem_accommodation" /> Accommodation
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="form.expense_per_diem_subsistence" /> Subsistence
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="form.expense_per_diem_incidental" /> Incidental expenses
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="form.expense_transportation" /> Transportation
                                </label>
                                <div v-if="form.expense_transportation" class="ml-6 mt-1 space-y-1">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="form.expense_transportation_official_vehicle" /> Official Vehicle
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="form.expense_transportation_public_conveyance" /> Public Conveyance (Airplane, Bus, Taxi)
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="form.expense_transportation_others" /> Others
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approving Officer -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="approving_officer" value="Approving Officer (PSTD)" />
                            <input id="approving_officer" type="text" v-model="form.approving_officer" required
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="e.g. MERIAM B. BOUQUIA" />
                            <InputError :message="form.errors.approving_officer" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="approving_position" value="Position / Designation" />
                            <input id="approving_position" type="text" v-model="form.approving_position" required
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="e.g. OIC, PSTO-SDN" />
                            <InputError :message="form.errors.approving_position" class="mt-1" />
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div>
                        <InputLabel for="remarks" value="Remarks (optional)" />
                        <textarea id="remarks" v-model="form.remarks" rows="2"
                                  class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>

                    <!-- Passengers (pre-filled: driver first, then trip ticket passengers) -->
                    <div>
                        <InputLabel value="Personnel / Passengers" />
                        <p class="mt-0.5 text-xs text-gray-400">Pre-filled from the reservation. Edit as needed.</p>
                        <div class="mt-1">
                            <PassengerForm v-model="form.passengers" />
                        </div>
                        <InputError :message="form.errors.passengers" class="mt-1" />
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <PrimaryButton :disabled="form.processing">Create Travel Order</PrimaryButton>
                        <Link :href="route('reservations.show', ticket.ticket_number)"
                              class="text-sm text-gray-500 hover:underline">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
