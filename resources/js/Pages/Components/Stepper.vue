<template>
    <ul class="steps w-full mb-6 bg-base-100">
        <li :class="['step text-sm', `step-${identifyColorStatus(status)}`]">
            {{ status === 'CANCELED' ? 'CANCELED' : 'PENDING' }}</li>
        <li
            :class="['step text-sm break-normal', ['APPROVED', 'FILED', 'DISAPPROVED'].includes(status) ? `step-${identifyColorStatus(status)} ` : '']">
            {{
                status === 'FILED' ? 'APPROVED' :
                    (['APPROVED', 'DISAPPROVED'].includes(status) ? status : 'FOR APPROVAL')
            }}
        </li>
        <li :class="['step text-sm', status === 'FILED' ? `step-${identifyColorStatus(status)} ` : '']">
            FILED
        </li>
    </ul>
</template>

<script setup>
import { identifyColorStatus } from '../utils/colorIdentifier.js'
defineProps({
    status: {
        type: [Object, String],
        required: true
    }
})
</script>