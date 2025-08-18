<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    pdfUrl: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close']);

const printPdf = () => {
    const iframe = document.getElementById('pdf-iframe');
    if (iframe) {
        iframe.contentWindow.print();
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75" @click.self="emit('close')">
        <div class="bg-white rounded-lg shadow-xl p-4 w-full max-w-4xl h-5/6 flex flex-col mx-4">
            <div class="flex justify-between items-center border-b pb-2 mb-2">
                <h3 class="text-lg font-bold text-gray-900">Print Preview</h3>
                <div>
                    <button @click="printPdf" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 mr-2">Print</button>
                    <button @click="emit('close')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">Close</button>
                </div>
            </div>
            <div class="flex-grow">
                <iframe v-if="pdfUrl" :src="pdfUrl" id="pdf-iframe" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>
</template>
