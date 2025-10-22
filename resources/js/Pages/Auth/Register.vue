<template>
    <Head title="Register"  />
    <main class="w-full px-20 box-border min-h-screen bg-base-300 mb-0 flex flex-col justify-center items-center">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-extrabold text-primary tracking-wide">
                TimeTrack Pro
            </h1>
            <p class="text-base text-gray-500">Overtime Tracker System</p>
        </div>

        <div class="min-h-[70vh] max-w-7xl mx-auto flex justify-center items-center">
            <div class="bg-base-100 grid grid-cols-1 xl:grid-cols-2 gap-12 w-full h-auto p-8 rounded-md shadow">
                <div class="col-span-1 flex justify-center items-center">
                    <img :src="registerImage" alt="register" class="w-full h-full max-w-80 max-h-80 object-contain" />
                </div>
                <div class="col-span-1">
                    <h2 class="text-2xl font-bold mb-6 text-primary text-center">Create Your Account</h2>
                    <form @submit.prevent="submitForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <TextInput name="Email:" type="email" :message="form.errors.email" v-model="form.email" />
                        </div>
                        <TextInput name="Name:" :message="form.errors.name" v-model="form.name" />
                        <TextInput name="Employee ID:" type="text" :message="form.errors.employeeid" v-model="form.employeeid" />
                        <SelectOption name="Choose a role:" :message="form.errors.role" v-model="form.role" :options="options" />
                        <SelectOption name="Select a unit:" :message="form.errors.organization_unit_id" v-model="form.organization_unit_id" :options="unitsList" />
                        <TextInput name="Password:" type="password" :message="form.errors.password" v-model="form.password" />
                        <TextInput name="Confirm Password:" type="password" v-model="form.password_confirmation" />

                        <!-- Make the submit button span full width -->
                        <div class="col-span-2">
                            <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">
                                <span v-if="form.processing">
                                    <span class="loading loading-spinner loading-xs"></span> Register
                                </span>
                                <span v-else>Register</span>
                            </button>
                        </div>
                    </form>
                    <div class="mt-6 text-center">
                        Already have an account?
                        <Link :href="route('login')" class="link">Login here</Link>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import TextInput from '../Components/TextInput.vue'
import SelectOption from '../Components/SelectOption.vue'
import registerImage from '../../images/Coder.svg'

const props = defineProps({
    units: Array
})

const unitsList = ref([
  { label: 'Choose a unit', value: '' },
])

props.units.forEach(unit => {
  unitsList.value.push({
    label: unit.unit_path,
    value: unit.id
  })
})

const options = ref([
    { label: 'Choose a role', value: '' },
    { label: 'Employee', value: 'employee' },
    { label: 'Approver', value: 'approver' }
])

const form = useForm({
    name: '',
    email: '',
    employeeid: '',
    role: '',
    organization_unit_id: '',
    password: '',
    password_confirmation: '',
})

const submitForm = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation')
        },
        onError: (errors) => {
            console.error('Registration failed:', errors)
        }
    })
}

defineOptions({
    layout: null
})
</script>