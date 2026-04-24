<template>

  <Head title="For Filing list" />
  <Modal ref="bulkActionModal" title="Bulk File Requests" width="w-lg">
    <div class="flex flex-col gap-4">
      <div class="alert alert-info px-3 py-2 rounded text-sm gap-2">
        <Icon icon="material-symbols:info-outline" width="20" height="20" />
        <span>You are about to file <strong>{{ selectedRequests.length }}</strong> approved request(s) totaling
          <strong>{{ selectedHours }}</strong> hours.</span>
      </div>

      <div class="text-sm font-semibold flex justify-between items-center px-1">
        <span>Request Overview</span>
        <span class="badge badge-sm badge-primary">{{ selectedRequests.length }} request(s)</span>
      </div>

      <div class="overflow-y-auto max-h-52 border border-base-300 rounded-lg">
        <table class="table table-sm table-zebra w-full">
          <thead class="sticky top-0 bg-base-200">
            <tr>
              <th class="text-xs">Employee</th>
              <th class="text-xs">Date</th>
              <th class="text-xs text-right">Hours</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="request in selectedRequests" :key="request.id">
              <td class="text-sm">{{ request.user.name }}</td>
              <td class="text-sm">{{ request.schedule.date }}</td>
              <td class="text-sm text-right font-semibold">{{ request.overtime.hours }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-end gap-2 mt-2">
        <button class="btn btn-sm btn-neutral" @click="closeBulkActionModal()" :disabled="bulkForm.processing">
          Cancel
        </button>
        <button class="btn btn-sm btn-primary" @click="executeBulkAction()" :disabled="bulkForm.processing">
          <span v-if="bulkForm.processing" class="loading loading-spinner loading-xs"></span>
          <span>Yes, File</span>
        </button>
      </div>
    </div>
  </Modal>

  <OvertimeRequestDetailModal ref="manageRequestModal" :user="user" :schedule="schedule" :overtime="overtime"
    :processing="overtimeRequestForm.processing" :current-action="overtimeRequestForm.update_status"
    v-model:remarks-model="overtimeRequestForm.remarks"
    :remarks-error="overtimeRequestForm.errors?.remarks"
    :is-remarks-disabled="['FILED', 'DECLINED', 'CANCELED'].includes(overtime.status)"
    :remarks-placeholder="['FILED', 'DECLINED', 'APPROVED'].includes(overtime.status) ? '' : 'Enter any remarks regarding this request...'"
    @close="closeManageRequestModal()" @action="updateOvertiemRequestStatus" />
  <div class="flex flex-col gap-6">
    <!-- Breadcrumbs -->
    <Breadcrumbs :items="[
      { label: 'Dashboard', route: 'main', params: { week: selectedWeek, year: selectedYear } },
      { label: 'Pending', route: 'overtime.pending', params: { status: 'PENDING', page: 'Approver/Pending', week: selectedWeek, year: selectedYear } },
      { label: 'Filing', route: 'overtime.filing', params: { status: 'APPROVED', page: 'Approver/Filing', week: selectedWeek, year: selectedYear }, active: true },
      { label: 'Filed', route: 'overtime.filing', params: { status: 'FILED', page: 'Approver/Filed', week: selectedWeek, year: selectedYear } },
    ]" />

    <!-- Page Title -->
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-base-content">For Filing Overtime Requests</h1>
    </div>

    <div class="stats stats-horizontal shadow-xs flex-wrap">
      <Card title="Requests to File" :value="total_requests" description="Approved but not yet filed" />
      <Card title="Total Overtime Hours" :value="total_requests_hours" description="Awaiting confirmation" />
    </div>

    <!-- Filing Table -->
    <div class="card bg-base-100 shadow-xs">
      <div class="card-body">
        <div class="flex justify-between mb-4">
          <h2 class="card-title">Approved Requests Awaiting Filing</h2>
          <div class="flex flex-row flex-end gap-4 w-1/4">
            <SelectOption :options="years" v-model="selectedYear" margin='' @change="handleWeekSelection()" />
            <SelectOption :options="weeks" v-model="selectedWeek" margin='' @change="handleWeekSelection()" />
          </div>
        </div>

        <div class="overflow-x-auto min-h-48 max-h-[50vh]">
          <table class="table table-zebra w-full">
            <thead class="sticky top-0 bg-base-300 z-10 rounded">
              <tr>
                <th class="w-10">
                  <input type="checkbox" class="checkbox checkbox-sm checkbox-primary"
                    :checked="isAllSelected" :indeterminate="isIndeterminate"
                    @change="toggleAll($event.target.checked)" />
                </th>
                <th>Employee ID</th>
                <th>Employee</th>
                <th>Date</th>
                <th>Week</th>
                <th>Hours</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="requests.length === 0">
                <td colspan="7" class="text-center h-48 italic text-gray-400 py-4">
                  No Awaiting Filing(s)
                </td>
              </tr>
              <tr v-for="request in requests" :key="request.id">
                <td>
                  <input type="checkbox" class="checkbox checkbox-sm checkbox-primary"
                    :checked="selectedIds.includes(request.id)"
                    @change="toggleSelect(request.id)" />
                </td>
                <td>{{ request.user.employee_id }}</td>
                <td>{{ request.user.name }}</td>
                <td>{{ request.schedule.date }}</td>
                <td>{{ request.schedule.week }}</td>
                <td>{{ request.overtime.hours }}</td>
                <td class="flex gap-2 justify-center">
                  <button class="btn btn-xs text-sm btn-primary"
                    @click="openManageRequestModal(request)">Manage</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="requests.length > 0" class="flex justify-between items-center px-1">
      <span class="text-sm opacity-70">
        {{ selectedIds.length }} of {{ requests.length }} selected
        <template v-if="selectedIds.length > 0">
          &mdash; {{ selectedHours }} hour(s)
        </template>
      </span>
      <div class="tooltip tooltip-left" data-tip="Mark as Filed">
        <button class="btn btn-sm btn-primary gap-1"
          :disabled="selectedIds.length === 0 || bulkForm.processing"
          @click="openBulkActionModal()">
          <span v-if="bulkForm.processing" class="loading loading-spinner loading-xs"></span>
          <Icon icon="material-symbols:task-outline" width="18" height="18" />
          File Selected
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, inject, watch, computed } from 'vue'
import Card from '../Components/Card.vue'
import SelectOption from '../Components/SelectOption.vue'
import { weeks, years, currentWeek } from '../utils/dropdownOptions.js'
import { useBulkSelection } from '../composables/useBulkSelection.js'
import Modal from '../Components/Modal.vue'
import Breadcrumbs from '../Components/Breadcrumbs.vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import OvertimeRequestDetailModal from '../Components/OvertimeRequestDetailModal.vue'


