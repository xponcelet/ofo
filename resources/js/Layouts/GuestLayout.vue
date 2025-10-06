<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import ApplicationMark from '@/Components/ApplicationMark.vue'
import NavLink from '@/Components/NavLink.vue'

const page = usePage()
const isCurrentUrl = (path) => page.url.startsWith(path)
</script>

<template>
    <header class="bg-white shadow-sm border-b border-outline">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- ✅ 1. Logo -->
            <div class="flex items-center gap-2">
                <Link href="/" class="flex items-center gap-2 group">
                    <ApplicationMark class="h-9 w-auto" />
                    <span class="text-primary-dark font-bold text-xl tracking-tight group-hover:text-accent transition-colors">
                        My<span class="text-accent">Roadbook</span>
                    </span>
                </Link>
            </div>

            <!-- ✅ 2. Navigation -->
            <div class="hidden sm:flex justify-center items-center space-x-8">
                <NavLink
                    href="/"
                    :active="isCurrentUrl('/') && !isCurrentUrl('/voyages')"
                >
                    Dashboard
                </NavLink>

                <NavLink
                    href="/voyages"
                    :active="isCurrentUrl('/voyages') && !isCurrentUrl('/trips')"
                >
                    Inspirations
                </NavLink>

                <NavLink
                    href="/login"
                    :active="isCurrentUrl('/trips')"
                >
                    Mes voyages
                </NavLink>
            </div>

            <!-- ✅ 3. Connexion -->
            <div class="hidden sm:flex items-center space-x-4">
                <Link
                    :href="route('login')"
                    class="text-sm font-medium text-primary-dark hover:text-accent"
                >
                    Se connecter
                </Link>
                <Link
                    :href="route('register')"
                    class="bg-accent text-white text-sm font-medium px-4 py-2 rounded-md hover:bg-accent-dark transition"
                >
                    S’inscrire
                </Link>
            </div>
        </nav>
    </header>
</template>
