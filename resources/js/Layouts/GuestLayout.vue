<script setup>
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import ApplicationMark from '@/Components/ApplicationMark.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'

defineProps({ title: String })

const showingNavigationDropdown = ref(false)
</script>

<template>
    <!-- Partie gauche de la nav -->
    <div class="flex">
        <!-- Logo -->
        <div class="shrink-0 flex items-center">
            <Link :href="route().has('public.trips.index') ? route('public.trips.index') : '/'">
                <ApplicationMark class="block h-9 w-auto" />
            </Link>
        </div>

        <!-- Liens principaux -->
        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
            <NavLink
                v-if="route().has('public.trips.index')"
                :href="route('public.trips.index')"
                :active="route().current('public.trips.*')"
            >
                Inspirations
            </NavLink>

            <!-- Dashboard visible si connecté -->
            <NavLink
                v-if="$page.props?.auth?.user && route().has('dashboard')"
                :href="route('dashboard')"
                :active="route().current('dashboard')"
            >
                Dashboard
            </NavLink>
        </div>
    </div>

    <!-- Partie droite de la nav -->
    <div class="hidden sm:flex sm:items-center sm:ms-6">
        <!-- Boutons invité -->
        <div v-if="!$page.props?.auth?.user" class="ms-3 flex items-center gap-4">
            <Link
                v-if="route().has('login')"
                :href="route('login')"
                class="text-sm text-gray-600 hover:text-gray-900"
            >
                Se connecter
            </Link>
            <Link
                v-if="route().has('register')"
                :href="route('register')"
                class="text-sm text-gray-600 hover:text-gray-900"
            >
                S’inscrire
            </Link>
        </div>

        <!-- Bouton si déjà connecté -->
        <div v-else class="ms-3 flex items-center gap-4">
            <Link :href="route('dashboard')" class="text-sm text-gray-600 hover:text-gray-900">
                Mon espace
            </Link>
        </div>
    </div>

    <!-- Hamburger (responsive) -->
    <div class="-me-2 flex items-center sm:hidden">
        <button
            @click="showingNavigationDropdown = !showingNavigationDropdown"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition"
        >
            <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path
                    :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                    :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
    </div>

    <!-- Menu responsive -->
    <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <ResponsiveNavLink
                v-if="route().has('public.trips.index')"
                :href="route('public.trips.index')"
                :active="route().current('public.trips.*')"
            >
                Voyages publics
            </ResponsiveNavLink>

            <ResponsiveNavLink
                v-if="$page.props?.auth?.user && route().has('dashboard')"
                :href="route('dashboard')"
                :active="route().current('dashboard')"
            >
                Dashboard
            </ResponsiveNavLink>
        </div>

        <!-- Login/register dans le menu mobile -->
        <div v-if="!$page.props?.auth?.user" class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4 space-y-1">
                <ResponsiveNavLink v-if="route().has('login')" :href="route('login')">
                    Se connecter
                </ResponsiveNavLink>
                <ResponsiveNavLink v-if="route().has('register')" :href="route('register')">
                    S’inscrire
                </ResponsiveNavLink>
            </div>
        </div>
    </div>
</template>
