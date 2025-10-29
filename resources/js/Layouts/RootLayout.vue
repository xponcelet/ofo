<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

// Layouts
import AppLayout from '@/Layouts/AppLayout.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import Banner from '@/Components/Banner.vue'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

defineProps({
    title: { type: String, default: '' },
})

const page = usePage()
const isAuth = computed(() => !!page.props?.auth?.user)
const isAdminRoute = computed(() => page.url.startsWith('/admin'))
</script>

<template>
    <div class="flex flex-col min-h-screen bg-background text-on-surface">
        <Head :title="title" />

        <Banner />

        <!-- Choix du layout -->
        <component
            :is="isAdminRoute ? AdminLayout : (isAuth ? AppLayout : GuestLayout)"
            :title="title"
        >
            <slot />
        </component>

        <!-- Footer uniquement pour front/guest -->
        <footer
            v-if="!isAdminRoute"
            class="bg-surface-variant text-on-surface-variant border-t border-outline mt-8"
        >
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-sm text-center">&copy; 2025 MyRoadbook | Xavier Poncelet</p>
                <LanguageSwitcher placement="top" />
            </div>
        </footer>
    </div>
</template>