const toast = inject('toast')

const props = defineProps({
  info: Object,
  success: Boolean,
  message: String
})

const selectedWeek = ref(props?.info?.payload?.week)
const selectedYear = ref(props?.info?.payload?.year)
const requests = ref([...props?.info?.requests ?? []])
const { selectedIds, selectedRequests, selectedHours, isAllSelected, isIndeterminate, toggleSelect, toggleAll, clearSelection } = useBulkSelection(requests)

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

const overtimeRequestForm = useForm({
  id: '',
  current_status: '',
  update_status: '',
  remarks: ''
})

const bulkForm = useForm({
  ids: [],
  update_status: ''
})

// ===== Watchers =====

watch(() => props?.info?.requests, (updatedRequest) => {
  requests.value = [...updatedRequest]
  clearSelection()
})

watch(() => props.info.payload.week, (newWeek) => {
  selectedWeek.value = newWeek
})

watch(() => props.info.payload.year, (newYear) => {
  selectedYear.value = newYear
})



const manageRequestModal = ref(null)

const openManageRequestModal = (data) => {
  manageRequestModal.value?.open()

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

  overtimeRequestForm.id = data?.id
  overtimeRequestForm.current_status = data?.overtime?.status,
    overtimeRequestForm.remarks = data?.overtime?.remarks
}

const closeManageRequestModal = () => {
  manageRequestModal.value?.close()
}


// === Requests ===

const updateOvertiemRequestStatus = (status) => {
  if (status && overtimeRequestForm.id) {
    overtimeRequestForm.update_status = status
    overtimeRequestForm.post(route('overtime.update.approver'), {
      onSuccess: () => {
        overtimeRequestForm.reset()
        closeManageRequestModal()
        setTimeout(() => {
          toast(`Overtime request has been ${status}`, 'success')
        }, 200);
      },
      onError: (errors) => {
        toast('Failed to update schedule. Please try again', 'error')
        console.log(errors)
      }
    })
  } else {
    toast('Failed to update schedule. Please try again', 'error')
  }
}


const bulkActionModal = ref(null)

const openBulkActionModal = () => {
  bulkForm.ids = [...selectedIds.value]
  bulkForm.update_status = 'FILED'
  bulkActionModal.value?.open()
}

const closeBulkActionModal = () => {
  bulkActionModal.value?.close()
}

const executeBulkAction = () => {
  bulkForm.post(route('overtime.update.bulk'), {
    onSuccess: () => {
      const count = bulkForm.ids.length
      bulkForm.reset()
      selectedIds.value = []
      closeBulkActionModal()
      setTimeout(() => {
        toast(`${count} request(s) have been filed`, 'success')
      }, 200)
    },
    onError: (errors) => {
      toast('Bulk update failed. Please refresh and try again.', 'error')
      console.log(errors)
    }
  })
}

const handleWeekSelection = () => {
  router.get(route('overtime.filing'), {
    year: selectedYear.value,
    week: selectedWeek.value,
    status: 'APPROVED',
    page: 'Approver/Filing'
  }, {
    preserveState: true
  })
}
</script>
