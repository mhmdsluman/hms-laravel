    <script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head, Link, useForm } from '@inertiajs/vue3';

    const props = defineProps({
        service: Object,
    });

    const form = useForm({
        name: props.service.name,
        department: props.service.department,
        price: props.service.price,
    });

    const submit = () => {
        form.put(route('services.update', props.service.id));
    };
    </script>
    <template>
        <Head title="Edit Service" />
        <AuthenticatedLayout>
            <template #header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Service</h2>
            </template>
            <div class="py-12">
                <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <form @submit.prevent="submit">
                                <div class="space-y-4">
                                    <div>
                                        <label for="name" class="block font-medium text-sm text-gray-700">Service Name</label>
                                        <input id="name" type="text" v-model="form.name" class="block mt-1 w-full rounded-md" required>
                                    </div>
                                    <div>
                                        <label for="department" class="block font-medium text-sm text-gray-700">Department</label>
                                        <input id="department" type="text" v-model="form.department" class="block mt-1 w-full rounded-md" required>
                                    </div>
                                    <div>
                                        <label for="price" class="block font-medium text-sm text-gray-700">Price</label>
                                        <input id="price" type="number" step="0.01" v-model="form.price" class="block mt-1 w-full rounded-md" required>
                                    </div>
                                </div>
                                <div class="flex items-center justify-end mt-6">
                                    <Link :href="route('services.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                    <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-green-600 text-white rounded-md">Update Service</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </template>
