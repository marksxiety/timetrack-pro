<template>

    <Head title="System Settings" />
    <div class="flex flex-col gap-6">
        <Breadcrumbs :items="[
            { label: 'Home', route: 'main' },
            { label: 'Settings', active: true },
        ]" />

        <div>
            <h1 class="text-lg font-bold">System Settings</h1>
            <p class="text-xs opacity-50 mt-0.5">Configure default shift codes, overtime rules, and system preferences.
            </p>
        </div>

        <form @submit.prevent="submitForm">
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-8 w-full max-w-7xl">

                <div class="col-span-2 flex flex-col gap-8">
                    <div class="bg-base-100 p-8 rounded-md shadow-xs border border-base-200 flex flex-col gap-6">
                        <h2 class="text-xl font-bold text-center text-primary uppercase tracking-wide">
                            Overtime Configuration
                        </h2>

                        <div class="form-control gap-1">
                            <label class="label pb-0">
                                <span
                                    class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Minimum
                                    Overtime Hours</span>
                            </label>
                            <input type="number" step="0.25" min="0.25"
                                class="input input-bordered focus:input-primary text-sm transition-all w-full"
                                v-model="form.minimum_overtime_hours" />
                            <span v-if="form.errors.minimum_overtime_hours" class="text-error text-xs mt-0.5">
                                {{ form.errors.minimum_overtime_hours }}
                            </span>
                            <p class="text-xs text-base-content/40 mt-1">Must be in 0.25 increments (e.g., 0.25, 0.50,
                                1.00)</p>
                        </div>

                        <div class="form-control gap-1">
                            <label class="label pb-0">
                                <span
                                    class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Overtime
                                    Minute Step</span>
                            </label>
                            <select class="select select-bordered focus:select-primary text-sm transition-all w-full"
                                v-model="form.overtime_minute_step">
                                <option v-for="opt in minuteStepOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                            <span v-if="form.errors.overtime_minute_step" class="text-error text-xs mt-0.5">
                                {{ form.errors.overtime_minute_step }}
                            </span>
                            <p class="text-xs text-base-content/40 mt-1">Minute interval for overtime time pickers</p>
                        </div>
                    </div>

                    <div class="bg-base-100 rounded-md p-6 shadow-xs border border-base-200 flex flex-col">
                        <h2 class="text-lg font-semibold mb-1 text-base-content">Organization Units</h2>
                        <p class="text-xs text-base-content/40 mb-4">Manage the organization units used for scoping
                            employees, approvers, required hours, and reports.</p>

                        <div class="overflow-auto flex-1">
                            <table class="table w-full text-sm">
                                <thead class="sticky top-0 bg-base-200 z-10 text-base-content">
                                    <tr class="text-center">
                                        <th class="py-3">Unit Name</th>
                                        <th class="w-32">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="unit in orgUnits" :key="unit.id"
                                        class="text-center hover:bg-base-300/30 transition-colors">
                                        <td class="py-2 font-semibold">{{ unit.unit_path }}</td>
                                        <td>
                                            <div class="flex items-center justify-center gap-1">
                                                <button type="button" @click="startEdit(unit)"
                                                    class="btn btn-success btn-xs">Edit</button>
                                                <button type="button" @click="confirmDelete(unit)"
                                                    class="btn btn-error btn-xs">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="orgUnits.length === 0">
                                        <td colspan="2" class="text-center py-4 text-base-content/50 text-xs">No
                                            organization units found. Add one below.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <form @submit.prevent="addUnit" class="flex items-end gap-3 mt-4">
                            <div class="form-control flex-1">
                                <label class="label pb-0">
                                    <span
                                        class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">New
                                        Unit Name</span>
                                </label>
                                <input type="text" v-model="addForm.unit_path" placeholder="e.g. Engineering Department"
                                    class="input input-bordered input-sm focus:input-primary text-sm transition-all w-full" />
                                <span v-if="addForm.errors.unit_path" class="text-error text-xs mt-0.5">
                                    {{ addForm.errors.unit_path }}
                                </span>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm" :disabled="addForm.processing">
                                <span v-if="addForm.processing" class="loading loading-spinner loading-xs"></span>
                                Add Unit
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-span-3 flex">
                    <div class="bg-base-100 rounded-md p-6 shadow-xs border border-base-200 flex flex-col flex-1">
                        <h2 class="text-lg font-semibold mb-4 text-base-content">Default Shift Codes</h2>
                        <p class="text-xs text-base-content/40 mb-4">Assign a default shift code for each day of the
                            week. Leave blank for no default.</p>
                        <div class="overflow-auto flex-1">
                            <table class="table w-full text-sm">
                                <thead class="sticky top-0 bg-base-200 z-10 text-base-content">
                                    <tr class="text-center">
                                        <th class="py-3">Day</th>
                                        <th>Shift Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(entry, index) in form.default_shift_codes" :key="index"
                                        class="text-center hover:bg-base-300/30 transition-colors">
                                        <td class="py-2 font-semibold">{{ entry.day }}</td>
                                        <td>
                                            <input type="text"
                                                class="input input-bordered input-sm focus:input-primary text-sm transition-all w-full max-w-32 mx-auto text-center"
                                                v-model="entry.code" placeholder="—" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner"></span>
                                <span>Save Settings</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </form>

        <Modal ref="editModal" title="Edit Organization Unit">
            <form @submit.prevent="saveEdit" class="flex flex-col gap-4">
                <div class="form-control gap-1">
                    <label class="label pb-0">
                        <span
                            class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Unit
                            Name</span>
                    </label>
                    <input type="text" v-model="editForm.unit_path" ref="editInput"
                        class="input input-bordered focus:input-primary text-sm transition-all w-full" />
                    <span v-if="editForm.errors.unit_path" class="text-error text-xs mt-0.5">
                        {{ editForm.errors.unit_path }}
                    </span>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="cancelEdit" class="btn btn-ghost btn-sm">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" :disabled="editForm.processing">
                        <span v-if="editForm.processing" class="loading loading-spinner loading-xs"></span>
                        Save
                    </button>
                </div>
            </form>
        </Modal>

        <Modal ref="deleteModal" title="Delete Organization Unit">
            <div class="flex flex-col gap-4">
                <p class="text-sm">Are you sure you want to delete <strong>{{ deleteTarget?.unit_path }}</strong>?</p>
                <div class="form-control gap-1">
                    <label class="label pb-0">
                        <span
                            class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Reassign
                            users to</span>
                    </label>
                    <select v-model="reassignTo"
                        class="select select-bordered select-sm focus:select-primary text-sm w-full">
                        <option v-for="unit in reassignOptions" :key="unit.id" :value="unit.id">{{ unit.unit_path }}
                        </option>
                    </select>
                    <span v-if="deleteForm.errors.reassign_to" class="text-error text-xs mt-0.5">
                        {{ deleteForm.errors.reassign_to }}
                    </span>
                </div>
                <p class="text-xs text-base-content/50">Users assigned to this unit will be moved to the selected unit.
                    Required hours for this unit will be removed.</p>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="cancelDelete" class="btn btn-ghost btn-sm">Cancel</button>
                    <button type="button" @click="deleteUnit" class="btn btn-error btn-sm"
                        :disabled="deleteForm.processing">
                        <span v-if="deleteForm.processing" class="loading loading-spinner loading-xs"></span>
                        Delete
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { inject, ref, computed, nextTick } from 'vue'
import Breadcrumbs from '../Components/Breadcrumbs.vue'
import Modal from '../Components/Modal.vue'

