<template>

    <Head title="Manage Users" />
    <Modal ref="displayUserModal">
        <div class="flex flex-col">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                    <Icon icon="material-symbols:person-edit-rounded" class="text-primary text-lg" />
                </div>
                <div>
                    <h3 class="text-base font-bold">Update User Profile</h3>
                    <p class="text-[11px] opacity-50">Modify user information and settings</p>
                </div>
            </div>
            <form @submit.prevent="updateUserProfile()">
                <div class="card border border-base-300 shadow-xs mb-3">
                    <div class="card-body p-3">
                        <h4 class="card-title text-xs flex items-center gap-1.5 mb-2">
                            <Icon icon="material-symbols:info-rounded" class="text-sm" />
                            Basic Information
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <TextInput name="Email" type="email" :message="selectedUser.errors.email"
                                v-model="selectedUser.email" placeholder="" margin="" />
                            <TextInput name="Name" type="text" :message="selectedUser.errors.name"
                                v-model="selectedUser.name" placeholder="" margin="" />
                        </div>
                    </div>
                </div>

                <div class="card border border-base-300 shadow-xs mb-3">
                    <div class="card-body p-3">
                        <h4 class="card-title text-xs flex items-center gap-1.5 mb-2">
                            <Icon icon="material-symbols:assignment-rounded" class="text-sm" />
                            Assignment & Access
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <SelectOption name="Unit" :options="unitsList" v-model="selectedUser.organization_unit_id"
                                margin="" minwidth="" />
                            <SelectOption name="Active" :options="[
                                { label: 'YES', value: 1 },
                                { label: 'NO', value: 0 }
                            ]" v-model="selectedUser.active" margin="" minwidth="" />
                            <SelectOption name="Role" :options="[
                                { label: 'Approver', value: 'approver' },
                                { label: 'Employee', value: 'employee' }
                            ]" v-model="selectedUser.role" margin="" minwidth="" />
                        </div>
                    </div>
                </div>

                <div class="card border border-base-300 shadow-xs mb-3">
                    <div class="card-body p-3">
                        <h4 class="card-title text-xs flex items-center gap-1.5 mb-2">
                            <Icon icon="material-symbols:lock-reset" class="text-sm" />
                            Change Password
                            <span class="badge badge-ghost badge-xs">Optional</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <TextInput name="New Password" type="password" v-model="selectedUser.new_password"
                                :message="selectedUser.errors.new_password" placeholder="" margin="" />
                            <TextInput name="Confirm New Password" type="password"
                                v-model="selectedUser.new_password_confirmation"
                                :message="selectedUser.errors.new_password_confirmation" placeholder="" margin="" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" class="btn btn-ghost btn-sm" :disabled="selectedUser.processing"
                        @click="closeUserModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" :disabled="selectedUser.processing">
                        <span v-if="selectedUser.processing" class="loading loading-spinner loading-xs"></span>
                        <Icon v-else icon="material-symbols:save-rounded" class="text-lg" />
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </Modal>

    <div class="flex flex-col gap-3">
        <!-- Breadcrumbs -->
        <Breadcrumbs :items="[
            { label: 'Home', route: 'main' },
            { label: 'Manage Users', route: 'approver.manage.user', active: true },
        ]" />

        <!-- Page Heading -->
        <div>
            <h1 class="text-lg font-bold">Manage Users</h1>
            <p class="text-xs opacity-50 mt-0.5">Update user information, change passwords, and manage roles and unit
                assignments.</p>
        </div>

        <!-- Summary Cards -->
        <div class="stats stats-horizontal shadow-xs flex-wrap">
            <Card title="Total Users" :value="users.length" />
            <Card title="Active" :value="activeUsers" />
            <Card title="Inactive" :value="inactiveUsers" />
        </div>

        <!-- User Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="user in users" :key="user.id"
                class="group relative card bg-base-100 border border-base-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-primary/5 hover:border-primary/20 overflow-hidden">

                <div
                    class="absolute top-0 left-0 w-full h-1 bg-primary scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left">
                </div>

                <div class="card-body p-5">
                    <div class="flex items-start gap-4">
                        <div class="relative shrink-0">
                            <div :class="[
                                'w-14 h-14 rounded-2xl flex items-center justify-center overflow-hidden bg-base-200 border-2 transition-colors duration-300',
                                user.active ? 'border-success/20 group-hover:border-success/50' : 'border-base-300'
                            ]">
                                <img v-if="user.avatar_url" :src="user.avatar_url" alt="Avatar"
                                    class="w-full h-full object-cover" />
                                <Icon v-else icon="solar:user-bold-duotone" class="w-8 h-8 opacity-40" />
                            </div>
                            <div :class="[
                                'absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-base-100',
                                user.active ? 'bg-success' : 'bg-error'
                            ]"></div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h2
                                        class="font-bold text-sm text-base-content group-hover:text-primary transition-colors duration-300 truncate">
                                        {{ user.name }}
                                    </h2>
                                    <p class="text-[11px] font-medium opacity-60 truncate">{{ user.email }}</p>
                                </div>
                            </div>

                            <div class="mt-2">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase bg-base-200 text-base-content/70 group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                    {{ user.role }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="my-4 border-t border-base-200/60"></div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between text-[11px]">
                            <div class="flex items-center gap-1.5 opacity-60">
                                <Icon icon="solar:card-2-linear" class="text-sm" />
                                <span>ID: {{ user.employeeid ?? '—' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-60">
                                <Icon icon="solar:calendar-date-linear" class="text-sm" />
                                <span>{{ new Date(user.created_at).toLocaleDateString() }}</span>
                            </div>
                        </div>

                        <button @click="handleSelectedUser(user)"
                            class="btn btn-sm btn-ghost bg-base-200/50 hover:bg-primary hover:text-primary-content border-none mt-2 w-full transition-all duration-300 group-hover:shadow-md">
                            <Icon icon="solar:pen-new-square-linear" class="w-4 h-4" />
                            Edit Member
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Breadcrumbs from '../Components/Breadcrumbs.vue'
import Card from '../Components/Card.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Icon } from "@iconify/vue"
import { ref, inject, computed } from "vue"
import Modal from '../Components/Modal.vue'
import TextInput from '../Components/TextInput.vue'
import SelectOption from '../Components/SelectOption.vue'

const displayUserModal = ref(null)
const toast = inject('toast')

const props = defineProps({
    user: Object,
    avatar_url: String,
    errors: Object,
    flash: Object,
    auth: Object,
    users: Object,
    units: Array,
})

const unitsList = ref([])

props.units.forEach(unit => {
    unitsList.value.push({
        label: unit.unit_path,
        value: unit.id
    })
})

const activeUsers = computed(() => props.users.filter(u => u.active).length)
const inactiveUsers = computed(() => props.users.filter(u => !u.active).length)

const selectedUser = useForm({
    id: null,
    active: null,
    email: null,
    organization_unit_id: null,
    role: null,
    name: null,
    employeeid: null,
    new_password: null,
    new_password_confirmation: null,
})

const handleSelectedUser = (data) => {
    selectedUser.id = data.id
    selectedUser.active = data.active
    selectedUser.email = data.email
    selectedUser.organization_unit_id = data.organization_unit_id
    selectedUser.role = data.role
    selectedUser.name = data.name
    selectedUser.employeeid = data.employeeid

    displayUserModal.value?.open()
}

const closeUserModal = () => {
    displayUserModal.value?.close()
    selectedUser.reset()
}

const updateUserProfile = () => {
    selectedUser.post(route('approver.update.user'), {
        preserveState: true,
        onSuccess: () => {
            closeUserModal()
            selectedUser.reset()
            toast('Profile updated successfully', 'success')
        },
        onError: () => {
            toast('Updating profile failed. Please try again', 'error')
        }
    })
}
</script>