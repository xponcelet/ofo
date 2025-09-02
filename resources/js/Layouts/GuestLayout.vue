<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import ApplicationMark from '@/Components/ApplicationMark.vue'
import Banner from '@/Components/Banner.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'

defineProps({
    title: { type: String, default: '' },
})

const showingNavigationDropdown = ref(false)
</script>

<template>
    <div>
        <Head :title="title" />
        <Banner />

        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route().has('public.trips.index') ? route('public.trips.index') : '/voyages'">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Nav links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink
                                    v-if="route().has('public.trips.index')"
                                    :href="route('public.trips.index')"
                                    :active="route().current('public.trips.*')"
                                >
                                    Voyages publics
                                </NavLink>

                                <!-- Montre Dashboard uniquement si l'utilisateur est connecté -->
                                <NavLink
                                    v-if="$page.props?.auth?.user && route().has('dashboard')"
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                            </div>
                        </div>

                        <!-- Right side: login/register si invité -->
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="ms-3 flex items-center gap-4" v-if="!$page.props?.auth?.user">
                                <Link v-if="route().has('login')" :href="route('login')" class="text-sm text-gray-600 hover:text-gray-900">
                                    Se connecter
                                </Link>
                                <Link v-if="route().has('register')" :href="route('register')" class="text-sm text-gray-600 hover:text-gray-900">
                                    S’inscrire
                                </Link>
                            </div>
                            <div class="ms-3 flex items-center gap-4" v-else>
                                <Link :href="route('dashboard')" class="text-sm text-gray-600 hover:text-gray-900">
                                    Mon espace
                                </Link>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100
                       focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                            >
                                <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown}"
                                          stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 6h16M4 12h16M4 18h16"/>
                                    <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown}"
                                          stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
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

                    <div class="pt-4 pb-1 border-t border-gray-200" v-if="!$page.props?.auth?.user">
                        <div class="px-4 space-y-1">
                            <ResponsiveNavLink v-if="route().has('login')" :href="route('login')">Se connecter</ResponsiveNavLink>
                            <ResponsiveNavLink v-if="route().has('register')" :href="route('register')">S’inscrire</ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