const toast = inject('toast')

const props = defineProps({
    settings: Object,
    organization_units: Array,
})

const minuteStepOptions = [
    { label: '1 minute', value: 1 },
    { label: '5 minutes', value: 5 },
    { label: '10 minutes', value: 10 },
    { label: '15 minutes', value: 15 },
    { label: '30 minutes', value: 30 },
]

const orgUnits = ref([...(props.organization_units ?? [])])

const form = useForm({
    default_shift_codes: props.settings?.default_shift_codes ?? [
        { day: 'Sunday', code: '' },
        { day: 'Monday', code: '' },
        { day: 'Tuesday', code: '' },
        { day: 'Wednesday', code: '' },
        { day: 'Thursday', code: '' },
        { day: 'Friday', code: '' },
        { day: 'Saturday', code: '' },
    ],
    minimum_overtime_hours: props.settings?.minimum_overtime_hours ?? 1,
    overtime_minute_step: props.settings?.overtime_minute_step ?? 15,
})

const submitForm = () => {
    form.put(route('admin.settings.update'), {
        onSuccess: () => {
            toast('Settings updated successfully.', 'success')
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0]
            if (firstError) {
                toast(firstError, 'error')
            }
        },
    })
}

const addForm = useForm({ unit_path: '' })

const addUnit = () => {
    addForm.post(route('admin.organization-units.store'), {
        onSuccess: () => {
            toast('Organization unit created.', 'success')
            addForm.reset()
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0]
            if (firstError) toast(firstError, 'error')
        },
    })
}

const editModal = ref(null)
const editInput = ref(null)
const editForm = useForm({ unit_path: '' })
const editingId = ref(null)

const startEdit = (unit) => {
    editingId.value = unit.id
    editForm.unit_path = unit.unit_path
    editForm.clearErrors()
    editModal.value?.open()
    nextTick(() => {
        editInput.value?.focus()
    })
}

const cancelEdit = () => {
    editModal.value?.close()
    editingId.value = null
    editForm.reset()
}

const saveEdit = () => {
    editForm.put(route('admin.organization-units.update', editingId.value), {
        onSuccess: () => {
            const idx = orgUnits.value.findIndex(u => u.id === editingId.value)
            if (idx !== -1) orgUnits.value[idx].unit_path = editForm.unit_path
            editModal.value?.close()
            editingId.value = null
            editForm.reset()
            toast('Organization unit updated.', 'success')
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0]
            if (firstError) toast(firstError, 'error')
        },
    })
}

const deleteModal = ref(null)
const deleteTarget = ref(null)
const reassignTo = ref(null)
const deleteForm = useForm({ reassign_to: null })

const reassignOptions = computed(() => {
    if (!deleteTarget.value) return []
    return orgUnits.value.filter(u => u.id !== deleteTarget.value.id)
})

const confirmDelete = (unit) => {
    deleteTarget.value = unit
    const options = orgUnits.value.filter(u => u.id !== unit.id)
    reassignTo.value = options.length > 0 ? options[0].id : null
    deleteForm.clearErrors()
    deleteModal.value?.open()
}

const cancelDelete = () => {
    deleteModal.value?.close()
    deleteTarget.value = null
    reassignTo.value = null
}

const deleteUnit = () => {
    deleteForm.reassign_to = reassignTo.value
    deleteForm.delete(route('admin.organization-units.destroy', deleteTarget.value.id), {
        onSuccess: () => {
            orgUnits.value = orgUnits.value.filter(u => u.id !== deleteTarget.value.id)
            deleteModal.value?.close()
            deleteTarget.value = null
            reassignTo.value = null
            toast('Organization unit deleted.', 'success')
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0]
            if (firstError) toast(firstError, 'error')
        },
    })
}
</script>
