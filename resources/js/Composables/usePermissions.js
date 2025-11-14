import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const user = computed(() => usePage().props.auth.user);

    const hasRole = (roles) => {
        if (!user.value || !user.value.role) {
            return false;
        }

        // Ensure roles is always an array for consistent checking
        const rolesArray = Array.isArray(roles) ? roles : [roles];

        // Check if the user's single role is included in the array of required roles
        return rolesArray.includes(user.value.role);
    };

    // This can be used if you add a permissions array to your user object later
    const hasPermission = (permissions) => {
        if (!user.value || !user.value.permissions) {
            return false;
        }
        const permissionsArray = Array.isArray(permissions) ? permissions : [permissions];
        return permissionsArray.every(p => user.value.permissions.includes(p));
    };

    return { hasRole, hasPermission };
}
