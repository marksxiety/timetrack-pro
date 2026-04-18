<template>

    <Head title="Forgot Password" />
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
                            <Icon icon="material-symbols:lock-open-outline" width="32" height="32" class="text-primary" />
                        </div>
                    </div>

                    <!-- Heading -->
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-extrabold text-base-content">Forgot Password?</h2>
                        <p class="text-sm text-base-content/50 mt-2 leading-relaxed">
                            No worries. Enter your registered email and we'll send you a link to reset your password.
                        </p>
                    </div>

                    <!-- Success state -->
                    <div v-if="successMessage" class="alert alert-success mb-4">
                        <Icon icon="material-symbols:check-circle-outline" width="20" height="20" class="shrink-0" />
                        <span class="text-sm">{{ successMessage }}</span>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-5">
                        <!-- Email -->
                        <div class="form-control gap-1">
                            <label class="label pb-0">
                                <span
                                    class="label-text font-semibold text-base-content/80 text-xs uppercase tracking-wider">Email
                                    Address</span>
                            </label>
                            <label
                                class="input input-bordered flex items-center gap-2 focus-within:input-primary transition-all w-full">
                                <Icon icon="material-symbols:mail-outline" width="16" height="16"
                                    class="text-base-content/40 shrink-0" />
                                <input type="email" class="grow bg-transparent outline-none text-sm"
                                    placeholder="you@example.com" v-model="form.email" :disabled="!!successMessage" />
                            </label>
                            <span v-if="form.errors.email" class="text-error text-xs mt-0.5">{{ form.errors.email
                            }}</span>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary w-full shadow-lg shadow-primary/20"
                            :disabled="form.processing || !!successMessage">
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <span class="loading loading-spinner loading-xs"></span> Sending link...
                            </span>
                            <span v-else-if="successMessage" class="flex items-center gap-2">
                                <Icon icon="material-symbols:check-rounded" width="16" height="16" />
                                Email Sent
                            </span>
                            <span v-else class="flex items-center gap-2">
                                Send Reset Link
                                <Icon icon="material-symbols:arrow-forward-rounded" width="16" height="16" />
                            </span>
                        </button>
                    </form>

                    <div class="divider text-xs text-base-content/30 my-5">OR</div>

                    <div class="flex flex-col items-center gap-2 text-sm text-base-content/50">
                        <Link :href="route('login')"
                            class="flex items-center gap-1.5 link link-primary font-semibold no-underline hover:underline">
                            <Icon icon="material-symbols:arrow-back-rounded" width="16" height="16" />
                            Back to Login
                        </Link>
                        <span>
                            Don't have an account?
                            <Link :href="route('register')" class="link link-primary font-semibold">Register here</Link>
                        </span>
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

const successMessage = ref('')

const form = useForm({
    email: '',
})

const submitForm = () => {
    form.post(route('password.email'), {
        onSuccess: () => {
            successMessage.value = 'A password reset link has been sent to your email address.'
        },
        onError: (errors) => {
            console.error('Reset request failed:', errors)
        }
    })
}

defineOptions({
    layout: null
})
</script>