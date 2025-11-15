<template>
    <div v-if="show" class="fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Invoice History for {{ patient.first_name }} {{ patient.last_name }}</h3>
                    <div class="mt-4">
                        <div class="space-y-4">
                            <div v-for="invoice in invoices" :key="invoice.id" class="flex items-center">
                                <input type="checkbox" :id="'invoice-' + invoice.id" :value="invoice.id" v-model="selectedInvoices" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label :for="'invoice-' + invoice.id" class="ml-3 block text-sm font-medium text-gray-700">
                                    {{ new Date(invoice.created_at).toLocaleDateString() }} - ${{ invoice.total_amount }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="printSelectedInvoices" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Print Selected Invoices
                    </button>
                    <button @click="close" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    patient: Object,
});

const emit = defineEmits(['close']);

const invoices = ref([]);
const selectedInvoices = ref([]);

const fetchInvoices = async () => {
    if (props.patient) {
        try {
            const response = await axios.get(route('patients.invoiceHistory', props.patient.id));
            invoices.value = response.data;
        } catch (error) {
            console.error('Error fetching invoices:', error);
        }
    }
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        fetchInvoices();
    }
});

const printSelectedInvoices = () => {
    if (selectedInvoices.value.length === 0) {
        alert('Please select at least one invoice to print.');
        return;
    }
    const ids = selectedInvoices.value.join(',');
    window.open(route('print.invoices', { ids }), '_blank');
};

const close = () => {
    emit('close');
};
</script>
