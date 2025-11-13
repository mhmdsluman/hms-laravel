<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const form = useForm({
    name: '',
    email: '',
    role: 'clerk',
    speciality: '',
    password: '',
    password_confirmation: '',
    photo: null, // <-- ADDED
});

const page = usePage();

const roles = ['admin', 'clinician', 'clerk', 'lab', 'pharmacy', 'radiology', 'patient', 'nurse', 'ot_manager'];

// Modal state for success/error feedback (glassy modal)
const showSuccessModal = ref(false);
const showErrorModal = ref(false);
const modalMessage = ref('');

// helper to close both modals
const closeModals = () => {
    showSuccessModal.value = false;
    showErrorModal.value = false;
};

// Watch server flash props (if backend uses flash messages)
// This will be handled by the layout, but we keep the error modal logic
watch(
    () => page.props.value?.flash?.error,
    (val) => {
        if (val) {
            modalMessage.value = val;
            showErrorModal.value = true;
            setTimeout(() => (showErrorModal.value = false), 4000);
        }
    }
);

// Client-side password validation
function validatePasswords() {
    if (form.errors.password) delete form.errors.password;
    if (form.errors.password_confirmation) delete form.errors.password_confirmation;

    if (!form.password || form.password.length < 8) {
        form.errors.password = ['Password must be at least 8 characters.يجب أن تكون كلمة المرور 8 أحرف على الأقل.'];
        modalMessage.value = 'Password must be at least 8 characters.يجب أن تكون كلمة المرور 8 أحرف على الأقل.';
        showErrorModal.value = true;
        setTimeout(() => (showErrorModal.value = false), 4000);
        return false;
    }

    if (form.password !== form.password_confirmation) {
        form.errors.password_confirmation = ['Passwords do not match.كلمات المرور غير متطابقة'];
        modalMessage.value = 'Passwords do not match.كلمات المرور غير متطابقة';
        showErrorModal.value = true;
        setTimeout(() => (showErrorModal.value = false), 4000);
        return false;
    }

    return true;
}

// --- START: Photo Capture Logic ---
const showCameraModal = ref(false);
const photoPreviewUrl = ref(null);
const videoRef = ref(null);
const canvasRef = ref(null);
const streamRef = ref(null);

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

function closeCamera() {
    if (streamRef.value) {
        streamRef.value.getTracks().forEach(track => track.stop());
    }
    showCameraModal.value = false;
}

function capturePhoto() {
    const video = videoRef.value;
    const canvas = canvasRef.value;
    const context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/jpeg');
    photoPreviewUrl.value = dataUrl;
    canvas.toBlob((blob) => {
        form.photo = new File([blob], 'user_photo.jpg', { type: 'image/jpeg' });
    }, 'image/jpeg');
    closeCamera();
}

function clearPhotoPreview() {
    form.photo = null;
    photoPreviewUrl.value = null;
}
// --- END: Photo Capture Logic ---

const submit = () => {
    closeModals();
    if (!validatePasswords()) {
        return;
    }

    form.post(route('users.store'), {
        onSuccess: () => {
             // This page redirects, so this block is not called.
             // We will rely on the global layout for the success message.
             // We just need to clear the form on a successful redirect.
             form.reset();
             clearPhotoPreview();
             form.role = 'clerk';
        },
        onError: (errors) => {
            let firstMessage = 'There was a problem with your submission.حدثت مشكلة في تقديمك.';
            if (errors && typeof errors === 'object') {
                const firstKey = Object.keys(errors)[0];
                if (firstKey && errors[firstKey] && errors[firstKey].length) {
                    firstMessage = errors[firstKey][0];
                } else {
                    firstMessage = Object.values(errors).flat().slice(0, 3).join(' ');
                }
            }
            modalMessage.value = firstMessage;
            showErrorModal.value = true;
            setTimeout(() => (showErrorModal.value = false), 6000);
        },
    });
};
</script>

