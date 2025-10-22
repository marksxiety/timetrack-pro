<template>
    <Head title="Login"  />
    <main class="w-full px-20 box-border min-h-screen bg-base-300 mb-0 flex flex-col justify-center items-center">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-extrabold text-primary tracking-wide">
                TimeTrack Pro
            </h1>
            <p class="text-base text-gray-500">Overtime Tracker System</p>
        </div>

        <div class="min-h-[70vh] max-w-4xl mx-auto flex justify-center items-center">
            <div class="bg-base-100 grid grid-cols-1 xl:grid-cols-2 gap-12 w-full h-auto p-8 rounded-md shadow">
                <div class="col-span-1 flex justify-center">
                    <img :src="loginImage" alt="login" class="w-full h-full max-w-80 max-h-80 object-contain" />
                </div>
                <div class="col-span-1">
                    <h2 class="text-2xl font-bold mb-6 text-primary text-center">Login</h2>
                    <form @submit.prevent="submitForm" class="card">
                        <TextInput name="Email:" type="email" :message="form.errors.email" v-model="form.email" />
                        <TextInput name="Password:" type="password" :message="form.errors.password"
                            v-model="form.password" />
                        <div class="flex items-end mb-4">
                            <label class="label">
                                <input type="checkbox" checked="checked" class="checkbox checkbox-primary"
                                    v-model="form.remember" />
                                Remember me
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">
                            <span v-if="form.processing"><span class="loading loading-spinner loading-xs"></span>
                                Login</span>
                            <span v-else>Login</span>
                        </button>
                    </form>
                    <div class="mt-6 text-center"> Dont have an account?
                        <Link :href="route('register')" class="link">
                        Register here
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import TextInput from '../Components/TextInput.vue'
import loginImage from '../../images/Secure-login.svg'

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
            console.error('Registration failed:', errors)
        }
    })
}

defineOptions({
    layout: null
})
</script>