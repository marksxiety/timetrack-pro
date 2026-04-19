<template>

    <Head title="Manage Profile" />
    <div class="flex flex-col gap-6">

        <Breadcrumbs :items="[
            { label: 'Dashboard', route: 'main' },
            { label: 'Update Profile', route: 'profile.employee', active: true },
        ]" />

        <!-- Page Title -->
        <div class="flex flex-col gap-1">
            <h1 class="text-xl font-semibold">Update Profile</h1>
            <p class="text-sm text-base-content/50">Manage your personal information and account security.</p>
        </div>

        <!-- Page Grid -->
        <div class="grid grid-cols-1 md:grid-cols-[260px_1fr] gap-5 max-w-4xl mx-auto w-full">

            <!-- Left: Avatar Card -->
            <div class="card bg-base-100 border border-base-300 shadow-none rounded-2xl">
                <div class="card-body items-center gap-0 p-7">

                    <!-- Avatar with online/offline indicator -->
                    <div class="mb-4" :class="form.active == 1 ? 'avatar avatar-online' : 'avatar avatar-offline'">
                        <div
                            class="w-28 rounded-full ring-2 ring-primary ring-offset-2 ring-offset-base-100 bg-base-200 flex items-center justify-center overflow-hidden">
                            <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar"
                                class="w-full h-full object-cover" />
                            <Icon v-else icon="lucide:user-round" width="48" height="48" class="text-base-content/20" />
                        </div>
                    </div>

                    <p class="font-semibold text-base text-center">{{ form.name || '—' }}</p>
                    <p class="text-xs text-base-content/50 text-center mb-5">{{ form.role || 'No role assigned' }}</p>

                    <!-- Upload Button -->
                    <button type="button" @click="triggerFileInput"
                        class="btn btn-sm btn-outline rounded-full w-full gap-2 mb-5">
                        <Icon icon="lucide:upload" width="13" height="13" />
                        Upload Avatar
                    </button>
                    <input ref="fileInput" type="file" id="avatar" name="avatar" accept="image/*" @change="change"
                        class="hidden" />
                    <p v-if="form.errors.avatar" class="text-xs text-error text-center mt-1">
                        {{ form.errors.avatar }}
                    </p>

                    <div class="divider my-1 w-full"></div>

                    <!-- Meta Info -->
                    <div class="flex flex-col gap-3 w-full">
                        <div class="flex items-center gap-2.5 text-xs text-base-content/50">
                            <Icon icon="lucide:mail" width="13" height="13" class="flex-shrink-0" />
                            <span class="text-base-content font-medium truncate">{{ form.email || '—' }}</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-base-content/50">
                            <Icon icon="lucide:id-card" width="13" height="13" class="flex-shrink-0" />
                            <span class="text-base-content font-medium">{{ form.employee_id || '—' }}</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-base-content/50">
                            <Icon icon="lucide:shield-check" width="13" height="13" class="flex-shrink-0" />
                            <span class="text-base-content font-medium">{{ form.role || '—' }}</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right: Form -->
            <form @submit.prevent="submitForm" class="flex flex-col gap-4">

                <!-- User Information -->
                <div class="card bg-base-100 border border-base-300 shadow-none rounded-2xl">
                    <div class="card-body p-5 gap-4">

                        <!-- Section Header -->
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                                <Icon icon="lucide:user-round" width="15" height="15" />
                            </div>
                            <div>
                                <p class="font-semibold text-sm leading-tight">User Information</p>
                                <p class="text-xs text-base-content/40 leading-tight">Update your name, email and
                                    status.</p>
                            </div>
                        </div>

                        <!-- Name + Active -->
                        <div class="grid grid-cols-[1fr_auto] gap-3 items-start">
                            <div class="form-control gap-1">
                                <label class="label py-0">
                                    <span
                                        class="label-text text-[11px] uppercase tracking-widest font-medium text-base-content/50">Full
                                        Name</span>
                                </label>
                                <label class="input input-bordered input-sm flex items-center gap-2 rounded-lg">
                                    <Icon icon="lucide:user-round" width="13" height="13"
                                        class="text-base-content/30 flex-shrink-0" />
                                    <input type="text" v-model="form.name" placeholder="Full name" class="grow" />
                                </label>
                                <p v-if="form.errors.name" class="text-xs text-error mt-0.5">{{ form.errors.name }}</p>
                            </div>

                            <div class="form-control gap-1">
                                <label class="label py-0">
                                    <span
                                        class="label-text text-[11px] uppercase tracking-widest font-medium text-base-content/50">Status</span>
                                </label>
                                <div class="flex gap-1.5 h-8">
                                    <button type="button" @click="form.active = 1"
                                        class="flex-1 text-xs font-medium px-3 rounded-lg border transition-all"
                                        :class="form.active == 1 ? 'bg-success/15 text-success border-success/30' : 'border-base-300 text-base-content/40 hover:bg-base-200'">
                                        YES
                                    </button>
                                    <button type="button" @click="form.active = 0"
                                        class="flex-1 text-xs font-medium px-3 rounded-lg border transition-all"
                                        :class="form.active == 0 ? 'bg-error/15 text-error border-error/30' : 'border-base-300 text-base-content/40 hover:bg-base-200'">
                                        NO
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-control gap-1">
                            <label class="label py-0">
                                <span
                                    class="label-text text-[11px] uppercase tracking-widest font-medium text-base-content/50">Email
                                    Address</span>
                            </label>
                            <label class="input input-bordered input-sm flex items-center gap-2 rounded-lg">
                                <Icon icon="lucide:mail" width="13" height="13"
                                    class="text-base-content/30 flex-shrink-0" />
                                <input type="email" v-model="form.email" placeholder="Email address" class="grow" />
                            </label>
                            <p v-if="form.errors.email" class="text-xs text-error mt-0.5">{{ form.errors.email }}</p>
                        </div>

                        <!-- Employee ID + Role -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="form-control gap-1">
                                <label class="label py-0">
                                    <span
                                        class="label-text text-[11px] uppercase tracking-widest font-medium text-base-content/50">Employee
                                        ID</span>
                                </label>
                                <label
                                    class="input input-bordered input-sm flex items-center gap-2 rounded-lg bg-base-200 opacity-60 cursor-not-allowed">
                                    <Icon icon="lucide:id-card" width="13" height="13"
                                        class="text-base-content/30 flex-shrink-0" />
                                    <input type="text" v-model="form.employee_id" readonly
                                        class="grow cursor-not-allowed" />
                                </label>
                            </div>
                            <div class="form-control gap-1">
                                <label class="label py-0">
                                    <span
                                        class="label-text text-[11px] uppercase tracking-widest font-medium text-base-content/50">Role</span>
                                </label>
                                <label
                                    class="input input-bordered input-sm flex items-center gap-2 rounded-lg bg-base-200 opacity-60 cursor-not-allowed">
                                    <Icon icon="lucide:shield-check" width="13" height="13"
                                        class="text-base-content/30 flex-shrink-0" />
                                    <input type="text" v-model="form.role" readonly class="grow cursor-not-allowed" />
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Change Password -->
                <div class="card bg-base-100 border border-base-300 shadow-none rounded-2xl">
                    <div class="card-body p-5 gap-4">

                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-8 h-8 rounded-lg bg-secondary/10 text-secondary flex items-center justify-center flex-shrink-0">
                                <Icon icon="lucide:lock-keyhole" width="15" height="15" />
                            </div>
                            <div>
                                <p class="font-semibold text-sm leading-tight">Change Password</p>
                                <p class="text-xs text-base-content/40 leading-tight">Leave blank to keep your current
                                    password.</p>
                            </div>
                        </div>

                        <!-- Old Password -->
                        <div class="form-control gap-1">
                            <label class="label py-0">
                                <span
                                    class="label-text text-[11px] uppercase tracking-widest font-medium text-base-content/50">Current
                                    Password</span>
                            </label>
                            <label class="input input-bordered input-sm flex items-center gap-2 rounded-lg">
                                <Icon icon="lucide:lock" width="13" height="13"
                                    class="text-base-content/30 flex-shrink-0" />
                                <input type="password" v-model="form.old_password" placeholder="••••••••"
                                    class="grow" />
                            </label>
                            <p v-if="form.errors.old_password" class="text-xs text-error mt-0.5">{{
                                form.errors.old_password }}</p>
                        </div>

                        <!-- New + Confirm -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="form-control gap-1">
                                <label class="label py-0">
                                    <span
                                        class="label-text text-[11px] uppercase tracking-widest font-medium text-base-content/50">New
                                        Password</span>
                                </label>
                                <label class="input input-bordered input-sm flex items-center gap-2 rounded-lg">
                                    <Icon icon="lucide:lock-keyhole" width="13" height="13"
                                        class="text-base-content/30 flex-shrink-0" />
                                    <input type="password" v-model="form.new_password" placeholder="••••••••"
                                        class="grow" />
                                </label>
                                <!-- Password Strength Bar -->
                                <div class="h-1 rounded-full bg-base-300 mt-1 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300"
                                        :style="{ width: passwordStrengthWidth, background: passwordStrengthColor }">
                                    </div>
                                </div>
                                <p v-if="form.errors.new_password" class="text-xs text-error mt-0.5">{{
                                    form.errors.new_password }}</p>
                            </div>
                            <div class="form-control gap-1">
                                <label class="label py-0">
                                    <span
                                        class="label-text text-[11px] uppercase tracking-widest font-medium text-base-content/50">Confirm
                                        Password</span>
                                </label>
                                <label class="input input-bordered input-sm flex items-center gap-2 rounded-lg">
                                    <Icon icon="lucide:lock-keyhole" width="13" height="13"
                                        class="text-base-content/30 flex-shrink-0" />
                                    <input type="password" v-model="form.new_password_confirmation"
                                        placeholder="••••••••" class="grow" />
                                </label>
                                <p v-if="form.errors.new_password_confirmation" class="text-xs text-error mt-0.5">{{
                                    form.errors.new_password_confirmation }}</p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary rounded-xl w-full gap-2" :disabled="form.processing">
                    <span v-if="form.processing" class="loading loading-spinner loading-xs"></span>
                    <Icon v-else icon="lucide:save" width="15" height="15" />
                    Save Changes
                </button>

            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3'
