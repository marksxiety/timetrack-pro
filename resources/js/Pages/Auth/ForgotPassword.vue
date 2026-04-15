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
            <div class="card bg-base-100 shadow-2xl w-full">
                <div class="card-body p-8 lg:p-10">

                    <!-- Icon -->
                    <div class="flex justify-center mb-2">
                        <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-primary" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/40 shrink-0"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Email Sent
                            </span>
                            <span v-else class="flex items-center gap-2">
                                Send Reset Link
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </span>
                        </button>
                    </form>

                    <div class="divider text-xs text-base-content/30 my-5">OR</div>

                    <div class="flex flex-col items-center gap-2 text-sm text-base-content/50">
                        <Link :href="route('login')"
                            class="flex items-center gap-1.5 link link-primary font-semibold no-underline hover:underline">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
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