<template>
    <Head title="Registered Hours Limit"  />
    <div class="flex flex-col gap-8 h-screen px-6 py-4">
        <!-- Breadcrumbs -->
        <div class="breadcrumbs text-sm">
            <ul class="flex gap-2 items-center">
                <li>
                    <Link :href="route('main')" class="hover:text-primary transition-colors">Home</Link>
                </li>
                <li>
                    <Link :href="route('hours')" class="hover:text-primary transition-colors">Authorized Hours
                    Registration</Link>
                </li>
            </ul>
        </div>

        <!-- Page Heading -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-base-content">Manage Authorized Hours</h1>
        </div>

        <!-- Main Grid Content -->
        <div class="grid place-items-center">
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-8 h-[32rem] w-full max-w-7xl">

                <!-- Form Panel -->
                <div class="col-span-2">
                    <div
                        class="bg-base-100 p-8 rounded-md shadow-lg min-h-[50vh] flex flex-col justify-center border border-base-200">
                        <!-- Title -->
                        <h2 class="text-xl font-bold mb-6 text-center text-primary uppercase tracking-wide">
                            Weekly Authorization Form
                        </h2>

                        <form @submit.prevent="submitForm()" class="card space-y-4">
                            <SelectOption name="Year:" :options="years" v-model="form.year"
                                :message="form.errors.year" />
                            <SelectOption name="Week:" :options="weeks" v-model="form.week"
                                :message="form.errors.week" />
                            <TextInput name="Required Hours:" type="number" v-model="form.required_hours"
                                :message="form.errors.required_hours"
                                placeholder="Enter the required hours per week..." />
                            <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner"></span>
                                <span>Submit</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Table Panel -->
                <div class="col-span-3">
                    <div class="bg-base-100 rounded-md p-6 min-h-[50vh] overflow-auto shadow-lg border border-base-200">
                        <h2 class="text-lg font-semibold mb-4 text-base-content">Registered Required Hours</h2>
                        <table class="table w-full text-sm">
                            <thead class="sticky top-0 bg-base-200 z-10 text-base-content">
                                <tr class="text-center">
                                    <th class="py-3">Year</th>
                                    <th>Week</th>
                                    <th>Required Hours</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="required in registerd_required_hours" :key="required.required_code"
                                    class="text-center hover:bg-base-300/30 transition-colors">
                                    <td class="py-2">{{ required.year }}</td>
                                    <td>{{ required.week }}</td>
                                    <td>{{ `${required.required_hours} hrs` }}</td>
                                    <td class="flex gap-2 justify-center">
                                        <button class="btn btn-success btn-xs" @click="handleHypyerLink(required)">
                                            EDIT
                                        </button>
                                        <button class="btn btn-error btn-xs">
                                            <span>DELETE</span>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="registerd_required_hours.length === 0">
                                    <td colspan="4" class="text-center italic text-gray-400 py-4">
                                        No required hours available.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>


<script setup>
import TextInput from '../Components/TextInput.vue'
import SelectOption from '../Components/SelectOption.vue'
import { ref, inject, watch } from 'vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'
import { years, weeks, currentWeek } from '../utils/dropdownOptions.js'

const toast = inject('toast')
const page = usePage()

const props = defineProps({
    requiredhours: Array,
    error: String
})

const mode = ref('insert')
const id = ref(null)

const registerd_required_hours = ref([...props.requiredhours ?? []])

const form = useForm({
    year: new Date().getFullYear(),
    week: currentWeek(),
    required_hours: ''
})

const submitForm = () => {
    if (mode.value === 'insert') {
        form.post(route('hours.register'), {
            onSuccess: () => {
                toast(page.props?.flash?.message, 'success')
                form.reset()
            },
            onError: () => {
                toast('Registration failed. Please try again', 'error')
            }
        })
    } else {
        if (id) {
            form.put(route('hours.update', id.value), {
                onSuccess: () => {
                    toast(page.props?.flash?.message, 'success')
                    mode.value = 'insert'
                    form.reset()
                },
                onError: (error) => {
                    toast('Updating data failed. Please try again', 'error')
                }
            })
        } else {
            toast('Invalid action. Please try again.', 'error')
        }
    }
}

const handleHypyerLink = (data) => {
    form.year = data.year
    form.week = data.week
    form.required_hours = data.required_hours
    id.value = data.id
    mode.value = 'update'
}


watch(
    () => props.requiredhours,
    (newrequiredhours) => {
        registerd_required_hours.value = [...newrequiredhours]
    }
)

</script>