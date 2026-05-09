<template>

    <Head title="Reset Password" />
    <main class="min-h-screen bg-base-100 flex flex-1 flex-col items-center justify-center relative overflow-hidden">
        <AuthBackground />

        <div class="relative z-10 w-full max-w-md mx-auto px-4 py-12 flex flex-col items-center gap-8">

            <!-- Brand Header -->
            <div class="text-center space-y-1">
                <h1 class="text-3xl font-black text-base-content tracking-tight mb-2">TimeTrack <span
                        class="text-primary">Pro</span></h1>
                <p class="text-sm text-base-content/50 uppercase tracking-widest font-medium">Overtime Tracker System
                </p>
            </div>

            <!-- Card -->
            <div class="card bg-base-100 shadow-2xl w-full">
                <div class="card-body p-8 lg:p-10">

                    <!-- Icon -->
                    <div class="flex justify-center mb-2">
                        <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center">
                            <Icon icon="material-symbols:key-outline" width="32" height="32" class="text-primary" />
                        </div>
                    </div>

                    <!-- Heading -->
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-extrabold text-base-content">Reset Password</h2>
                        <p class="text-sm text-base-content/50 mt-2 leading-relaxed">
                            Enter your new password below. Make sure it's at least 8 characters long.
                        </p>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-5">
                        <!-- Email (hidden, submitted with form) -->
                        <input type="hidden" v-model="form.email" />

                        <!-- New Password -->
                        <div class="form-control gap-1">
                            <label class="label pb-0">
                                <span
                                    class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">New
                                    Password</span>
                            </label>
                            <label
                                class="input input-bordered flex items-center gap-2 focus-within:input-primary transition-all w-full">
                                <Icon icon="material-symbols:lock-outline" width="16" height="16"
                                    class="text-base-content/40 shrink-0" />
                                <PasswordInput v-model="form.password" placeholder="Enter new password" />
                            </label>
                            <span v-if="form.errors.password" class="text-error text-xs mt-0.5">{{ form.errors.password
                            }}</span>
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-control gap-1">
                            <label class="label pb-0">
                                <span
                                    class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Confirm
                                    Password</span>
                            </label>
                            <label
                                class="input input-bordered flex items-center gap-2 focus-within:input-primary transition-all w-full">
                                <Icon icon="material-symbols:shield-outline" width="16" height="16"
                                    class="text-base-content/40 shrink-0" />
                                <PasswordInput v-model="form.password_confirmation" placeholder="Confirm new password" prefix-icon="material-symbols:shield-outline" />
                            </label>
                            <span v-if="form.errors.password_confirmation"
                                class="text-error text-xs mt-0.5">{{ form.errors.password_confirmation }}</span>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary w-full shadow-lg shadow-primary/20"
                            :disabled="form.processing">
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <span class="loading loading-spinner loading-xs"></span> Resetting password...
                            </span>
                            <span v-else class="flex items-center gap-2">
                                Reset Password
                                <Icon icon="material-symbols:arrow-forward-rounded" width="16" height="16" />
                            </span>
                        </button>
                    </form>

                    <div class="divider text-xs text-base-content/30 my-5">OR</div>

                    <div class="flex flex-col items-center gap-2 text-sm text-base-content/50">
                        <Link :href="route('password.request')"
                            class="flex items-center gap-1.5 link link-primary font-semibold no-underline hover:underline">
                            <Icon icon="material-symbols:lock-open-outline" width="16" height="16" />
                            Request another link
                        </Link>
                        <Link :href="route('login')"
                            class="flex items-center gap-1.5 link link-primary font-semibold no-underline hover:underline">
                            <Icon icon="material-symbols:arrow-back-rounded" width="16" height="16" />
                            Back to Login
                        </Link>
                    </div>
                </div>
            </div>

            <p class="text-xs text-base-content/30">&copy; {{ new Date().getFullYear() }} TimeTrack Pro. All rights
                reserved.</p>
        </div>
    </main>
    <FloatingThemeToggle />
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AuthBackground from '../../Components/AuthBackground.vue'
import PasswordInput from '../Components/PasswordInput.vue'
import FloatingThemeToggle from '../../Components/FloatingThemeToggle.vue'

const props = defineProps({
    token: String,
    email: String,
})

const showConfirmPassword = ref(false)

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

const submitForm = () => {
    form.post(route('password.update'), {
        onSuccess: () => {
            form.reset('password', 'password_confirmation')
        },
        onError: (errors) => {
            console.error('Reset failed:', errors)
        }
    })
}

defineOptions({
    layout: null
})
</script>
