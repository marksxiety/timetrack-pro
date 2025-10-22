<template>
    <div class="bg-base-300">
        <div
            class="flex justify-between items-center bottom-2 shadow-md px-4 py-2 bg-base-100 rounded h-14 sticky top-0 z-10">
            <Link :href="route('main')" class="font-bold text-sm">TimeTrack Pro</Link>
            <div v-if="$page?.props?.auth.user" class="flex justify-between items-center gap-2 font-semibold">
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button"
                        class="flex gap-2 text-sm items-center hover:bg-base-300 py-2 px-4 rounded-3xl">
                        <Icon icon="tabler:clock-code" width="24" height="24" />
                        Operations
                    </div>
                    <ul tabindex="0"
                        class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-40 p-2 shadow">
                        <li v-if="$page?.props?.auth.user?.role === 'approver'">
                            <Link :href="route('shifts')" class="justify-between">
                            <Icon icon="jam:code" width="24" height="24" />
                            Shift Codes
                            </Link>
                        </li>
                        <li v-if="$page?.props?.auth.user?.role === 'approver'">
                            <Link :href="route('schedule.manage')" class="justify-between">
                            <Icon icon="icon-park-outline:schedule" width="24" height="24" />
                            Schedule
                            </Link>
                        </li>
                        <li v-if="$page?.props?.auth.user?.role === 'approver'">
                            <Link :href="route('hours')" class="justify-between">
                            <Icon icon="tabler:clock-check" width="24" height="24" />
                            Manage ROA
                            </Link>
                        </li>
                        <li v-if="$page?.props?.auth.user?.role === 'approver'">
                            <Link :href="route('approver.generate.report')" class="justify-between">
                            <Icon icon="mdi:report-box-multiple-outline" width="24" height="24" />
                            Generate Report
                            </Link>
                        </li>
                        <li v-if="$page?.props?.auth.user?.role === 'approver'">
                            <Link :href="route('approver.manage.user')" class="justify-between">
                            <Icon icon="material-symbols:manage-accounts-rounded" width="24" height="24" />
                            Manage Users
                            </Link>
                        </li>
                        <li v-if="$page?.props?.auth.user?.role === 'employee'">
                            <Link :href="route('schedule')" class="justify-between">
                            <Icon icon="mingcute:schedule-line" width="24" height="24" />
                            Schedule
                            </Link>
                        </li>
                    </ul>
                </div>
                <div v-if="$page?.props?.auth.user" class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="flex gap-2 text-sm items-center py-2 px-4 rounded-3xl">
                        <div class="avatar">
                            <div
                                class="w-8 h-8 rounded-full ring-2 ring-offset-2 ring-primary ring-offset-base-100 overflow-hidden flex items-center justify-center">
                                <img v-if="props.auth.user?.avatar_url" :src="props.auth.user?.avatar_url" alt="avatar"
                                    class="w-full h-full object-cover rounded-full" />
                                <div v-else class="flex items-center justify-center p-1 w-full h-full">
                                    <Icon
                                        :icon="($page?.props?.auth.user?.role === 'employee') ? 'iconamoon:profile-circle-fill' : 'eos-icons:admin-outlined'"
                                        width="24" height="24" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul tabindex="0"
                        class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-40 p-2 shadow">
                        <li v-if="$page?.props?.auth.user?.role === 'employee'">
                            <Link :href="route('profile.employee')" class="justify-between px-4 py-2">
                            <Icon icon="iconamoon:profile-circle-fill" width="24" height="24" />
                            Profile
                            </Link>
                        </li>
                        <li>
                            <label class="swap swap-rotate justify-between px-4 py-2 ">
                                Theme
                                <input type="checkbox" class="theme-controller" value="light" v-model="isLight"
                                    @change="toggleTheme()" />
                                <svg class="swap-off h-6 w-6 fill-current" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" />
                                </svg>
                                <svg class="swap-on h-6 w-6 fill-current" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z" />
                                </svg>
                            </label>
                        </li>
                        <li>
                            <Link :href="route('logout')" method="post" class="justify-between px-4 py-2">
                            <Icon icon="ic:round-logout" width="24" height="24" />
                            Logout
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <main class="w-full px-20 box-border min-h-screen bg-base-300 mb-0">
            <Toast ref="toastRef" />
            <slot></slot>
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import Toast from '../Pages/Components/Toast.vue'
import { ref, provide, onMounted } from 'vue'
import { Icon } from "@iconify/vue"
import { theme, setTheme } from '../Pages/utils/themeStore.js'

const toastRef = ref()

provide('toast', (msg, type = 'info') => {
    toastRef.value?.showToast(msg, type)
})

const props = defineProps({
    user: Object,
    avatar_url: String,
    errors: Object,
    flash: Object,
    auth: Object,
})

const isLight = ref(theme.value === 'light')

const toggleTheme = () => {
    const newTheme = isLight.value ? 'light' : 'dark'
    setTheme(newTheme)
}

onMounted(() => {
    setTheme(theme.value)
})

</script>