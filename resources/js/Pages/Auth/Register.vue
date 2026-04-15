<template>

    <Head title="Register" />
    <main class="min-h-screen bg-base-200 flex items-center justify-center relative overflow-hidden">
        <!-- Decorative background blobs -->
        <div
            class="absolute top-0 right-0 w-72 h-72 bg-secondary opacity-10 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2 pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 left-0 w-96 h-96 bg-primary opacity-10 rounded-full blur-3xl -translate-x-1/3 translate-y-1/3 pointer-events-none">
        </div>

        <div class="w-full max-w-5xl mx-auto px-4 py-12 flex flex-col items-center gap-8 z-10">

            <!-- Brand Header -->
            <div class="text-center space-y-1">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <div class="w-9 h-9 rounded-lg bg-primary flex items-center justify-center shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-content" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-black text-base-content tracking-tight">TimeTrack <span
                            class="text-primary">Pro</span></h1>
                </div>
                <p class="text-sm text-base-content/50 uppercase tracking-widest font-medium">Overtime Tracker System
                </p>
            </div>

            <!-- Card -->
            <div class="card bg-base-100 shadow-2xl w-full max-w-4xl">
                <div class="card-body p-0 grid grid-cols-1 lg:grid-cols-2">

                    <!-- Left: Illustration Panel -->
                    <div
                        class="hidden lg:flex flex-col items-center justify-center p-10 bg-primary/5 rounded-l-2xl border-r border-base-200 gap-6">
                        <img :src="registerImage" alt="register illustration"
                            class="w-60 h-60 object-contain drop-shadow-lg" />
                        <div class="text-center space-y-1">
                            <p class="font-bold text-base-content text-lg">Join the team!</p>
                            <p class="text-sm text-base-content/50">Create your account to start tracking and managing
                                your overtime.</p>
                        </div>
                    </div>

                    <!-- Right: Form Panel -->
                    <div class="flex flex-col justify-center p-8 lg:p-10">
                        <div class="mb-6">
                            <h2 class="text-2xl font-extrabold text-base-content">Create Account</h2>
                            <p class="text-sm text-base-content/50 mt-1">Fill in the details below to register</p>
                        </div>

                        <form @submit.prevent="submitForm" class="space-y-4">
                            <!-- Email - full width -->
                            <div class="form-control gap-1">
                                <label class="label pb-0">
                                    <span
                                        class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Email</span>
                                </label>
                                <label
                                    class="input input-bordered flex items-center gap-2 focus-within:input-primary transition-all w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-base-content/40 shrink-0" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                    <input type="email" class="grow bg-transparent outline-none text-sm"
                                        placeholder="you@example.com" v-model="form.email" />
                                </label>
                                <span v-if="form.errors.email" class="text-error text-xs mt-0.5">{{ form.errors.email
                                }}</span>
                            </div>

                            <!-- Name + Employee ID -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="form-control gap-1">
                                    <label class="label pb-0">
                                        <span
                                            class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Name</span>
                                    </label>
                                    <input type="text"
                                        class="input input-bordered focus:input-primary text-sm transition-all w-full"
                                        placeholder="Juan dela Cruz" v-model="form.name" />
                                    <span v-if="form.errors.name" class="text-error text-xs mt-0.5">{{ form.errors.name
                                    }}</span>
                                </div>

                                <div class="form-control gap-1">
                                    <label class="label pb-0">
                                        <span
                                            class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Employee
                                            ID</span>
                                    </label>
                                    <input type="text"
                                        class="input input-bordered focus:input-primary text-sm transition-all w-full"
                                        placeholder="EMP-0001" v-model="form.employeeid" />
                                    <span v-if="form.errors.employeeid" class="text-error text-xs mt-0.5">{{
                                        form.errors.employeeid }}</span>
                                </div>
                            </div>

                            <!-- Role + Unit -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="form-control gap-1">
                                    <label class="label pb-0">
                                        <span
                                            class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Role</span>
                                    </label>
                                    <select
                                        class="select select-bordered focus:select-primary text-sm transition-all w-full"
                                        v-model="form.role">
                                        <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label
                                        }}</option>
                                    </select>
                                    <span v-if="form.errors.role" class="text-error text-xs mt-0.5">{{ form.errors.role
                                    }}</span>
                                </div>

                                <div class="form-control gap-1">
                                    <label class="label pb-0">
                                        <span
                                            class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Unit</span>
                                    </label>
                                    <select
                                        class="select select-bordered focus:select-primary text-sm transition-all w-full"
                                        v-model="form.organization_unit_id">
                                        <option v-for="opt in unitsList" :key="opt.value" :value="opt.value">{{
                                            opt.label }}</option>
                                    </select>
                                    <span v-if="form.errors.organization_unit_id" class="text-error text-xs mt-0.5">{{
                                        form.errors.organization_unit_id }}</span>
                                </div>
                            </div>

                            <!-- Password + Confirm -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="form-control gap-1">
                                    <label class="label pb-0">
                                        <span
                                            class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Password</span>
                                    </label>
                                    <label
                                        class="input input-bordered flex items-center gap-2 focus-within:input-primary transition-all w-full">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 text-base-content/40 shrink-0" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <input :type="showPassword ? 'text' : 'password'"
                                            class="grow bg-transparent outline-none text-sm" placeholder="••••••••"
                                            v-model="form.password" />
                                        <button type="button" @click="showPassword = !showPassword"
                                            class="text-base-content/30 hover:text-base-content/60 transition-colors">
                                            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg"
                                                class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        </button>
                                    </label>
                                    <span v-if="form.errors.password" class="text-error text-xs mt-0.5">{{
                                        form.errors.password }}</span>
                                </div>

                                <div class="form-control gap-1">
                                    <label class="label pb-0">
                                        <span
                                            class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Confirm</span>
                                    </label>
                                    <label
                                        class="input input-bordered flex items-center gap-2 focus-within:input-primary transition-all w-full">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 text-base-content/40 shrink-0" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        <input :type="showConfirm ? 'text' : 'password'"
                                            class="grow bg-transparent outline-none text-sm" placeholder="••••••••"
                                            v-model="form.password_confirmation" />
                                        <button type="button" @click="showConfirm = !showConfirm"
                                            class="text-base-content/30 hover:text-base-content/60 transition-colors">
                                            <svg v-if="!showConfirm" xmlns="http://www.w3.org/2000/svg"
                                                class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        </button>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit -->
                            <button type="submit" class="btn btn-primary w-full mt-2 shadow-lg shadow-primary/20"
                                :disabled="form.processing">
                                <span v-if="form.processing" class="flex items-center gap-2">
                                    <span class="loading loading-spinner loading-xs"></span> Creating account...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    Create Account
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </span>
                            </button>
                        </form>

                        <div class="divider text-xs text-base-content/30 my-5">OR</div>

                        <p class="text-sm text-center text-base-content/50">
                            Already have an account?
                            <Link :href="route('login')" class="link link-primary font-semibold">Login here</Link>
                        </p>
                    </div>
                </div>
            </div>

            <p class="text-xs text-base-content/30">&copy; {{ new Date().getFullYear() }} TimeTrack Pro. All rights
                reserved.</p>
        </div>
    </main>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import registerImage from '../../images/Coder.svg'

const showPassword = ref(false)
const showConfirm = ref(false)

const props = defineProps({
    units: Array
})

const unitsList = ref([{ label: 'Choose a unit', value: '' }])

props.units.forEach(unit => {
    unitsList.value.push({ label: unit.unit_path, value: unit.id })
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