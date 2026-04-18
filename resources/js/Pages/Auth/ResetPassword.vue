<template>

    <Head title="Reset Password" />
    <main class="min-h-screen bg-base-200 flex items-center justify-center relative overflow-hidden">
        <!-- Decorative background blobs -->
        <div
            class="absolute top-0 left-0 w-72 h-72 bg-primary opacity-10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 right-0 w-96 h-96 bg-secondary opacity-10 rounded-full blur-3xl translate-x-1/3 translate-y-1/3 pointer-events-none">
        </div>

        <div class="w-full max-w-md mx-auto px-4 py-12 flex flex-col items-center gap-8 z-10">

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
                                <input :type="showPassword ? 'text' : 'password'" class="grow bg-transparent outline-none text-sm"
                                    placeholder="Enter new password" v-model="form.password" />
                                <button type="button" @click="showPassword = !showPassword"
                                    class="text-base-content/30 hover:text-base-content/60 transition-colors">
                                    <Icon v-if="!showPassword" icon="material-symbols:visibility-outline" width="16" height="16" />
                                    <Icon v-else icon="material-symbols:visibility-off-outline" width="16" height="16" />
                                </button>
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
                                <input :type="showConfirmPassword ? 'text' : 'password'"
                                    class="grow bg-transparent outline-none text-sm" placeholder="Confirm new password"
                                    v-model="form.password_confirmation" />
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                    class="text-base-content/30 hover:text-base-content/60 transition-colors">
                                    <Icon v-if="!showConfirmPassword" icon="material-symbols:visibility-outline" width="16" height="16" />
                                    <Icon v-else icon="material-symbols:visibility-off-outline" width="16" height="16" />
                                </button>
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
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const props = defineProps({
    token: String,
    email: String,
})

const showPassword = ref(false)
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
