import { watch } from 'vue'

export function useTravelOrderConsistency(form) {
    watch(() => form.transportation_mode, (mode) => {
        if (mode !== 'government_vehicle') form.vehicle_id = ''
    })

    watch(() => form.expense_per_diem, (enabled) => {
        if (enabled) return
        form.expense_per_diem_accommodation = false
        form.expense_per_diem_subsistence = false
        form.expense_per_diem_incidental = false
    })

    watch(() => form.expense_transportation, (enabled) => {
        if (enabled) return
        form.expense_transportation_official_vehicle = false
        form.expense_transportation_public_conveyance = false
        form.expense_transportation_others = false
    })
}
