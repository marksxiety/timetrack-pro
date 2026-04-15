<template>

    <Head title="Login" />
    <main class="min-h-screen bg-base-200 flex items-center justify-center relative overflow-hidden">
        <!-- Decorative background blobs -->
        <div
            class="absolute top-0 left-0 w-72 h-72 bg-primary opacity-10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 right-0 w-96 h-96 bg-secondary opacity-10 rounded-full blur-3xl translate-x-1/3 translate-y-1/3 pointer-events-none">
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
                        <img :src="loginImage" alt="login illustration"
                            class="w-60 h-60 object-contain drop-shadow-lg" />
                        <div class="text-center space-y-1">
                            <p class="font-bold text-base-content text-lg">Welcome back!</p>
                            <p class="text-sm text-base-content/50">Sign in to manage and track your overtime records.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Form Panel -->
                    <div class="flex flex-col justify-center p-8 lg:p-10">
                        <div class="mb-8">
                            <h2 class="text-2xl font-extrabold text-base-content">Sign In</h2>
                            <p class="text-sm text-base-content/50 mt-1">Enter your credentials to continue</p>
                        </div>

                        <form @submit.prevent="submitForm" class="space-y-5">
                            <!-- Email -->
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
                                        placeholder="you@example.com" v-model="form.email" autocomplete="off" />
                                </label>
                                <span v-if="form.errors.email" class="text-error text-xs mt-0.5">{{ form.errors.email
                                    }}</span>
                            </div>

                            <!-- Password -->
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
                                        v-model="form.password" autocomplete="off" />
                                    <button type="button" @click="showPassword = !showPassword"
                                        class="text-base-content/30 hover:text-base-content/60 transition-colors">
                                        <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </label>
                                <span v-if="form.errors.password" class="text-error text-xs mt-0.5">{{
                                    form.errors.password }}</span>
                            </div>

                            <!-- Remember me -->
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" class="checkbox checkbox-primary checkbox-sm"
                                        v-model="form.remember" />
                                    <span class="text-sm text-base-content/60">Remember me</span>
                                </label>
                                <Link :href="route('password.request')" class="link link-primary text-sm font-medium">Forgot password?</Link>
                            </div>

                            <!-- Submit -->
                            <button type="submit" class="btn btn-primary w-full mt-2 shadow-lg shadow-primary/20"
                                :disabled="form.processing">
                                <span v-if="form.processing" class="flex items-center gap-2">
                                    <span class="loading loading-spinner loading-xs"></span> Signing in...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    Sign In
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </span>
                            </button>
                        </form>

                        <div class="divider text-xs text-base-content/30 my-6">OR</div>

                        <p class="text-sm text-center text-base-content/50">
                            Don't have an account?
                            <Link :href="route('register')" class="link link-primary font-semibold">Register here</Link>
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
import loginImage from '../../images/Secure-login.svg'

const showPassword = ref(false)

const form = useForm({
    email: null,
    password: null,
    remember: null
})

const submitForm = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password', 'remember')
        },
        onError: (errors) => {
            console.error('Login failed:', errors)
        }
    })
}

defineOptions({
    layout: null
})
</script>