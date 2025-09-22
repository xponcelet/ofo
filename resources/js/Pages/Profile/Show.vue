<script setup>
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import UpdateSubscriptionForm from '@/Pages/Profile/Partials/UpdateSubscriptionForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});

onMounted(() => {
    const params = new URLSearchParams(window.location.search)
    if (params.get('checkout') === '1') {
        // Recharge seulement ce qui nous intéresse (auth + flash)
        router.reload({ only: ['auth', 'flash'], preserveScroll: true })
    }
})
</script>

<template>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div v-if="$page.props.jetstream.canUpdateProfileInformation">
            <UpdateProfileInformationForm :user="$page.props.auth.user" />
            <SectionBorder />
        </div>

        <div class="mt-10 sm:mt-0">
            <UpdateSubscriptionForm
                :key="$page.props.auth.user.is_premium + ':' + ($page.props.auth.user.premium_ends_at ?? '')"
                :user="$page.props.auth.user"
            />
            <SectionBorder />
        </div>

        <div v-if="$page.props.jetstream.canUpdatePassword">
            <UpdatePasswordForm class="mt-10 sm:mt-0" />
            <SectionBorder />
        </div>

        <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
            <TwoFactorAuthenticationForm
                :requires-confirmation="confirmsTwoFactorAuthentication"
                class="mt-10 sm:mt-0"
            />
            <SectionBorder />
        </div>

        <LogoutOtherBrowserSessionsForm :sessions="sessions" class="mt-10 sm:mt-0" />

        <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
            <SectionBorder />
            <DeleteUserForm class="mt-10 sm:mt-0" />
        </template>
    </div>
</template>
