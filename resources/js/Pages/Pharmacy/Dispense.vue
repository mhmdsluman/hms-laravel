<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    orderItem: Object,
    inventoryItems: Array,
});

const form = useForm({
    inventory_item_id: null,
    quantity_dispensed: 1,
    // Add new fields for the verifier
    verifier_email: '',
    verifier_password: '',
});

const submit = () => {
    form.post(route('dispensations.store', props.orderItem.id));
};
</script>

<template>
    <Head title="Dispense Medication" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dispense: {{ orderItem.service.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                         <div class="mb-4 border-b pb-4">
                            <h3 class="font-semibold">Patient: {{ orderItem.order.patient.first_name }} {{ orderItem.order.patient.last_name }}</h3>
                            <p class="text-sm text-gray-600">UHID: {{ orderItem.order.patient.uhid }}</p>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="space-y-4">
                                <div>
                                    <label for="inventory_item_id" class="block font-medium text-sm text-gray-700">Select Batch / Stock</label>
                                    <select id="inventory_item_id" v-model="form.inventory_item_id" class="block mt-1 w-full rounded-md" required>
                                        <option :value="null" disabled>Select an available batch</option>
                                        <option v-for="item in inventoryItems" :key="item.id" :value="item.id">
                                            Batch: {{ item.batch_number }} (In Stock: {{ item.quantity_in_stock }}) - Expires: {{ item.expiry_date }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.inventory_item_id" class="text-sm text-red-600 mt-1">{{ form.errors.inventory_item_id }}</div>
                                </div>
                                <div>
                                    <label for="quantity_dispensed" class="block font-medium text-sm text-gray-700">Quantity to Dispense</label>
                                    <input id="quantity_dispensed" type="number" v-model="form.quantity_dispensed" class="block mt-1 w-full rounded-md" required>
                                    <div v-if="form.errors.quantity_dispensed" class="text-sm text-red-600 mt-1">{{ form.errors.quantity_dispensed }}</div>
                                </div>

                                <!-- **NEW**: Verification section for controlled substances -->
                                <div v-if="orderItem.service.is_controlled_substance" class="border-t border-red-200 pt-4 mt-4 space-y-4 bg-red-50 p-4 rounded-lg">
                                    <h4 class="font-bold text-red-700">Verification Required (Controlled Substance)</h4>
                                    <p class="text-sm text-red-600">A second authorized user must verify this dispensation.</p>
                                    <div>
                                        <label for="verifier_email" class="block font-medium text-sm text-gray-700">Verifier's Email</label>
                                        <input id="verifier_email" type="email" v-model="form.verifier_email" class="block mt-1 w-full rounded-md" required>
                                        <div v-if="form.errors.verifier_email" class="text-sm text-red-600 mt-1">{{ form.errors.verifier_email }}</div>
                                    </div>
                                    <div>
                                        <label for="verifier_password" class="block font-medium text-sm text-gray-700">Verifier's Password</label>
                                        <input id="verifier_password" type="password" v-model="form.verifier_password" class="block mt-1 w-full rounded-md" required>
                                        <div v-if="form.errors.verifier_password" class="text-sm text-red-600 mt-1">{{ form.errors.verifier_password }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('pharmacy.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
                                    Confirm & Dispense
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
