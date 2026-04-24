<template>
    <Modal ref="modalRef" :title="title" width="w-md">
        <div>
            <div class="flex flex-col gap-2 w-full">
                <div class="mb-6">
                    <Stepper :status="overtime.status" />
                </div>

                <div class="space-y-6">
                    <!-- Employee Information -->
                    <div class="card border border-base-300 shadow-xs">
                        <div class="card-body p-6">
                            <h3 class="card-title text-base mb-4 flex items-center gap-2">
                                <Icon icon="material-symbols:person-outline" width="20" height="20" />
                                Employee Information
                            </h3>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:badge-outline" width="14" height="14" />
                                        Name
                                    </span>
                                    <span class="font-semibold">{{ user.name }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:mail-outline" width="14" height="14" />
                                        Email
                                    </span>
                                    <span class="font-semibold">{{ user.email }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:user-attributes-rounded" width="14" height="14" />
                                        Employee ID
                                    </span>
                                    <span class="font-semibold">{{ user.employee_id }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:work-outline" width="14" height="14" />
                                        Role
                                    </span>
                                    <span class="font-semibold capitalize">{{ user.role }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registered Schedule -->
                    <div class="card border border-base-300 shadow-xs">
                        <div class="card-body p-6">
                            <h3 class="card-title text-base mb-4 flex items-center gap-2">
                                <Icon icon="material-symbols:schedule-outline" width="20" height="20" />
                                Registered Schedule
                            </h3>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:calendar-today-outline" width="14" height="14" />
                                        Date
                                    </span>
                                    <span class="font-semibold">{{ schedule.date }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:date-range-outline" width="14" height="14" />
                                        Week
                                    </span>
                                    <span class="font-semibold">{{ schedule.week }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:code" width="14" height="14" />
                                        Shift Code
                                    </span>
                                    <span class="font-semibold">{{ schedule.shift_code }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="clarity:employee-line" width="14" height="14" />
                                        Schedule
                                    </span>
                                    <span class="font-semibold">
                                        {{ schedule.shift_start === '--' || schedule.shift_end === '--'
                                            ? 'N/A'
                                            : schedule.shift_start + ' → ' + schedule.shift_end }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Overtime Request -->
                    <div class="card border border-base-300 shadow-xs">
                        <div class="card-body p-6">
                            <h3 class="card-title text-base mb-4 flex items-center gap-2">
                                <Icon icon="material-symbols:timer-outline" width="20" height="20" />
                                Overtime Request
                            </h3>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:event-available-outline" width="14" height="14" />
                                        Filing Date
                                    </span>
                                    <span class="font-semibold">{{ overtime.created_at }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:schedule" width="14" height="14" />
                                        Time
                                    </span>
                                    <span class="font-semibold">{{ overtime.start_time }} → {{ overtime.end_time }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:hourglass-empty" width="14" height="14" />
                                        Hours
                                    </span>
                                    <span class="font-semibold text-lg">{{ overtime.hours }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:info-outline" width="14" height="14" />
                                        Status
                                    </span>
                                    <div class="badge badge-lg gap-2" :class="getStatusBadgeClass(overtime.status)">
                                        {{ overtime.status }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="card border border-base-300 shadow-xs">
                        <div class="card-body p-6">
                            <h3 class="card-title text-base mb-3 flex items-center gap-2">
                                <Icon icon="material-symbols:description-outline" width="20" height="20" />
                                Reason
                            </h3>
                            <p class="opacity-80 leading-relaxed">{{ overtime.reason }}</p>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="card border border-base-300 shadow-xs">
                        <div class="card-body p-6">
                            <h3 class="card-title text-base mb-4 flex items-center gap-2">
                                <Icon icon="material-symbols:comment-outline" width="20" height="20" />
                                Remarks
                            </h3>
                            <template v-if="readOnly">
                                <p v-if="overtime.remarks" class="opacity-80 leading-relaxed">{{ overtime.remarks }}</p>
                                <p v-else class="opacity-40 italic text-sm">No remarks provided.</p>
                            </template>
                            <TextArea v-else type="text" :model-value="remarksModel"
                                @update:model-value="$emit('update:remarksModel', $event)"
                                :message="remarksError" :disabled="isRemarksDisabled"
                                :placeholder="remarksPlaceholder" />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="divider"></div>
                    <div class="flex justify-between gap-4">
                        <div>
                            <button class="btn btn-neutral" :disabled="processing" @click="$emit('close')">
                                {{ readOnly ? 'CLOSE' : 'CLOSE' }}
                            </button>
                        </div>
                        <div v-if="!readOnly">
                            <div v-if="overtime.status === 'PENDING'" class="flex flex-end gap-2">
                                <button class="btn btn-secondary" :disabled="processing" @click="$emit('action', 'DISAPPROVED')">
                                    <span v-if="processing && currentAction === 'DISAPPROVED'" class="loading loading-spinner"></span>
                                    <span>DISAPPROVE</span>
                                </button>
                                <button class="btn btn-primary" :disabled="processing" @click="$emit('action', 'APPROVED')">
                                    <span v-if="processing && currentAction === 'APPROVED'" class="loading loading-spinner"></span>
                                    <span>APPROVE</span>
                                </button>
                            </div>
                            <div v-if="overtime.status === 'APPROVED'" class="flex flex-end gap-2">
                                <button class="btn btn-secondary" :disabled="processing" @click="$emit('action', 'DECLINED')">
                                    <span v-if="processing && currentAction === 'DECLINED'" class="loading loading-spinner"></span>
                                    <span>DECLINE</span>
                                </button>
                                <button class="btn btn-primary" :disabled="processing" @click="$emit('action', 'FILED')">
                                    <span v-if="processing && currentAction === 'FILED'" class="loading loading-spinner"></span>
                                    <span>FILE</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { ref } from 'vue'
import Modal from './Modal.vue'
import Stepper from './Stepper.vue'
import TextArea from './TextArea.vue'
import { Icon } from '@iconify/vue'
import { getStatusBadgeClass } from '../utils/helpers/status.js'

const props = defineProps({
    /** @type {{ name: string, employee_id: string, role: string, email: string }} */
    user: { type: Object, required: true },
    /** @type {{ date: string, shift_code: string, shift_start: string, shift_end: string, week: string }} */
    schedule: { type: Object, required: true },
    /** @type {{ start_time: string, end_time: string, hours: string, status: string, created_at: string, reason: string, remarks: string }} */
    overtime: { type: Object, required: true },
    title: { type: String, default: 'Manage Overtime Request' },
    readOnly: { type: Boolean, default: false },
    processing: { type: Boolean, default: false },
    currentAction: { type: String, default: '' },
    remarksModel: { type: String, default: '' },
    remarksError: { type: [String, Object], default: '' },
    isRemarksDisabled: { type: Boolean, default: true },
    remarksPlaceholder: { type: String, default: '' },
})

defineEmits(['close', 'action', 'update:remarksModel'])

const modalRef = ref(null)

function open() {
    modalRef.value?.open()
}

function close() {
    modalRef.value?.close()
}

defineExpose({ open, close })
</script>
