<template>
    <Head title="Manage Profile"  />
    <div class="flex flex-col gap-6 h-full">
        <div class="breadcrumbs text-sm">
            <ul>
                <li>
                    <Link :href="route('main')">Dashboard</Link>
                </li>
                <li>
                    <Link :href="route('profile.employee')">
                    Update Profile
                    </Link>
                </li>
            </ul>
        </div>

        <!-- Page Heading -->

        <div class="flex justify-center">
            <div class="bg-base-100 rounded-2xl p-4 w-3/4">
                <form @submit.prevent="submitForm" class="grid gap-4 grid-cols-1 md:grid-cols-2">
                    <div class="mb-4 col-span-1 h-full">
                        <div class="flex flex-col items-center justify-center space-y-3 h-full">
                            <div v-if="avatarPreview"
                                class="w-64 h-64 rounded-full overflow-hidden border-4 border-primary bg-base-100 flex items-center justify-center shadow">
                                <img :src="avatarPreview" alt="Avatar Preview" class="object-cover w-full h-full" />
                            </div>
                            <div v-else
                                class="w-64 h-64 rounded-full overflow-hidden border-4 border-primary bg-base-100 flex items-center justify-center shadow">
                                <Icon icon="streamline:user-profile-focus" width="90" height="90" />
                            </div>
                            <button type="button" @click="triggerFileInput" class="btn btn-primary rounded-full">
                                Update Avatar
                            </button>
                        </div>
                        <input ref="fileInput" type="file" id="avatar" name="avatar" accept="image/*" @change="change"
                            class="hidden" />
                        <p v-if="form.errors.avatar"
                            class="mt-1 text-sm text-red-600 bg-red-50 px-2 py-1 rounded text-center">
                            {{ form.errors.avatar }}
                        </p>
                    </div>
                    <div class="col-span-1 flex flex-col gap-4 p-2">
                        <fieldset class="bg-base-200 border border-base-300 p-4 rounded-md">
                            <legend class="text-sm font-semibold px-2">User Information</legend>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2">
                                    <TextInput name="Name:" :message="form.errors.name" v-model="form.name"
                                        placeholder="" />
                                </div>
                                <div class="col-span-1">
                                    <SelectOption name="Active: " :options="[
                                        { label: 'YES', value: 1 },
                                        { label: 'NO', value: 0 }
                                    ]" v-model="form.active" margin="" minwidth="" />
                                </div>
                            </div>
                            <TextInput name="Email:" type="email" :message="form.errors.email" v-model="form.email"
                                placeholder="" />
                            <div class="flex justify-between gap-4">
                                <TextInput name="Employee ID:" type="text" :message="form.errors.employee_id"
                                    v-model="form.employee_id" :readonly="true" placeholder="" class="w-full"
                                    v-bind:readonly />
                                <TextInput name="Role:" type="text" :message="form.errors.role" v-model="form.role"
                                    :readonly="true" placeholder="" class="w-full" v-bind:readonly />
                            </div>

                        </fieldset>

                        <fieldset class="bg-base-200 border border-base-300 p-4 rounded-md">
                            <legend class="text-sm font-semibold px-2">Change Password</legend>
                            <TextInput name="Old Password:" type="password" :message="form.errors.old_password"
                                v-model="form.old_password" placeholder="" />
                            <TextInput name="New Password" type="password" v-model="form.new_password"
                                :message="form.errors.new_password" placeholder="" />
                            <TextInput name="Confrim New Password" type="password"
                                v-model="form.new_password_confirmation"
                                :message="form.errors.new_password_confirmation" placeholder="" />

                        </fieldset>
                        <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">
                            <span v-if="form.processing" class="loading loading-spinner loading-xs"></span> Update
                            Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import TextInput from './Components/TextInput.vue'
import SelectOption from './Components/SelectOption.vue'
import { ref, inject } from 'vue'
import { Icon } from "@iconify/vue";

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
    new_password_confirmation: ''
})

const fileInput = ref(null)
// Use avatarPreview for previewing the image
const avatarPreview = ref(props.avatar_url ?? null)

const triggerFileInput = () => {
    fileInput.value.click()
}

const change = (event) => {
    const file = event.target.files[0]
    form.avatar = file
    if (file) {
        const reader = new FileReader()
        reader.onload = (e) => {
            avatarPreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    } else {
        avatarPreview.value = props.avatar_url ?? null
    }
}


const submitForm = () => {
    form.post(route('profile.update.employee'), {
        onFinish: () => {
            toast(props.flash?.message, 'success')
            form.reset('old_password', 'new_password_confirmation', 'new_password')
        },
        onError: (errors) => {
            toast('Updating task failed', 'error')
        }
    })
}
</script>