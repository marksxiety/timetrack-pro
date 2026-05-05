<template>
    <Head title="System Settings" />
    <div class="flex flex-col gap-6">
        <Breadcrumbs :items="[
            { label: 'Home', route: 'main' },
            { label: 'Settings', active: true },
        ]" />

        <div>
            <h1 class="text-lg font-bold">System Settings</h1>
            <p class="text-xs opacity-50 mt-0.5">Configure default shift codes, overtime rules, and system preferences.</p>
        </div>

        <form @submit.prevent="submitForm">
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-8 w-full max-w-7xl">

                <div class="col-span-2">
                    <div class="bg-base-100 p-8 rounded-md shadow-xs border border-base-200 flex flex-col gap-6">
                        <h2 class="text-xl font-bold text-center text-primary uppercase tracking-wide">
                            Overtime Configuration
                        </h2>

                        <div class="form-control gap-1">
                            <label class="label pb-0">
                                <span class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Minimum Overtime Hours</span>
                            </label>
                            <input type="number" step="0.25" min="0.25"
                                class="input input-bordered focus:input-primary text-sm transition-all w-full"
                                v-model="form.minimum_overtime_hours" />
                            <span v-if="form.errors.minimum_overtime_hours" class="text-error text-xs mt-0.5">
                                {{ form.errors.minimum_overtime_hours }}
                            </span>
                            <p class="text-xs text-base-content/40 mt-1">Must be in 0.25 increments (e.g., 0.25, 0.50, 1.00)</p>
                        </div>

                        <div class="form-control gap-1">
                            <label class="label pb-0">
                                <span class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Overtime Minute Step</span>
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
                </div>

                <div class="col-span-3">
                    <div class="bg-base-100 rounded-md p-6 shadow-xs border border-base-200 flex flex-col">
                        <h2 class="text-lg font-semibold mb-4 text-base-content">Default Shift Codes</h2>
                        <p class="text-xs text-base-content/40 mb-4">Assign a default shift code for each day of the week. Leave blank for no default.</p>
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
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { inject } from 'vue'
import Breadcrumbs from '../Components/Breadcrumbs.vue'

const toast = inject('toast')

const props = defineProps({
    settings: Object,
})

const minuteStepOptions = [
    { label: '1 minute', value: 1 },
    { label: '5 minutes', value: 5 },
    { label: '10 minutes', value: 10 },
    { label: '15 minutes', value: 15 },
    { label: '30 minutes', value: 30 },
]

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
</script>
