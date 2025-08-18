    <script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head, Link, useForm } from '@inertiajs/vue3';

    defineProps({
        medications: Array,
    });

    const form = useForm({
        service_id: null,
        quantity_in_stock: '',
        batch_number: '',
        expiry_date: '',
        location: 'Main Pharmacy',
    });

    const submit = () => {
        form.post(route('inventory.store'));
    };
    </script>
    <template>
        <Head title="Add New Stock" />
        <AuthenticatedLayout>
            <template #header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add New Stock to Inventory</h2>
            </template>
            <div class="py-12">
                <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <form @submit.prevent="submit">
                                <div class="space-y-4">
                                    <div>
                                        <label for="service_id" class="block font-medium text-sm text-gray-700">Medication</label>
                                        <select id="service_id" v-model="form.service_id" class="block mt-1 w-full rounded-md" required>
                                            <option :value="null" disabled>Select a medication</option>
                                            <option v-for="med in medications" :key="med.id" :value="med.id">{{ med.name }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="quantity_in_stock" class="block font-medium text-sm text-gray-700">Quantity Received</label>
                                        <input id="quantity_in_stock" type="number" v-model="form.quantity_in_stock" class="block mt-1 w-full rounded-md" required>
                                    </div>
                                    <div>
                                        <label for="batch_number" class="block font-medium text-sm text-gray-700">Batch Number</label>
                                        <input id="batch_number" type="text" v-model="form.batch_number" class="block mt-1 w-full rounded-md">
                                    </div>
                                    <div>
                                        <label for="expiry_date" class="block font-medium text-sm text-gray-700">Expiry Date</label>
                                        <input id="expiry_date" type="date" v-model="form.expiry_date" class="block mt-1 w-full rounded-md">
                                    </div>
                                </div>
                                <div class="flex items-center justify-end mt-6">
                                    <Link :href="route('inventory.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                    <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Add to Stock</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </template>
