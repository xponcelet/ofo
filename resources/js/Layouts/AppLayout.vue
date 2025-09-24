<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import ApplicationMark from '@/Components/ApplicationMark.vue'
import NavLink from '@/Components/NavLink.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'

defineProps({ title: String })

const showingNavigationDropdown = ref(false)
const logout = () => router.post(route('logout'))
</script>

<template>
    <!-- Code inchangé de AppLayout entre <div class="flex"> ... </div> -->
    <!-- ✅ ici tu gardes le logo, les liens, le profil utilisateur, le hamburger, etc. -->
    <div class="flex">
        <!-- Logo -->
        <div class="shrink-0 flex items-center">
            <Link :href="route('dashboard')">
                <ApplicationMark class="block h-9 w-auto" />
            </Link>
        </div>

        <!-- Navigation Links -->
        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                Dashboard
            </NavLink>
            <NavLink :href="route('public.trips.index')" :active="route().current('public.trips.*')">
                Voyages publics
            </NavLink>
            <NavLink v-if="$page.props.auth?.user" :href="route('trips.index')" :active="route().current('trips.*')">
                Mes voyages
            </NavLink>
        </div>
    </div>

    <!-- User Dropdown (inchangé) -->
    <div v-if="$page.props.auth?.user" class="hidden sm:flex sm:items-center sm:ms-6">
        <Dropdown align="right" width="48">
            <template #trigger>
                <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full">
                    <img class="size-8 rounded-full object-cover"
                         :src="$page.props.auth.user.profile_photo_url"
                         :alt="$page.props.auth.user.name" />
                </button>
                <span v-else class="inline-flex rounded-md">
          <button type="button" class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium text-gray-500 bg-white hover:text-gray-700">
            {{ $page.props.auth.user.name }}
            <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
          </button>
        </span>
            </template>

            <template #content>
                <DropdownLink :href="route('favorites.index')">Mes favoris</DropdownLink>
                <div class="block px-4 py-2 text-xs text-gray-400">Manage Account</div>
                <DropdownLink :href="route('profile.show')">Profile</DropdownLink>
                <div class="border-t border-gray-200" />
                <form @submit.prevent="logout">
                    <DropdownLink as="button">Log Out</DropdownLink>
                </form>
            </template>
        </Dropdown>
    </div>

    <!-- Hamburger menu (inchangé aussi) -->
    <div class="-me-2 flex items-center sm:hidden">
        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500">
            <!-- ... tes icônes hamburger ... -->
        </button>
    </div>

    <!-- Menu responsive (inchangé) -->
    <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="sm:hidden">
        <!-- ... ton code responsive ... -->
    </div>
</template>
