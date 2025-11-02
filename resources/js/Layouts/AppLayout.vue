<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import ApplicationMark from '@/Components/ApplicationMark.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'

defineProps({ title: String })

const page = usePage()
const logout = () => router.post(route('logout'))
const isCurrentUrl = (path) => page.url.startsWith(path)
const openMobileMenu = ref(false)

// Ferme le menu et navigue
function handleNav(href) {
    openMobileMenu.value = false
    if (href) router.visit(href)
}
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50">
        <header class="bg-white shadow-sm border-b border-outline relative z-40">
            <nav
                class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
            >
                <!-- Logo -->
                <Link :href="route('dashboard')" class="flex items-center gap-2 group">
                    <ApplicationMark class="h-9 w-auto" />
                    <span
                        class="text-primary-dark font-bold text-xl tracking-tight group-hover:text-accent transition-colors"
                    >
            My<span class="text-accent">Roadbook</span>
          </span>
                </Link>

                <!-- Navigation desktop -->
                <div class="hidden sm:flex justify-center items-center space-x-8">
                    <NavLink
                        :href="route('dashboard')"
                        :active="isCurrentUrl('/dashboard')"
                    >Dashboard</NavLink
                    >
                    <NavLink
                        :href="route('public.trips.index')"
                        :active="isCurrentUrl('/voyages')"
                    >Inspirations</NavLink
                    >
                    <NavLink
                        :href="route('trips.index')"
                        :active="isCurrentUrl('/trips')"
                    >Mes voyages</NavLink
                    >
                </div>

                <!-- Profil desktop -->
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
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                    />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('favorites.index')"
                            >Mes favoris</DropdownLink
                            >
                            <DropdownLink :href="route('profile.show')">Profil</DropdownLink>
                            <DropdownLink
                                v-if="$page.props.auth?.user?.role === 'admin'"
                                :href="route('admin.users.index')"
                                class="text-pink-700 font-semibold flex items-center gap-2"
                            >
                                🛠️ Espace admin
                            </DropdownLink>
                            <div class="border-t border-outline" />
                            <form @submit.prevent="logout">
                                <DropdownLink as="button">Se déconnecter</DropdownLink>
                            </form>
                        </template>
                    </Dropdown>
                </div>

                <!-- Bouton burger mobile -->
                <button
                    @click="openMobileMenu = true"
                    class="sm:hidden inline-flex items-center justify-center p-2 rounded-full hover:bg-gray-100 text-gray-700 transition"
                    aria-label="Menu"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>
            </nav>

            <!-- ✅ Bannière en dessous des onglets -->
            <transition name="fade">
                <div
                    v-if="$page.props?.flash?.error"
                    class="bg-red-600 text-white text-center py-3 shadow-md z-30"
                >
          <span class="font-medium">
            Vous avez atteint la limite maximale de voyages.
          </span>
                    <Link
                        :href="route('profile.show')"
                        class="ml-2 underline font-semibold hover:text-yellow-200 transition"
                    >
                        Passer en mode premium
                    </Link>
                </div>
            </transition>

            <transition name="fade">
                <div
                    v-if="$page.props?.flash?.success"
                    class="bg-green-600 text-white text-center py-3 shadow-md z-30"
                >
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>
            </transition>
        </header>

        <main class="flex-grow bg-gray-50 py-8">
            <slot />
        </main>

        <!-- --- Drawer Material 3 mobile --- -->
        <transition name="slide">
            <div
                v-if="openMobileMenu"
                class="fixed inset-0 z-50 flex justify-end bg-black/40 backdrop-blur-sm"
            >
                <!-- overlay -->
                <div class="flex-grow" @click="openMobileMenu = false"></div>

                <!-- drawer -->
                <aside
                    class="relative bg-white w-72 sm:w-80 h-full rounded-l-3xl shadow-2xl flex flex-col py-6 px-6 animate-slide-in"
                >
                    <!-- close btn -->
                    <button
                        @click="openMobileMenu = false"
                        class="absolute top-4 right-4 p-2 rounded-full hover:bg-gray-100 text-gray-700 transition"
                        aria-label="Fermer le menu"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    <!-- nav links -->
                    <nav
                        class="flex flex-col mt-12 space-y-2 text-lg font-medium text-gray-800"
                    >
                        <button @click="handleNav(route('dashboard'))" class="nav-item">
                            Dashboard
                        </button>
                        <button
                            @click="handleNav(route('public.trips.index'))"
                            class="nav-item"
                        >
                            Inspirations
                        </button>
                        <button @click="handleNav(route('trips.index'))" class="nav-item">
                            Mes voyages
                        </button>
                        <button
                            @click="handleNav(route('favorites.index'))"
                            class="nav-item"
                        >
                            Mes favoris
                        </button>
                        <button @click="handleNav(route('profile.show'))" class="nav-item">
                            Profil
                        </button>

                        <hr class="my-3 border-outline/60" />

                        <button
                            @click="() => { logout(); openMobileMenu = false }"
                            class="nav-item text-red-600 hover:bg-red-50 hover:text-red-700"
                        >
                            Se déconnecter
                        </button>
                    </nav>

                    <!-- footer -->
                    <div
                        class="mt-auto pt-8 border-t border-outline/30 text-center text-sm text-gray-400"
                    >
                        &copy; 2025 MyRoadbook
                    </div>
                </aside>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

.nav-item {
    display: block;
    width: 100%;
    text-align: left;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    transition: background-color 0.2s, color 0.2s, transform 0.1s;
}
.nav-item:hover {
    background-color: rgb(240 249 255);
    color: rgb(37 99 235);
}
.nav-item:active {
    transform: scale(0.97);
}
</style>
