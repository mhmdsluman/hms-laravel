<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { Country, State, City } from 'country-state-city';
import axios from 'axios';

const props = defineProps({
    providers: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    full_name: '',
    date_of_birth: '',
    gender: 'Male',
    primary_phone_country_code: '+249',
    primary_phone: '',
    email: '',
    addresses: [{
        type: 'Home',
        street: '',
        city: '',
        state: '',
        postal_code: '',
        country: 'Somalia',
        country_iso: 'SO',
    }],
    insurance_provider_id: null,
    policy_number: '',
    start_date: '',
    end_date: '',
    photo: null, // <-- ADDED
});

// Duplicate check state
const phoneStatus = ref({ checking: false, isDuplicate: false, message: '' });
let debounceTimeout = null;

const checkDuplicate = (field, value, statusRef) => {
    clearTimeout(debounceTimeout);
    if (!value) {
        statusRef.value = { checking: false, isDuplicate: false, message: '' };
        return;
    }
    statusRef.value = { checking: true, isDuplicate: false, message: 'Checking...' };
    debounceTimeout = setTimeout(() => {
        axios.post(route('patients.checkDuplicate'), { field, value })
            .then(response => {
                statusRef.value.isDuplicate = response.data.is_duplicate;
                statusRef.value.message = response.data.is_duplicate
                    ? `${field.charAt(0).toUpperCase() + field.slice(1)} is already in use.`
                    : 'Available';
            })
            .catch(() => {
                statusRef.value.message = 'Error checking duplicate.';
            })
            .finally(() => {
                statusRef.value.checking = false;
            });
    }, 500);
};

watch(() => form.primary_phone, (newValue) => {
    const fullPhone = form.primary_phone_country_code + ltrim(newValue, '0');
    checkDuplicate('primary_phone', fullPhone, phoneStatus);
});

function ltrim(str, chars) {
    return str.replace(new RegExp(`^[${chars}]+`), '');
}

// Country/State/City dropdown state
const countries = ref([]);
const states = ref([]);
const cities = ref([]);
const selectedCountry = ref(null);
const selectedState = ref(null);

onMounted(() => {
    countries.value = Country.getAllCountries();
    const lastCountry = localStorage.getItem('lastSelectedCountry');
    if (lastCountry) {
        selectedCountry.value = countries.value.find(c => c.isoCode === lastCountry) || null;
    }
    if (!selectedCountry.value) {
        const somalia = countries.value.find(c => c.isoCode === 'SO' || c.name?.toLowerCase() === 'somalia');
        if (somalia) {
            selectedCountry.value = somalia;
            form.addresses[0].country_iso = somalia.isoCode;
            form.addresses[0].country = somalia.name;
        }
    } else {
        form.addresses[0].country_iso = selectedCountry.value.isoCode;
        form.addresses[0].country = selectedCountry.value.name;
    }
});

watch(selectedCountry, (country) => {
    if (country) {
        states.value = State.getStatesOfCountry(country.isoCode);
        cities.value = [];
        form.addresses[0].country_iso = country.isoCode;
        form.addresses[0].country = country.name;
        form.addresses[0].state = '';
        form.addresses[0].city = '';
        selectedState.value = null;
        localStorage.setItem('lastSelectedCountry', country.isoCode);
    } else {
        states.value = [];
        cities.value = [];
        form.addresses[0].country_iso = '';
        form.addresses[0].country = '';
    }
});

watch(selectedState, (state) => {
    if (state) {
        cities.value = City.getCitiesOfState(state.countryCode, state.isoCode);
        form.addresses[0].state = state.name;
        form.addresses[0].city = '';
    } else {
        cities.value = [];
        form.addresses[0].state = '';
    }
});

// --- START: Photo Capture Logic ---
const showCameraModal = ref(false);
const photoPreviewUrl = ref(null);
const videoRef = ref(null);
const canvasRef = ref(null);
const streamRef = ref(null);

// Handle file input change
function onFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreviewUrl.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// Open camera modal and start stream
async function openCameraModal() {
    showCameraModal.value = true;
    try {
        streamRef.value = await navigator.mediaDevices.getUserMedia({ video: true });
        videoRef.value.srcObject = streamRef.value;
    } catch (err) {
        console.error("Error accessing camera: ", err);
        alert("Could not access camera. Please check permissions.");
        showCameraModal.value = false;
    }
}

// Close camera modal and stop stream
function closeCamera() {
    if (streamRef.value) {
        streamRef.value.getTracks().forEach(track => track.stop());
    }
    showCameraModal.value = false;
}

