<template>
    <div class="min-h-screen">
        <div class="container mx-auto px-4 py-6 max-w-7xl">
            <!-- Breadcrumbs -->
            <div class="breadcrumbs text-sm mb-2">
                <ul>
                    <li>
                        <Link :href="route('main')" class="hover:text-primary transition-colors">
                        Home
                        </Link>
                    </li>
                    <li class="font-semibold">Overtime Request</li>
                </ul>
            </div>

            <!-- Main Card -->
            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body p-6 lg:p-8">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-base-content">Overtime Requests</h1>
                            <p class="text-base-content/60 text-sm mt-1">Track and manage your overtime submissions</p>
                        </div>
                    </div>

                    <!-- Filters Section -->
                    <div class="bg-base-200 rounded-xl p-4 mb-6">
                        <div class="flex flex-col lg:flex-row gap-3">
                            <div class="flex-1">
                                <TextInput name="Search" type="text" v-model="searchValue"
                                    :placeholder="'Search by reason, remarks, date, week...'" margin=""
                                    class="w-full input-bordered" />
                            </div>
                            <div class="flex-none w-full lg:w-48">
                                <SelectOption name="Week" :options="weeks" v-model="selectedWeek" margin=""
                                    class="select-bordered w-full" />
                            </div>
                            <div class="flex-none w-full lg:w-48">
                                <SelectOption name="Status" :options="statuses" v-model="selectedStatus" margin=""
                                    class="select-bordered w-full" />
                            </div>
                            <div class="flex-none self-end">
                                <button
                                    class="btn btn-primary gap-2 w-full lg:w-auto shadow-md hover:shadow-lg transition-shadow"
                                    @click="applyFilter">
                                    <Icon icon="proicons:filter" width="20" height="20" />
                                    <span>Apply</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="overflow-x-auto rounded-lg border border-base-300 bg-base-100">
                        <table class="table w-full min-h-96">
                            <thead class="bg-base-200">
                                <tr>
                                    <th class="font-bold text-sm">Date</th>
                                    <th class="font-bold text-sm text-center">Shift Code</th>
                                    <th class="font-bold text-sm text-center">Shift Time Range</th>
                                    <th class="font-bold text-sm">Week</th>
                                    <th class="font-bold text-sm">Hours</th>
                                    <th class="font-bold text-sm">Reason</th>
                                    <th class="font-bold text-sm">Remarks</th>
                                    <th class="text-center font-bold text-sm">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!requests || requests.length === 0">
                                    <td colspan="6" class="text-center py-12">
                                        <div class="flex flex-col items-center gap-3">
                                            <Icon icon="tabler:inbox-off" width="48" height="48"
                                                class="text-base-content/30" />
                                            <p class="text-base-content/50 font-medium">No overtime requests found</p>
                                            <p class="text-base-content/40 text-sm">Try adjusting your filters</p>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-else v-for="req in requests" :key="req.id" class="hover:bg-base-200">
                                    <td class="font-medium">{{ req.date }}</td>
                                    <td class="font-medium text-center">{{ req.shift }}</td>
                                    <td class="font-medium text-center">{{ req.shift_start_time }} - {{
                                        req.shift_end_time }}</td>
                                    <td>
                                        <div class="badge badge-ghost badge-sm">{{ req.week }}</div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1">
                                            <Icon icon="tabler:clock" width="16" height="16"
                                                class="text-base-content/60" />
                                            <span class="font-semibold">{{ req.hours }}</span>
                                        </div>
                                    </td>
                                    <td class="max-w-xs">
                                        <div class="tooltip tooltip-left tooltip-break"
                                            :data-tip="req.reason?.length > 80 ? req.reason : ''">
                                            <p class="line-clamp-2 text-sm text-base-content/80 break-words">
                                                {{ req.reason }}
                                            </p>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="text-sm"
                                            :class="req.remarks ? 'text-base-content/80' : 'text-base-content/40 italic'">
                                            {{ req.remarks ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="badge badge-sm font-semibold gap-1" :class="{
                                            'badge-primary': req.status === 'FILED',
                                            'badge-warning': req.status === 'PENDING',
                                            'badge-success': req.status === 'APPROVED',
                                            'badge-error': req.status === 'DECLINED' || req.status === 'DISAPPROVED' || req.status === 'CANCELED',
                                        }">
                                            <Icon :icon="getStatusIcon(req.status)" width="14" height="14" />
                                            {{ req.status }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-2">
                        <PaginationLinks :paginator="paginator" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { weeks, statuses } from '../utils/dropdownOptions.js'
import SelectOption from '../Components/SelectOption.vue'
import TextInput from '../Components/TextInput.vue'
import PaginationLinks from '../Components/PaginationLinks.vue'
import { Icon } from "@iconify/vue"

const props = defineProps({
    info: Object,
    payload: Object,
    errors: Object,
    flash: Object,
    success: Boolean,
    message: String,
    auth: Object,
})

const selectedWeek = ref(props.payload?.week ?? '')
const selectedStatus = ref(props.payload?.status ?? '')
const searchValue = ref(props.payload?.search ?? '')


const paginator = ref(props.info?.requests ?? { data: [], links: [] })
const requests = ref(paginator.value.data ?? [])

// Watch for props changes and update local data
watch(() => props.info?.requests, (newRequests) => {
    if (newRequests) {
        paginator.value = newRequests
        requests.value = newRequests.data || []
    }
}, { immediate: true })

const handleFilter = () => {
    paginator.value = {
        ...paginator.value,
        filters: {
            week: selectedWeek.value,
            status: selectedStatus.value,
            search: searchValue.value
        }
    }
}

const fetchRequests = () => {
    router.get(route('overtime.requests.employee'), {
        week: selectedWeek.value,
        status: selectedStatus.value,
        search: searchValue.value
    }, {
        preserveState: true,
        onSuccess: (page) => {
            paginator.value = page.props.info.requests
            requests.value = paginator.value.data
        }
    })
}

const getStatusIcon = (status) => {
    const icons = {
        'FILED': 'tabler:file-text',
        'CANCELED': 'tabler:x',
        'PENDING': 'tabler:clock',
        'APPROVED': 'tabler:check',
        'DECLINED': 'tabler:x',
        'DISAPPROVED': 'tabler:x'
    };
    return icons[status] || 'tabler:circle';
};

const applyFilter = () => {
    handleFilter()
    fetchRequests()
}

</script>

<style scoped>
.tooltip-break::before {
    white-space: normal !important;
    word-break: break-word;
    width: 16rem;
    text-align: center;
}
</style>