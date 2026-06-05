import { ref } from 'vue';
import { enhanceReasonWithAI } from '../services/ai.js';

export const originalReason = ref('')
export const enhanceCooldown = ref(0)

let cooldownTimer = null

/**
 * @typedef {Object} OvertimeRequestForm
 * @property {string} id
 * @property {string} employee_schedule_id
 * @property {string} date
 * @property {string} created_at
 * @property {string} week
 * @property {string} hours
 * @property {string} start_time
 * @property {string} end_time
 * @property {string} current_status
 * @property {string} shift_code
 * @property {string} shift_start_time
 * @property {string} shift_end_time
 * @property {string} update_status
 * @property {string} reason
 * @property {string} remarks
 */

/**
 * Shared logic for enhancing an overtime request reason with AI.
 * @param {Object} form - Inertia useForm instance with .reason, .errors, .reset()
 * @param {import('vue').Ref<boolean>} isEnhancing
 * @returns {Promise<void>}
 */
export async function enhanceReason(form, isEnhancing) {
    if (!form.reason) {
        form.errors.reason = 'Please enter a reason to enhance.';
        return;
    }

    if (form.reason.trim().length === 0) {
        form.errors.reason = 'Please enter a reason to enhance.';
        return;
    }

    const words = form.reason.trim().split(' ');
    if (words.length < 3) {
        form.errors.reason = 'Please provide a more detailed reason (at least 3 words).';
        return;
    }

    delete form.errors.reason;
    originalReason.value = form.reason;
    isEnhancing.value = true;

    if (cooldownTimer) {
        clearInterval(cooldownTimer);
        cooldownTimer = null;
    }

    try {
        const enhanced = await enhanceReasonWithAI(form.reason, (streamedText) => {
            form.reason = streamedText;
        });

        if (enhanced.success) {
            form.reason = enhanced.data;
        } else {
            form.reason = originalReason.value;
            if (enhanced.status === 422) {
                form.errors.reason = { message: enhanced.data, type: 'warning' };
            } else {
                form.errors.reason = 'Failed to enhance reason. Please try again.';
            }
        }
    } finally {
        isEnhancing.value = false;
        enhanceCooldown.value = 5;
        cooldownTimer = setInterval(() => {
            enhanceCooldown.value--;
            if (enhanceCooldown.value <= 0) {
                clearInterval(cooldownTimer);
                cooldownTimer = null;
            }
        }, 1000);
    }
}

/**
 * Restore the reason to its pre-enhancement state.
 * @param {Object} form - Inertia useForm instance
 */
export function undoEnhance(form) {
    if (!originalReason.value) return;
    form.reason = originalReason.value;
    originalReason.value = '';
}

/**
 * Populate a formFilledOvertime Inertia form from request data.
 * @param {Object} form - Inertia useForm instance
 * @param {Object} data - request data object
 * @param {string} data.id
 * @param {string} data.employee_schedule_id
 * @param {string} data.date
 * @param {string} data.created_at
 * @param {string} data.week
 * @param {string} data.hours
 * @param {string} data.start_time
 * @param {string} data.end_time
 * @param {string} data.status
 * @param {string} data.reason
 * @param {string} data.remarks
 * @param {string} data.shift - shift code (Employee/Request.vue) or data.shift_code (Employee/Index.vue)
 * @param {string} data.shift_code
 * @param {string} data.shift_start_time
 * @param {string} data.shift_end_time
 */
export function populateOvertimeForm(form, data) {
    form.id = data.id;
    form.employee_schedule_id = data.employee_schedule_id;
    form.date = data.date;
    form.created_at = data.created_at;
    form.week = data.week;
    form.hours = data.hours;
    form.start_time = data.start_time;
    form.end_time = data.end_time;
    form.current_status = data.status;
    form.reason = data.reason;
    form.remarks = data.remarks;
    form.shift_code = data.shift || data.shift_code;
    form.shift_start_time = data.shift_start_time;
    form.shift_end_time = data.shift_end_time;
}

/**
 * Reset overtime form state.
 * @param {Object} form - Inertia useForm instance
 * @param {import('vue').Ref<boolean>} confirmingCancel
 * @param {import('vue').Ref<boolean>} modeUpdate
 */
export function resetOvertimeForm(form, confirmingCancel, modeUpdate) {
    form.reset();
    confirmingCancel.value = false;
    modeUpdate.value = false;
}

/**
 * Submit a cancellation (or re-submission) of an overtime request.
 * @param {Object} form - Inertia useForm instance with .update_status, .post(), .reset()
 * @param {import('vue').Ref<boolean>} modeUpdate - whether the user is updating (vs cancelling)
 * @param {Function} toast - toast notification function
 * @param {Function} onClose - callback to run on success (resets form, closes modal, etc.)
 */
export function submitCancelation(form, modeUpdate, toast, onClose) {
    if (modeUpdate.value) {
        form.update_status = 'PENDING';
    } else {
        form.update_status = 'CANCELED';
    }

    form.post(route('overtime.update.employee'), {
        onSuccess: () => {
            toast(modeUpdate.value ? 'Updating Successful' : 'Cancelation Successful', 'success');
            onClose();
        },
        onError: () => {
            toast('Cancelation Request failed.', 'error');
        }
    });
}