// Capture photo from video stream
function capturePhoto() {
    const video = videoRef.value;
    const canvas = canvasRef.value;
    const context = canvas.getContext('2d');

    // Set canvas dimensions to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    // Draw video frame to canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Get data URL and convert to Blob/File
    const dataUrl = canvas.toDataURL('image/jpeg');
    photoPreviewUrl.value = dataUrl;

    canvas.toBlob((blob) => {
        form.photo = new File([blob], 'patient_photo.jpg', { type: 'image/jpeg' });
    }, 'image/jpeg');

    closeCamera();
}

// Clear photo preview and form data
function clearPhotoPreview() {
    form.photo = null;
    photoPreviewUrl.value = null;
}
// --- END: Photo Capture Logic ---


// Submit handler
const submit = () => {
    // Note: form.post() automatically handles multipart/form-data when a File object is present
    form.post(route('patients.store'), {
        onSuccess: () => {
            form.reset();
            clearPhotoPreview(); // Clear the photo preview
            // Re-apply preferred country
            if (selectedCountry.value) {
                form.addresses[0].country_iso = selectedCountry.value.isoCode;
                form.addresses[0].country = selectedCountry.value.name;
            } else {
                const somalia = countries.value.find(c => c.isoCode === 'SO');
                if (somalia) {
                    selectedCountry.value = somalia;
                    form.addresses[0].country_iso = somalia.isoCode;
                    form.addresses[0].country = somalia.name;
                }
            }
        },
        onError: () => {
             // Error handling (e.g., show a modal) can be added here
             console.error("Error submitting form:", form.errors);
        }
    });
};
</script>