<template>
    <Head title="Add User" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add New User</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        <!-- Glassy Modal (for errors) -->
                        <transition name="fade-scale" appear>
                            <div v-if="showErrorModal" class="fixed inset-0 z-50 flex items-center justify-center pointer-events-auto">
                                <div class="absolute inset-0" @click="closeModals"></div>
                                <div class="relative w-full max-w-lg mx-4 rounded-2xl overflow-hidden" role="dialog" aria-modal="true">
                                    <div class="flex items-stretch">
                                        <div class="w-1 rounded-l-2xl bg-rose-500"></div>
                                        <div class="flex-1 p-6 bg-white/35 backdrop-blur-md border border-white/20 shadow-2xl ring-1 ring-white/30">
                                            <div class="flex items-start space-x-4">
                                                <div class="flex-shrink-0">
                                                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-rose-50">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="text-lg font-semibold text-slate-900">Error</h3>
                                                    <p class="mt-1 text-sm text-slate-700">{{ modalMessage }}</p>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <button @click="closeModals" class="inline-flex items-center justify-center rounded-full p-1 hover:bg-white/40" aria-label="Close">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="mt-4 flex justify-end space-x-2">
                                                <button @click="closeModals" class="px-3 py-1 rounded-md text-sm font-medium bg-white/30 hover:bg-white/40 text-slate-800">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>
                        <!-- End glassy modal -->

                        <form @submit.prevent="submit">
                            <div class="space-y-4">

                                <!-- START: Profile Photo Section -->
                                <fieldset class="grid grid-cols-1 gap-6">
                                    <legend class="text-lg font-medium text-gray-900">User Photo (Optional)</legend>
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

                                <div class="border-t pt-4 space-y-4">
                                    <div>
                                        <label for="name" class="block font-medium text-sm text-gray-700">Name</label>
                                        <input id="name" type="text" v-model="form.name" class="block mt-1 w-full rounded-md" required>
                                        <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                                    </div>
                                    <div>
                                        <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                                        <input id="email" type="email" v-model="form.email" class="block mt-1 w-full rounded-md" required>
                                        <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
                                    </div>
                                    <div>
                                        <label for="role" class="block font-medium text-sm text-gray-700">Role</label>
                                        <select id="role" v-model="form.role" class="block mt-1 w-full rounded-md" required>
                                            <option v-for="role in roles" :key="role" :value="role">{{ role.toUpperCase() }}</option>
                                        </select>
                                        <div v-if="form.errors.role" class="text-sm text-red-600 mt-1">{{ form.errors.role }}</div>
                                    </div>
                                    <div>
                                        <label for="speciality" class="block font-medium text-sm text-gray-700">Speciality (e.g., Cardiology, Hematology)</label>
                                        <input id="speciality" type="text" v-model="form.speciality" class="block mt-1 w-full rounded-md">
                                        <div v-if="form.errors.speciality" class="text-sm text-red-600 mt-1">{{ form.errors.speciality }}</div>
                                    </div>
                                    <div>
                                        <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
                                        <input id="password" type="password" v-model="form.password" class="block mt-1 w-full rounded-md" required>
                                        <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</div>
                                    </div>
                                    <div>
                                        <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirm Password</label>
                                        <input id="password_confirmation" type="password" v-model="form.password_confirmation" class="block mt-1 w-full rounded-md" required>
                                        <div v-if="form.errors.password_confirmation" class="text-sm text-red-600 mt-1">{{ form.errors.password_confirmation }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('users.index')" class="text-sm text-gray-600 hover:underline">Cancel</Link>
                                <button type="submit" :disabled="form.processing" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md">Create User</button>
D                            </div>
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

<style>
/* subtle transition used by the modal */
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 180ms cubic-bezier(.2,.9,.2,1);
}
.fade-scale-enter-from,
.fade-scale-leave-to {
    opacity: 0;
    transform: translateY(6px) scale(0.98);
}
</style>
