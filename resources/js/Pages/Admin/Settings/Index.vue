<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    hospital_name: props.settings.hospital_name,
    hospital_email: props.settings.hospital_email,
    hospital_phone: props.settings.hospital_phone,
    hospital_address: props.settings.hospital_address,
    hospital_logo: null,
});

const submit = () => {
    form.post(route('admin.settings.update'), {
        _method: 'post',
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Hospital Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Hospital Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="hospital_name" value="Hospital Name" />
                                <TextInput id="hospital_name" type="text" class="mt-1 block w-full" v-model="form.hospital_name" />
                                <InputError class="mt-2" :message="form.errors.hospital_name" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="hospital_email" value="Hospital Email" />
                                <TextInput id="hospital_email" type="email" class="mt-1 block w-full" v-model="form.hospital_email" />
                                <InputError class="mt-2" :message="form.errors.hospital_email" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="hospital_phone" value="Hospital Phone" />
                                <TextInput id="hospital_phone" type="text" class="mt-1 block w-full" v-model="form.hospital_phone" />
                                <InputError class="mt-2" :message="form.errors.hospital_phone" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="hospital_address" value="Hospital Address" />
                                <TextInput id="hospital_address" type="text" class="mt-1 block w-full" v-model="form.hospital_address" />
                                <InputError class="mt-2" :message="form.errors.hospital_address" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="hospital_logo" value="Hospital Logo" />
                                <input id="hospital_logo" type="file" @input="form.hospital_logo = $event.target.files[0]" />
                                <InputError class="mt-2" :message="form.errors.hospital_logo" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Save
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
