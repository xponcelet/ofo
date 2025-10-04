<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import Banner from '@/Components/Banner.vue'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

defineProps({
    title: { type: String, default: '' },
})

const page = usePage()
const isAuth = computed(() => !!page.props?.auth?.user)
</script>

<template>
    <div class="flex flex-col min-h-screen bg-background text-on-surface">
        <!-- SEO / titre onglet -->
        <Head :title="title" />

        <!-- Bannière éventuelle -->
        <Banner />

        <!-- Header -->
        <header class="bg-surface shadow-sm border-b border-outline">
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo / titre appli -->
                    <h1 class="text-xl font-bold text-primary">MyRoadbook</h1>

                    <!-- Layout invité / connecté -->
                    <component :is="isAuth ? AppLayout : GuestLayout" :title="title" />
                </div>
            </div>
        </header>

        <!-- Contenu -->
        <main class="flex-grow w-full px-6 lg:px-12 py-8">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-surface-variant text-on-surface-variant border-t border-outline mt-8">
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">

                <!-- Lien RGPD (ouvre modal plus tard) -->
                <button type="button" class="text-sm hover:underline">
                    Politique de confidentialité
                </button>

                <!-- Copyright -->
                <p class="text-sm text-center">
                    &copy; 2025 MyRoadbook | Xavier Poncelet
                </p>

                <!-- Sélecteur de langue (dropdown vers le haut) -->
                <LanguageSwitcher placement="top" />
            </div>
        </footer>
    </div>
</template>
