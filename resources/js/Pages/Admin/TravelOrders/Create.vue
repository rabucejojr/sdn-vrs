<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PassengerForm from "@/Components/PassengerForm.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { useTravelOrderConsistency } from "@/composables/useTravelOrderForm.js";

const props = defineProps({
  users: { type: Array, required: true },
  vehicles: { type: Array, required: true },
  transportationModes: { type: Object, required: true },
});

const driverName = ref("");

const form = useForm({
  issued_to: "",
  purpose: "",
  destination: "",
  destination_scope: "within_sdn",
  date_start: "",
  date_end: "",
  time_departure: "",
  time_return: "",
  transportation_mode: "",
  vehicle_id: "",
  fund_source: "",
  fund_type: "general", // ui helper: general | project | others
  fund_project_name: "",
  expense_actual: false,
  expense_per_diem: false,
  expense_per_diem_accommodation: false,
  expense_per_diem_subsistence: false,
  expense_per_diem_incidental: false,
  expense_transportation: false,
  expense_transportation_official_vehicle: false,
  expense_transportation_public_conveyance: false,
  expense_transportation_others: false,
  approving_officer: "",
  approving_position: "",
  remarks: "",
  passengers: [],
});
useTravelOrderConsistency(form);

const needsVehicle = computed(() => form.transportation_mode === "government_vehicle");

function buildFundSource() {
  if (form.fund_type === "general") return "General Fund";
  if (form.fund_type === "project")
    return form.fund_project_name
      ? `Project Funds (${form.fund_project_name})`
      : "Project Funds";
  return form.fund_project_name || "Others";
}

function submit() {
  form.fund_source = buildFundSource();

  const extras = driverName.value.trim()
    ? [
        {
          name: driverName.value.trim().toUpperCase(),
          designation: "Driver",
          user_id: null,
        },
      ]
    : [];
  const otherPassengers = form.passengers.filter((p) => p.name.trim() !== "");
  form.passengers = [...extras, ...otherPassengers];

  form.post(route("admin.travel-orders.store"));
}
</script>

