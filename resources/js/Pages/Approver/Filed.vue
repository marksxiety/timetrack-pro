<template>

  <Head title="Filed List" />
  <OvertimeRequestDetailModal ref="manageRequestModal" :user="user" :schedule="schedule" :overtime="overtime"
    title="Overtime Request Details" :read-only="true" @close="closeManageRequestModal()" />
  <div class="flex flex-col gap-6">
    <!-- Breadcrumbs -->
    <Breadcrumbs :items="[
      { label: 'Dashboard', route: 'main', params: { week: selectedWeek, year: selectedYear } },
      { label: 'Pending', route: 'overtime.pending', params: { status: 'PENDING', page: 'Approver/Pending', week: selectedWeek, year: selectedYear } },
      { label: 'Filing', route: 'overtime.filing', params: { status: 'APPROVED', page: 'Approver/Filing', week: selectedWeek, year: selectedYear } },
      { label: 'Filed', route: 'overtime.filing', params: { status: 'FILED', page: 'Approver/Filed', week: selectedWeek, year: selectedYear }, active: true },
    ]" />


    <!-- Page Title -->
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-base-content">Filed Overtime Requests</h1>
    </div>

    <div class="stats stats-horizontal shadow-xs flex-wrap">
      <Card title="Filed Requests" :value="total_requests" description="Successfully filed" />
      <Card title="Total Overtime Hours" :value="total_requests_hours" description="Completed hours" />
    </div>

    <!-- Filed Table -->
    <div class="card bg-base-100 shadow-xs">
      <div class="card-body">
        <div class="flex justify-between mb-4">
          <h2 class="card-title">Filed Overtime Requests</h2>
          <div class="flex flex-row flex-end gap-4 w-1/4">
            <SelectOption :options="years" v-model="selectedYear" margin='' @change="handleWeekSelection()" />
            <SelectOption :options="weeks" v-model="selectedWeek" margin='' @change="handleWeekSelection()" />
          </div>
        </div>

        <div class="overflow-x-auto min-h-48 max-h-[50vh]">
          <table class="table table-zebra w-full">
            <thead class="sticky top-0 bg-base-300 z-10 rounded">
              <tr>
                <th>Employee ID</th>
                <th>Employee</th>
                <th>Date</th>
                <th>Week</th>
                <th>Hours</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="requests.length === 0">
                <td colspan="5" class="text-center h-48 italic text-gray-400 py-4">
                  No filed request(s)
                </td>
              </tr>
              <tr v-for="request in requests" :key="request.id" class="hover cursor-pointer"
                @click="openManageRequestModal(request)">
                <td>{{ request.user.employee_id }}</td>
                <td>{{ request.user.name }}</td>
                <td>{{ request.schedule.date }}</td>
                <td>{{ request.schedule.week }}</td>
                <td>{{ request.overtime.hours }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import Card from '../Components/Card.vue'
import SelectOption from '../Components/SelectOption.vue'
import OvertimeRequestDetailModal from '../Components/OvertimeRequestDetailModal.vue'
import { weeks, years, currentWeek } from '../utils/dropdownOptions.js'
import Breadcrumbs from '../Components/Breadcrumbs.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  info: Object,
  success: Boolean,
  message: String
})

const selectedWeek = ref(props?.info?.payload?.week)
const selectedYear = ref(props?.info?.payload?.year)
const requests = ref([...props?.info?.requests ?? []])

const total_requests = computed(() => {
  return props?.info?.requests.length
})

const total_requests_hours = computed(() => {
  return props.info.requests.reduce((sum, r) => sum + (r.overtime?.hours ?? 0), 0).toFixed(2)
})

const user = ref({
  name: '',
  employee_id: '',
  role: '',
  email: ''
})

const schedule = ref({
  date: '',
  shift_code: '',
  shift_start: '',
  shift_end: '',
  week: '',
})

const overtime = ref({
  start_time: '',
  end_time: '',
  hours: '',
  status: '',
  created_at: '',
  reason: '',
  remarks: '',
})

// ===== Watchers =====

watch(() => props?.info?.requests, (updatedRequest) => {
  requests.value = [...updatedRequest]
})

watch(() => props.info.payload.week, (newWeek) => {
  selectedWeek.value = newWeek
})

watch(() => props.info.payload.year, (newYear) => {
  selectedYear.value = newYear
})

const manageRequestModal = ref(null)

const openManageRequestModal = (data) => {
  user.value = {
    name: data?.user?.name,
    employee_id: data?.user?.employee_id,
    role: data?.user?.role,
    email: data?.user?.email
  }

  schedule.value = {
    date: data?.schedule?.date,
    shift_code: data?.schedule?.shift_code,
    shift_start: data?.schedule?.shift_start,
    shift_end: data?.schedule?.shift_end,
    week: data?.schedule?.week,
  }

  overtime.value = {
    start_time: data?.overtime?.start_time,
    end_time: data?.overtime?.end_time,
    hours: data?.overtime?.hours,
    status: data?.overtime?.status,
    created_at: data?.overtime?.created_at,
    reason: data?.overtime?.reason,
    remarks: data?.overtime?.remarks,
  }

  manageRequestModal.value?.open()
}

const closeManageRequestModal = () => {
  manageRequestModal.value?.close()
}

const handleWeekSelection = () => {
  router.get(route('overtime.filing'), {
    year: selectedYear.value,
    week: selectedWeek.value,
    status: 'FILED',
    page: 'Approver/Filed'
  }, {
    preserveState: true
  })
}
</script>
