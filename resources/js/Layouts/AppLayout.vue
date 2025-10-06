<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import ApplicationMark from '@/Components/ApplicationMark.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'

defineProps({ title: String })

const showingNavigationDropdown = ref(false)
const logout = () => router.post(route('logout'))
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
                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</NavLink>
                <NavLink :href="route('public.trips.index')" :active="route().current('public.trips.*')">Inspirations</NavLink>
                <NavLink :href="route('trips.index')" :active="route().current('trips.*')">Mes voyages</NavLink>
            </div>

            <!-- ✅ 3. Profil à droite -->
            <div class="hidden sm:flex items-center space-x-4">
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button
                            v-if="$page.props.jetstream.managesProfilePhotos"
                            class="flex border-2 border-transparent rounded-full focus:outline-none focus:border-accent"
                        >
                            <img
                                class="h-9 w-9 rounded-full object-cover"
                                :src="$page.props.auth.user.profile_photo_url"
                                :alt="$page.props.auth.user.name"
                            />
                        </button>
                        <button
                            v-else
                            type="button"
                            class="text-sm font-medium text-primary-dark hover:text-accent flex items-center gap-1"
                        >
                            {{ $page.props.auth.user.name }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink :href="route('favorites.index')">Mes favoris</DropdownLink>
                        <DropdownLink :href="route('profile.show')">Profil</DropdownLink>
                        <div class="border-t border-outline" />
                        <form @submit.prevent="logout">
                            <DropdownLink as="button">Se déconnecter</DropdownLink>
                        </form>
                    </template>
                </Dropdown>
            </div>
        </nav>
    </header>

</template>
