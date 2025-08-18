<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    users: Object,
});

const showConfirmModal = ref(false);
const userToDelete = ref(null);

const confirmUserDeletion = (id) => {
    userToDelete.value = id;
    showConfirmModal.value = true;
};

const deleteUser = () => {
    router.delete(route('users.destroy', userToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            showConfirmModal.value = false;
            userToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="User Management" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">User Management</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>
                 <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    {{ $page.props.flash.error }}
                </div>                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">All Users</h3>
                            <Link :href="route('users.create')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add New User</Link>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Speciality</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users.data" :key="user.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap uppercase text-xs font-bold">{{ user.role }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ user.speciality }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                        <Link :href="route('users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                        <button @click="confirmUserDeletion(user.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination class="mt-6" :links="users.links" />
                    </div>
                </div>
            </div>
        </div>
        <ConfirmationModal :show="showConfirmModal" @confirm="deleteUser" @cancel="showConfirmModal = false" />
    </AuthenticatedLayout>
</template>