<template>
  <Head title="New Travel Order" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">New Travel Order</h2>
    </template>

    <div class="py-10">
      <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <form
          class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
          @submit.prevent="submit"
        >
          <!-- Issued To -->
          <div>
            <InputLabel for="issued_to" value="Issued To" />
            <select
              id="issued_to"
              v-model="form.issued_to"
              required
              class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">— Select personnel —</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
            <InputError :message="form.errors.issued_to" class="mt-1" />
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

          <!-- Destination + Scope -->
          <div>
            <InputLabel for="destination" value="Destination" />
            <input
              id="destination"
              type="text"
              v-model="form.destination"
              required
              class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
            <InputError :message="form.errors.destination" class="mt-1" />
            <div class="mt-2 flex gap-6 text-sm">
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" v-model="form.destination_scope" value="within_sdn" />
                Within Surigao del Norte
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  type="radio"
                  v-model="form.destination_scope"
                  value="outside_sdn"
                />
                Outside Surigao del Norte
              </label>
            </div>
            <p
              v-if="form.destination_scope === 'outside_sdn'"
              class="mt-1 text-xs text-orange-600"
            >
              Outside SDN requires Regional Director approval ({{ $page.props.organization.regional_director }}) —
              will appear on the printed Travel Order.
            </p>
          </div>

          <!-- Date range -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <InputLabel for="date_start" value="Date Start" />
              <input
                id="date_start"
                type="date"
                v-model="form.date_start"
                required
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <InputError :message="form.errors.date_start" class="mt-1" />
            </div>
            <div>
              <InputLabel for="date_end" value="Date End" />
              <input
                id="date_end"
                type="date"
                v-model="form.date_end"
                :min="form.date_start"
                required
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <InputError :message="form.errors.date_end" class="mt-1" />
            </div>
          </div>

          <!-- Times -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <InputLabel for="time_departure" value="Departure Time (optional)" />
              <input
                id="time_departure"
                type="time"
                v-model="form.time_departure"
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div>
              <InputLabel for="time_return" value="Return Time (optional)" />
              <input
                id="time_return"
                type="time"
                v-model="form.time_return"
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
          </div>

          <!-- Transportation Mode -->
          <div>
            <InputLabel for="transportation_mode" value="Mode of Transportation" />
            <select
              id="transportation_mode"
              v-model="form.transportation_mode"
              required
              class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">— Select mode —</option>
              <option v-for="(label, key) in transportationModes" :key="key" :value="key">
                {{ label }}
              </option>
            </select>
            <InputError :message="form.errors.transportation_mode" class="mt-1" />
          </div>

          <!-- Vehicle (conditional) -->
          <div v-if="needsVehicle">
            <InputLabel for="vehicle_id" value="Vehicle" />
            <select
              id="vehicle_id"
              v-model="form.vehicle_id"
              class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">— Select vehicle —</option>
              <option v-for="v in vehicles" :key="v.id" :value="v.id">
                {{ v.name }} ({{ v.plate_number }})
              </option>
            </select>
            <InputError :message="form.errors.vehicle_id" class="mt-1" />
          </div>

          <!-- Fund Source -->
          <div>
            <InputLabel value="Fund Source" />
            <div class="mt-1 flex flex-wrap gap-4 text-sm">
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" v-model="form.fund_type" value="general" /> General
                Fund
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" v-model="form.fund_type" value="project" /> Project
                Funds
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" v-model="form.fund_type" value="others" /> Others
              </label>
            </div>
            <input
              v-if="form.fund_type === 'project' || form.fund_type === 'others'"
              type="text"
              v-model="form.fund_project_name"
              :placeholder="
                form.fund_type === 'project'
                  ? 'Project name (e.g. Roadmapping)'
                  : 'Specify fund source'
              "
              class="mt-2 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
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
                  <input type="checkbox" v-model="form.expense_per_diem" />
                  Per Diem
                </label>
                <div v-if="form.expense_per_diem" class="ml-6 mt-1 space-y-1">
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="form.expense_per_diem_accommodation"
                    />
                    Accommodation
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="form.expense_per_diem_subsistence" />
                    Subsistence
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="form.expense_per_diem_incidental" />
                    Incidental expenses
                  </label>
                </div>
              </div>
              <div>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" v-model="form.expense_transportation" />
                  Transportation
                </label>
                <div v-if="form.expense_transportation" class="ml-6 mt-1 space-y-1">
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="form.expense_transportation_official_vehicle"
                    />
                    Official Vehicle
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      type="checkbox"
                      v-model="form.expense_transportation_public_conveyance"
                    />
                    Public Conveyance (Airplane, Bus, Taxi)
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="form.expense_transportation_others" />
                    Others
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Approving Officer -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <InputLabel for="approving_officer" value="Approving Officer (PSTD)" />
              <input
                id="approving_officer"
                type="text"
                v-model="form.approving_officer"
                required
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="e.g. MERIAM B. BOUQUIA"
              />
              <InputError :message="form.errors.approving_officer" class="mt-1" />
            </div>
            <div>
              <InputLabel for="approving_position" value="Position / Designation" />
              <input
                id="approving_position"
                type="text"
                v-model="form.approving_position"
                required
                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="e.g. OIC, PSTO-SDN"
              />
              <InputError :message="form.errors.approving_position" class="mt-1" />
            </div>
          </div>

          <!-- Remarks -->
          <div>
            <InputLabel for="remarks" value="Remarks (optional)" />
            <textarea
              id="remarks"
              v-model="form.remarks"
              rows="2"
              class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>

          <!-- Driver Name (optional — prepended to passenger list on submit) -->
          <div>
            <InputLabel for="driver_name" value="Driver Name (optional)" />
            <input
              id="driver_name"
              type="text"
              v-model="driverName"
              placeholder="e.g. Juan Dela Cruz"
              class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
            <p class="mt-1 text-xs text-gray-400">
              If set, the driver will be listed first in the personnel table.
            </p>
          </div>

          <!-- Passengers -->
          <div>
            <InputLabel value="Other Personnel / Passengers" />
            <div class="mt-1">
              <PassengerForm v-model="form.passengers" />
            </div>
            <InputError :message="form.errors.passengers" class="mt-1" />
          </div>

          <div class="flex flex-wrap items-center gap-4">
            <PrimaryButton :disabled="form.processing">Save as Draft</PrimaryButton>
            <Link
              :href="route('travel-orders.index')"
              class="text-sm text-gray-500 hover:underline"
              >Cancel</Link
            >
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
