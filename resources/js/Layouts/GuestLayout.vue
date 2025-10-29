<script setup>
import { Link } from '@inertiajs/vue3'
import ApplicationMark from '@/Components/ApplicationMark.vue'
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50 text-gray-800">
        <!-- ✅ Header visiteur -->
        <header class="bg-white shadow-sm border-b border-outline">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <Link :href="route('dashboard')" class="flex items-center gap-2 group">
                    <ApplicationMark class="h-9 w-auto" />
                    <span class="text-primary-dark font-bold text-xl tracking-tight group-hover:text-accent transition-colors">
                        My<span class="text-accent">Roadbook</span>
                    </span>
                </Link>

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

        <!-- ✅ Contenu principal -->
        <main class="flex-grow">
            <slot />
        </main>
    </div>
</template>
