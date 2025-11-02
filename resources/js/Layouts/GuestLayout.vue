<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import ApplicationMark from '@/Components/ApplicationMark.vue'
import NavLink from '@/Components/NavLink.vue'

const page = usePage()
const isCurrentUrl = (path) => page.url.startsWith(path)
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50 text-gray-800">
        <header class="bg-white shadow-sm border-b border-outline">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Logo -->
                <Link :href="route('dashboard')" class="flex items-center gap-2 group">
                    <ApplicationMark class="h-9 w-auto" />
                    <span class="text-primary-dark font-bold text-xl tracking-tight group-hover:text-accent transition-colors">
                        My<span class="text-accent">Roadbook</span>
                    </span>
                </Link>

                <!-- Navigation -->
                <div class="hidden sm:flex justify-center items-center space-x-8">
                    <NavLink :href="route('dashboard')" :active="isCurrentUrl('/dashboard')">Dashboard</NavLink>
                    <NavLink :href="route('public.trips.index')" :active="isCurrentUrl('/voyages')">Inspirations</NavLink>
                    <NavLink :href="route('trips.index')" :active="isCurrentUrl('/trips')">Mes voyages</NavLink>
                </div>

                <!-- Connexion / Inscription -->
                <div class="flex items-center space-x-6">
                    <Link :href="route('login')" class="text-sm hover:text-accent font-medium">Connexion</Link>
                    <Link
                        :href="route('register')"
                        class="text-sm bg-accent text-white px-3 py-1.5 rounded-md font-medium hover:bg-accent-dark transition"
                    >
                        Inscription
                    </Link>
                </div>
            </nav>
        </header>

        <main class="flex-grow bg-gray-50 py-8">
            <slot />
        </main>
    </div>
</template>
