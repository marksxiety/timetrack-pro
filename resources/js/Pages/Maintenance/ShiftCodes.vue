<template>
    <Head title="Manage Shift Codes"  />
    <Modal ref="modalRef">
        <h2 class="text-lg font-bold mb-4">Are you sure you want to delete?</h2>
        <div class="flex justify-end gap-4">
            <button class="btn btn-sm btn-primary mt-4" @click="handleDeletion()" :disabled="deleteform.processing">
                <span v-if="deleteform.processing" class="loading loading-spinner"></span>
                <span>Confirm</span>
            </button>
            <button class="btn btn-sm btn-secondary mt-4" @click="closeModal"
                :disabled="deleteform.processing">Cancel</button>
        </div>
    </Modal>
    <div class="flex flex-col gap-8 h-full">
        <!-- Breadcrumbs -->
        <div class="breadcrumbs text-sm">
            <ul>
                <li>
                    <Link :href="route('main')">Home</Link>
                </li>
                <li>
                    <Link :href="route('shifts')">Shift Code Registration</Link>
                </li>
            </ul>
        </div>

        <!-- Page Heading -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-base-content">Manage Shift Codes</h1>
        </div>

        <!-- Grid Section -->
        <div class="grid place-items-center min-h-[50vh]">
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-8 h-[32rem] w-full max-w-7xl">

                <!-- Form Panel -->
                <div class="col-span-2">
                    <div
                        class="bg-base-100 p-8 rounded-md shadow-lg h-full flex flex-col justify-center border border-base-200">
                        <!-- Title -->
                        <h2 class="text-xl font-bold mb-6 text-center text-primary uppercase tracking-wide">
                            shift Code Registration
                        </h2>
                        <form @submit.prevent="submitForm()" class="flex flex-col gap-4">
                            <div class="join w-full items-center">
                                <label class="input join-item border rounded w-full">
                                    <input type="text" placeholder="Enter Shift Code" class="w-full" v-model="form.code"
                                        required />
                                    <label class="join-item flex items-center gap-2 bg-base-200 rounded">
                                        <input type="checkbox" class="checkbox checkbox-primary checkbox-sm"
                                            @change="handleRequiredTS()" v-model="form.is_rest_day" />
                                        <span class="text-sm text-nowrap">RD / NWS</span>
                                    </label>
                                </label>
                            </div>


                            <TextInput name="Start Time:" type="time" :message="form.errors?.start_time"
                                v-model="form.start_time" :disabled="isDisabled" />
                            <TextInput name="End Time:" type="time" :message="form.errors?.end_time"
                                v-model="form.end_time" :disabled="isDisabled" />
                            <button type="submit" class="btn btn-primary w-full mt-4" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner"></span>
                                <span>Submit</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="col-span-3">
                    <div class="bg-base-100 rounded-md p-6 shadow-lg border border-base-200">
                        <!-- scrolling wrapper -->
                        <div class="max-h-[50vh] overflow-auto">
                            <table class="table w-full text-sm">
                                <thead class="sticky top-0 bg-base-200 z-10 text-base-content">
                                    <tr class="text-center">
                                        <th>Shift Code</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="shift in shiftcodes" :key="shift.shift_code" class="text-center hover">
                                        <td class="font-semibold">{{ shift.code }}</td>
                                        <td>{{ shift.start_time ?? 'N/A' }}</td>
                                        <td>{{ shift.end_time ?? 'N/A' }}</td>
                                        <td class="flex flex-row gap-2 justify-center">
                                            <button @click="handleHypyerLink(shift)" class="btn btn-success btn-xs"
                                                :disabled="deleteform.processing">
                                                EDIT
                                            </button>
                                            <button class="btn btn-error btn-xs" @click="initiateDeletion(shift.id)"
                                                :disabled="deleteform.processing">
                                                <span>DELETE</span>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="shiftcodes.length === 0">
                                        <td colspan="4" class="text-center italic text-gray-400 py-4">
                                            No shift codes available.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import TextInput from '../Components/TextInput.vue'
import { ref, watch } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import { inject } from 'vue'
import Modal from '../Components/Modal.vue'

const modalRef = ref(null)

const showModal = () => {
    modalRef.value?.open()
}

const closeModal = () => {
    modalRef.value?.close()
}

const toast = inject('toast')
const mode = ref('insert')
const id = ref(null)
const noreqtime = ref(false)
const isDisabled = ref(false)

const form = useForm({
    code: '',
    start_time: '',
    end_time: '',
    timerequired: noreqtime.value
})

const deleteform = useForm()


const submitForm = () => {
    if (mode.value == 'insert') {
        form.post(route('shift.register'), {
            onSuccess: () => {
                form.reset()
                toast('Shift code Registered Successfully.', 'success')
            },
            onError: (errors) => {
                if (errors.code) {
                    toast(errors.code, 'error')
                }
                console.log('shift code registration failed:', errors)
            }
        })
    } else {
        if (id) {
            console.log(form)
            form.put(route('shift.update', id.value), {
                onSuccess: () => {
                    form.reset()
                    toast('Shift code Updated Successfully.', 'success')
                    mode.value = 'insert'
                },
                onError: (errors) => {
                    toast('Shift Code updating failed. Please try again.', 'danger')
                }
            })
        } else {
            toast('Invalid action. Please try again.', 'danger')
        }
    }
}

const initiateDeletion = (shift_id) => {
    showModal()
    id.value = shift_id
}

const handleDeletion = () => {
    if (id) {
        mode.value = 'delete'
        deleteform.delete(route('shift.delete', id.value), {
            onSuccess: () => {
                toast('Shift code delete successfully.', 'success')
                mode.value = 'insert'
                closeModal()
            },
            onError: (errors) => {
                toast(deleteform.errors?.message || 'Shift Code deletion failed. Please try again.', 'error')
                closeModal()
            }
        })
    } else {
        toast('Invalid action. Please try again.', 'error')
    }
}

const handleHypyerLink = (data) => {
    form.code = data.code
    form.start_time = data.start_time
    form.end_time = data.end_time
    mode.value = 'update'
    id.value = data.id

    if (!data.start_time && !data.end_time) {
        isDisabled.value = true
    } else {
        isDisabled.value = false
    }

}

const props = defineProps({
    shiftcodes: Array,
    error: String
})

const handleRequiredTS = () => {
    // update the value first before identifying the disabling of input fields
    noreqtime.value = !noreqtime.value

    if (noreqtime.value) {
        isDisabled.value = true
    } else {
        isDisabled.value = false
    }
}

const shiftcodes = ref([...props.shiftcodes ?? []])

watch(
    () => props.shiftcodes,
    (newShiftcodes) => {
        shiftcodes.value = [...newShiftcodes]
    }
)

</script>
