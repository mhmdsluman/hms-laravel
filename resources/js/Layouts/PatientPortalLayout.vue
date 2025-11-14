<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-100">
            <!-- Primary Navigation Menu -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('portal.dashboard')">
                                <h1 class="text-xl font-bold text-blue-600">Patient Portal</h1>
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <Link :href="route('portal.dashboard')" :class="{ 'border-blue-400 text-gray-900': route().current('portal.dashboard'), 'border-transparent text-gray-500 hover:text-gray-700': !route().current('portal.dashboard') }" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Dashboard</Link>
                            <Link :href="route('portal.appointments')" :class="{ 'border-blue-400 text-gray-900': route().current('portal.appointments'), 'border-transparent text-gray-500 hover:text-gray-700': !route().current('portal.appointments') }" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">My Appointments</Link>
                            <Link :href="route('portal.bills')" :class="{ 'border-blue-400 text-gray-900': route().current('portal.bills'), 'border-transparent text-gray-500 hover:text-gray-700': !route().current('portal.bills') }" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">My Bills</Link>
                            <Link :href="route('portal.messages.index')" :class="{ 'border-blue-400 text-gray-900': route().current('portal.messages.index'), 'border-transparent text-gray-500 hover:text-gray-700': !route().current('portal.messages.index') }" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Messages</Link>
                            <Link :href="route('portal.appointments.request.create')" :class="{ 'border-blue-400 text-gray-900': route().current('portal.appointments.request.create'), 'border-transparent text-gray-500 hover:text-gray-700': !route().current('portal.appointments.request.create') }" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Request Appointment</Link>
                        </div>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="flex items-center ml-6">
                         <span class="text-gray-700">{{ $page.props.auth.user.name }}</span>
                         <Link :href="route('logout')" method="post" as="button" class="ml-4 text-sm text-gray-600 hover:text-gray-900">Log Out</Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="bg-white shadow" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                 <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <Transition name="page" mode="out-in">
                    <div :key="$page.url">
                        <slot />
                    </div>
                </Transition>
            </div>
        </main>
    </div>
</template>
