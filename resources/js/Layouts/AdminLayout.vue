<script setup>
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- En-tête admin -->
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">🛠️ Administration</h1>

                <nav class="flex items-center space-x-6 text-sm">
                    <Link
                        :href="route('admin.users.index')"
                        :class="[
                            'hover:text-pink-600 transition',
                            page.url.startsWith('/admin/users')
                                ? 'text-pink-600 font-semibold'
                                : 'text-gray-700'
                        ]"
                    >
                        Utilisateurs
                    </Link>

                    <Link
                        :href="route('admin.trips.index')"
                        :class="[
                            'hover:text-pink-600 transition',
                            page.url.startsWith('/admin/trips')
                                ? 'text-pink-600 font-semibold'
                                : 'text-gray-700'
                        ]"
                    >
                        Voyages
                    </Link>

                    <Link
                        :href="route('dashboard')"
                        class="bg-pink-600 hover:bg-pink-700 text-white px-3 py-1.5 rounded-md text-xs font-medium"
                    >
                        Retour au site
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Contenu -->
        <main class="py-8">
            <slot />
        </main>
    </div>
</template>
