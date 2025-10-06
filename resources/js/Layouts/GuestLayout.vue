<script setup>
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import ApplicationMark from '@/Components/ApplicationMark.vue'
import NavLink from '@/Components/NavLink.vue'

defineProps({ title: String })
const showingNavigationDropdown = ref(false)
</script>

<template>
    <header class="bg-white shadow-sm border-b border-outline">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- ✅ 1. Logo à gauche -->
            <div class="flex items-center gap-2">
                <Link :href="route('dashboard')" class="flex items-center gap-2 group">
                    <ApplicationMark class="h-9 w-auto" />
                    <span class="text-primary-dark font-bold text-xl tracking-tight group-hover:text-accent transition-colors">
            My<span class="text-accent">Roadbook</span>
          </span>
                </Link>
            </div>

            <!-- ✅ 2. Navigation centrée -->
            <div class="hidden sm:flex justify-center items-center space-x-8">
                <NavLink :href="route('public.trips.index')" :active="route().current('public.trips.*')">Inspirations</NavLink>
                <NavLink :href="route('trips.index')" :active="route().current('trips.*')">Mes voyages</NavLink>
            </div>

            <!-- ✅ 3. Boutons à droite -->
            <div class="hidden sm:flex items-center space-x-4">
                <Link
                    v-if="route().has('login')"
                    :href="route('login')"
                    class="text-sm text-primary-dark hover:text-accent transition"
                >
                    Se connecter
                </Link>
                <Link
                    v-if="route().has('register')"
                    :href="route('register')"
                    class="bg-accent text-white text-sm font-medium px-4 py-2 rounded-xl hover:bg-primary transition"
                >
                    S’inscrire
                </Link>
            </div>
        </nav>
    </header>
</template>
