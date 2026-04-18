<template>

    <Head title="Login" />
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
                                    <Icon icon="material-symbols:mail-outline" width="16" height="16"
                                        class="text-base-content/40 shrink-0" />
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
                                    <Icon icon="material-symbols:lock-outline" width="16" height="16"
                                        class="text-base-content/40 shrink-0" />
                                    <input :type="showPassword ? 'text' : 'password'"
                                        class="grow bg-transparent outline-none text-sm" placeholder="••••••••"
                                        v-model="form.password" autocomplete="off" />
                                    <button type="button" @click="showPassword = !showPassword"
                                        class="text-base-content/30 hover:text-base-content/60 transition-colors">
                                        <Icon v-if="!showPassword" icon="material-symbols:visibility-outline" width="16" height="16" />
                                        <Icon v-else icon="material-symbols:visibility-off-outline" width="16" height="16" />
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
                                    <Icon icon="material-symbols:arrow-forward-rounded" width="16" height="16" />
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
import { Icon } from '@iconify/vue'
import loginImage from '../../images/Secure-login.svg'
import AuthBackground from '../../Components/AuthBackground.vue'

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