<template>

    <Head title="Register" />
    <main class="min-h-screen bg-base-100 flex flex-1 flex-col items-center justify-center relative overflow-hidden">
        <AuthBackground />

        <div class="relative z-10 w-full max-w-5xl mx-auto px-4 py-12 flex flex-col items-center gap-8">

            <!-- Brand Header -->
            <div class="text-center space-y-1">
                <h1 class="text-3xl font-black text-base-content tracking-tight mb-2">TimeTrack <span
                        class="text-primary">Pro</span></h1>
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
                                    <Icon icon="material-symbols:mail-outline" width="16" height="16"
                                        class="text-base-content/40 shrink-0" />
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
                                        <Icon icon="material-symbols:lock-outline" width="16" height="16"
                                            class="text-base-content/40 shrink-0" />
                                        <input :type="showPassword ? 'text' : 'password'"
                                            class="grow bg-transparent outline-none text-sm" placeholder="••••••••"
                                            v-model="form.password" />
                                        <button type="button" @click="showPassword = !showPassword"
                                            class="text-base-content/30 hover:text-base-content/60 transition-colors">
                                            <Icon v-if="!showPassword" icon="material-symbols:visibility-outline" width="14" height="14" />
                                            <Icon v-else icon="material-symbols:visibility-off-outline" width="14" height="14" />
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
                                        <Icon icon="material-symbols:shield-outline" width="16" height="16"
                                            class="text-base-content/40 shrink-0" />
                                        <input :type="showConfirm ? 'text' : 'password'"
                                            class="grow bg-transparent outline-none text-sm" placeholder="••••••••"
                                            v-model="form.password_confirmation" />
                                        <button type="button" @click="showConfirm = !showConfirm"
                                            class="text-base-content/30 hover:text-base-content/60 transition-colors">
                                            <Icon v-if="!showConfirm" icon="material-symbols:visibility-outline" width="14" height="14" />
                                            <Icon v-else icon="material-symbols:visibility-off-outline" width="14" height="14" />
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
                                    <Icon icon="material-symbols:arrow-forward-rounded" width="16" height="16" />
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
import { Icon } from '@iconify/vue'
import registerImage from '../../images/Coder.svg'
import AuthBackground from '../../Components/AuthBackground.vue'

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