import { ref, inject, computed } from 'vue'
import { Icon } from '@iconify/vue'
import Breadcrumbs from './Components/Breadcrumbs.vue'

const props = defineProps({
    user: Object,
    avatar_url: String,
    errors: Object,
    flash: Object,
    auth: Object,
})

const toast = inject('toast')

const form = useForm({
    name: props.auth?.user?.name ?? '',
    email: props.auth?.user?.email ?? '',
    employee_id: props.auth?.user?.employeeid ?? '',
    role: props.auth?.user?.role ?? '',
    avatar: null,
    active: props.auth?.user?.active ?? 0,
    old_password: '',
    new_password: '',
    new_password_confirmation: '',
})

const fileInput = ref(null)
const avatarPreview = ref(props.avatar_url ?? null)

const triggerFileInput = () => fileInput.value.click()

const change = (event) => {
    const file = event.target.files[0]
    form.avatar = file
    if (file) {
        const reader = new FileReader()
        reader.onload = (e) => { avatarPreview.value = e.target.result }
        reader.readAsDataURL(file)
    } else {
        avatarPreview.value = props.avatar_url ?? null
    }
}

const passwordStrengthWidth = computed(() => {
    const val = form.new_password
    if (!val) return '0%'
    let score = 0
    if (val.length >= 8) score++
    if (/[A-Z]/.test(val)) score++
    if (/[0-9]/.test(val)) score++
    if (/[^A-Za-z0-9]/.test(val)) score++
    return `${(score / 4) * 100}%`
})

const passwordStrengthColor = computed(() => {
    const val = form.new_password
    if (!val) return 'transparent'
    let score = 0
    if (val.length >= 8) score++
    if (/[A-Z]/.test(val)) score++
    if (/[0-9]/.test(val)) score++
    if (/[^A-Za-z0-9]/.test(val)) score++
    return ['#ef4444', '#f97316', '#eab308', '#22c55e'][score - 1] ?? 'transparent'
})

const submitForm = () => {
    form.post(route('profile.update.employee'), {
        onFinish: () => {
            toast(props.flash?.message, 'success')
            form.reset('old_password', 'new_password', 'new_password_confirmation')
        },
        onError: () => {
            toast('Updating profile failed', 'error')
        },
    })
}
</script>