<template>
    <Head :title="$t('Patient Registration')" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t('Patient Registration') }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        <!-- Flash Message (from previous step, now handled by layout) -->
                        <div v-if="$page.props.flash.success" class="mb-4 p-4 rounded-md bg-green-100 border border-green-300 text-green-700">
                            {{ $page.props.flash.success }}
                        </div>
                        <div v-if="$page.props.flash.error" class="mb-4 p-4 rounded-md bg-red-100 border border-red-300 text-red-700">
                            {{ $page.props.flash.error }}
                        </div>
                        <!-- End Flash Message -->

                        <form @submit.prevent="submit">
                            <div class="space-y-6">

                                <!-- START: Profile Photo Section -->
                                <fieldset class="grid grid-cols-1 gap-6">
                                    <legend class="text-lg font-medium text-gray-900">Profile Photo (Optional)</legend>
                                    <div class="flex items-center gap-4">
                                        <!-- Photo Preview -->
                                        <div class="w-24 h-24 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center border">
                                            <img v-if="photoPreviewUrl" :src="photoPreviewUrl" class="w-full h-full object-cover">
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <!-- Buttons -->
                                        <div class="flex flex-col gap-2">
                                            <label for="photo_upload" class="cursor-pointer px-3 py-2 text-sm bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                                                Upload Photo
                                            </label>
                                            <input id="photo_upload" type="file" @input="onFileChange" accept="image/*" class="hidden">

                                            <button type="button" @click="openCameraModal" class="px-3 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                                Take Photo
                                            </button>
                                            <button v-if="photoPreviewUrl" type="button" @click="clearPhotoPreview" class="text-xs text-red-600 hover:underline">
                                                Remove Photo
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.photo" class="text-sm text-red-600 mt-1">{{ form.errors.photo }}</div>
                                </fieldset>
                                <!-- END: Profile Photo Section -->


                                <!-- Personal Info -->
                                <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-6">
                                    <legend class="text-lg font-medium text-gray-900">Personal Info</legend>
                                    <div class="md:col-span-2">
                                        <label for="full_name" class="block font-medium text-sm text-gray-700">Full Name</label>
                                        <input id="full_name" type="text" v-model="form.full_name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                        <div v-if="form.errors.full_name" class="text-sm text-red-600 mt-1">{{ form.errors.full_name }}</div>
                                    </div>
                                    <div>
                                        <label for="date_of_birth" class="block font-medium text-sm text-gray-700">Date of Birth</label>
                                        <input id="date_of_birth" type="date" v-model="form.date_of_birth" class="block mt-1 w-full rounded-md shadow-sm border-gray-300" required>
                                        <div v-if="form.errors.date_of_birth" class="text-sm text-red-600 mt-1">{{ form.errors.date_of_birth }}</div>
                                    </div>
                                    <div>
                                        <label for="gender" class="block font-medium text-sm text-gray-700">Gender</label>
                                        <select id="gender" v-model="form.gender" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                            <option>Male</option>
                                            <option>Female</option>
                                            <option>Other</option>
                                        </select>
                                        <div v-if="form.errors.gender" class="text-sm text-red-600 mt-1">{{ form.errors.gender }}</div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="primary_phone" class="block font-medium text-sm text-gray-700">Primary Phone Number</label>
                                        <div class="flex mt-1">
                                            <input type="text" v-model="form.primary_phone_country_code" class="w-1/4 rounded-l-md shadow-sm border-gray-300">
                                            <input id="primary_phone" type="tel" v-model="form.primary_phone" class="w-3/4 rounded-r-md shadow-sm border-gray-300" required>
                                        </div>
                                        <div v-if="phoneStatus.message" :class="{'text-red-600': phoneStatus.isDuplicate, 'text-green-600': !phoneStatus.isDuplicate && form.primary_phone}" class="text-xs mt-1">{{ phoneStatus.message }}</div>
                                        <div v-if="form.errors.primary_phone" class="text-sm text-red-600 mt-1">{{ form.errors.primary_phone }}</div>
                                    </div>
                                </fieldset>

                                <!-- Address -->
                                <fieldset class="grid grid-cols-1 gap-6 border-t pt-6">
                                    <legend class="text-lg font-medium text-gray-900">Address</legend>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block font-medium text-sm">Country</label>
                                            <select v-model="selectedCountry" class="mt-1 block w-full" required>
                                                <option :value="null" disabled>Select a country</option>
                                                <option v-for="c in countries" :key="c.isoCode" :value="c">{{ c.name }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-sm">State / Province</label>
                                            <select v-model="selectedState" class="mt-1 block w-full" :disabled="!states.length">
                                                <option :value="null">Select a state</option>
                                                <option v-for="s in states" :key="s.isoCode" :value="s">{{ s.name }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-sm">City</label>
                                            <select v-model="form.addresses[0].city" class="mt-1 block w-full" :disabled="!cities.length" required>
                                                <option value="" disabled>Select a city</option>
                                                <option v-for="city in cities" :key="city.name" :value="city.name">{{ city.name }}</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block font-medium text-sm">Street Address</label>
                                            <input type="text" v-model="form.addresses[0].street" class="mt-1 block w-full" required>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-sm">Postal Code (Optional)</label>
                                            <input type="text" v-model="form.addresses[0].postal_code" class="mt-1 block w-full">
                                        </div>
                                    </div>
                                </fieldset>

                                <!-- Insurance Details -->
                                <fieldset class="grid grid-cols-1 gap-6 border-t pt-6">
                                    <legend class="text-lg font-medium text-gray-900">Insurance Details (Optional)</legend>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="md:col-span-2">
                                            <label class="block font-medium text-sm">Insurance Provider</label>
                                            <select v-model="form.insurance_provider_id" class="mt-1 block w-full">
                                                <option :value="null">Self-Pay / No Insurance</option>
                                                <option v-for="provider in props.providers" :key="provider.id" :value="provider.id">{{ provider.name }}</option>
                                            </select>
                                        </div>
                                        <div v-if="form.insurance_provider_id">
                                            <label class="block font-medium text-sm">Policy Number</label>
                                            <input type="text" v-model="form.policy_number" class="mt-1 block w-full">
                                        </div>
                                        <div v-if="form.insurance_provider_id">
                                            <label class="block font-medium text-sm">Coverage Start Date</label>
                                            <input type="date" v-model="form.start_date" class="mt-1 block w-full">
                                        </div>
                                        <div v-if="form.insurance_provider_id">
                                            <label class="block font-medium text-sm">Coverage End Date</label>
                                            <input type="date" v-model="form.end_date" class="mt-1 block w-full">
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <button type="submit" :disabled="form.processing || phoneStatus.isDuplicate" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                                    Register Patient
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- START: Camera Modal -->
        <div v-if="showCameraModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
            <div class="bg-white p-4 rounded-lg shadow-xl max-w-lg w-full">
                <h3 class="text-lg font-medium mb-2">Take Photo</h3>
                <video ref="videoRef" autoplay playsinline class="w-full h-auto rounded-md border"></video>
                <canvas ref="canvasRef" class="hidden"></canvas>
                <div class="mt-4 flex justify-end gap-2">
                    <button @click="closeCamera" type="button" class="px-4 py-2 text-sm bg-gray-200 rounded-md">Cancel</button>
                    <button @click="capturePhoto" type="button" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md">Capture</button>
                </div>
            </div>
        </div>
        <!-- END: Camera Modal -->

    </AuthenticatedLayout>
</template>
