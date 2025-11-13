<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

const props = defineProps({
    schedules: Array,
});

const calendarOptions = {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    events: props.schedules.map(schedule => ({
        title: schedule.title,
        start: schedule.start_time,
        end: schedule.end_time,
    })),
    editable: true,
    selectable: true,
    select: handleDateSelect,
    eventClick: handleEventClick,
};

const showModal = ref(false);
const selectedEvent = ref(null);

const form = useForm({
    title: '',
    description: '',
    type: 'Appointment',
    resource_id: '',
    start_time: '',
    end_time: '',
});

function handleDateSelect(selectInfo) {
    form.start_time = selectInfo.startStr;
    form.end_time = selectInfo.endStr;
    showModal.value = true;
}

function handleEventClick(clickInfo) {
    selectedEvent.value = props.schedules.find(s => s.id === clickInfo.event.id);
    form.title = selectedEvent.value.title;
    form.description = selectedEvent.value.description;
    form.type = selectedEvent.value.type;
    form.resource_id = selectedEvent.value.resource_id;
    form.start_time = selectedEvent.value.start_time;
    form.end_time = selectedEvent.value.end_time;
    showModal.value = true;
}

function submit() {
    if (selectedEvent.value) {
        form.put(route('admin.schedule.update', selectedEvent.value.id), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.post(route('admin.schedule.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
}
</script>

<template>
    <Head title="Schedule" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Schedule</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <FullCalendar :options="calendarOptions" />
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submit">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">{{ selectedEvent ? 'Edit' : 'New' }} Schedule</h3>
                            <div class="mt-4">
                                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                <input type="text" id="title" v-model="form.title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="mt-4">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea id="description" v-model="form.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                            </div>
                            <div class="mt-4">
                                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                                <select id="type" v-model="form.type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option>Appointment</option>
                                    <option>Operating Theater</option>
                                    <option>Equipment</option>
                                </select>
                            </div>
                            <div class="mt-4">
                                <label for="resource_id" class="block text-sm font-medium text-gray-700">Resource ID</label>
                                <input type="text" id="resource_id" v-model="form.resource_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="mt-4">
                                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                                <input type="datetime-local" id="start_time" v-model="form.start_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="mt-4">
                                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                                <input type="datetime-local" id="end_time" v-model="form.end_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Save</button>
                            <button @click="showModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
