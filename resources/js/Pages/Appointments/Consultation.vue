<script setup>
// ... existing script setup ...
import { ref, onMounted, watch, computed } from 'vue';

// ... existing props ...

const ordersTab = ref('services'); // 'services' or 'pending'
const serviceCategoryTab = ref('Lab'); // Default to 'Lab'

// ... existing refs and forms ...

const pendingOrders = computed(() => {
    return props.appointment.orders.flatMap(order => order.items);
});

// ... existing functions ...
</script>

<template>
    <!-- ... existing template ... -->
    <!-- Orders Tab Content -->
    <div v-show="activeTab === 'orders'" class="p-6">
        <!-- ... existing flash messages and form errors ... -->

        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-6">
                <button @click="ordersTab = 'services'" :class="['py-4 px-1 border-b-2 font-medium text-sm', ordersTab === 'services' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500']">
                    Order Services
                </button>
                <button @click="ordersTab = 'pending'" :class="['py-4 px-1 border-b-2 font-medium text-sm', ordersTab === 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500']">
                    Pending Orders ({{ pendingOrders.length }})
                </button>
            </nav>
        </div>

        <div v-show="ordersTab === 'services'">
            <form @submit.prevent="submitOrders">
                <!-- Service Category Tabs -->
                <div class="border-b border-gray-200 mt-4">
                    <nav class="-mb-px flex space-x-6">
                        <button v-for="department in Object.keys(services)" :key="department" @click.prevent="serviceCategoryTab = department" :class="['py-4 px-1 border-b-2 font-medium text-sm', serviceCategoryTab === department ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500']">
                            {{ department }}
                        </button>
                    </nav>
                </div>

                <!-- ... Order Sets Quick Actions ... -->

                <div class="space-y-6 mt-4">
                    <div v-for="(group, department) in services" :key="department" v-show="serviceCategoryTab === department">
                        <h4 class="font-semibold mb-2">{{ department }}</h4>
                        <!-- ... service selection grid ... -->
                    </div>
                </div>

                <!-- ... submit button ... -->
            </form>
        </div>

        <div v-show="ordersTab === 'pending'" class="mt-4">
            <h4 class="font-semibold mb-2">Pending Orders</h4>
            <ul class="divide-y divide-gray-200">
                <li v-for="item in pendingOrders" :key="item.id" class="py-2 flex justify-between items-center">
                    <span>{{ item.service.name }}</span>
                    <span class="text-sm font-medium" :class="{
                        'text-yellow-600': item.status === 'Pending',
                        'text-blue-600': item.status === 'Sample Collected',
                        'text-green-600': item.status === 'Result Ready',
                    }">{{ item.status }}</span>
                </li>
            </ul>
        </div>
    </div>
    <!-- ... rest of the template ... -->
</template>
