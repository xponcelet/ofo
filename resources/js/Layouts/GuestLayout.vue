<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import ApplicationMark from '@/Components/ApplicationMark.vue'
import NavLink from '@/Components/NavLink.vue'

const page = usePage()
const isCurrentUrl = (path) => page.url.startsWith(path)
const openMobileMenu = ref(false)

// Navigation et fermeture automatique
function handleNav(href) {
    openMobileMenu.value = false
    if (href) router.visit(href)
}
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

                <!-- Desktop navigation -->
                <div class="hidden sm:flex justify-center items-center space-x-8">
                    <NavLink :href="route('dashboard')" :active="isCurrentUrl('/dashboard')">Accueil</NavLink>
                    <NavLink :href="route('public.trips.index')" :active="isCurrentUrl('/voyages')">Inspirations</NavLink>
                    <NavLink :href="route('trips.index')" :active="isCurrentUrl('/trips')">Mes voyages</NavLink>
                </div>

                <!-- Connexion / Inscription desktop -->
                <div class="hidden sm:flex items-center space-x-6">
                    <Link :href="route('login')" class="text-sm hover:text-accent font-medium">Connexion</Link>
                    <Link
                        :href="route('register')"
                        class="text-sm bg-accent text-white px-3 py-1.5 rounded-md font-medium hover:bg-accent-dark transition"
                    >
                        Inscription
                    </Link>
                </div>

                <!-- Bouton burger mobile -->
                <button
                    @click="openMobileMenu = true"
                    class="sm:hidden inline-flex items-center justify-center p-2 rounded-full hover:bg-gray-100 text-gray-700 transition"
                    aria-label="Menu"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </nav>
        </header>

        <!-- Contenu principal -->
        <main class="flex-grow bg-gray-50 py-8">
            <slot />
        </main>

        <!-- --- Drawer Material 3 --- -->
        <transition name="slide">
            <div
                v-if="openMobileMenu"
                class="fixed inset-0 z-50 flex justify-end bg-black/40 backdrop-blur-sm"
            >
                <!-- zone clic pour fermer -->
                <div class="flex-grow" @click="openMobileMenu = false"></div>

                <!-- Drawer -->
                <aside
                    class="relative bg-white w-72 sm:w-80 h-full rounded-l-3xl shadow-2xl flex flex-col py-6 px-6 animate-slide-in"
                >
                    <!-- Bouton de fermeture -->
                    <button
                        @click="openMobileMenu = false"
                        class="absolute top-4 right-4 p-2 rounded-full hover:bg-gray-100 text-gray-700 transition"
                        aria-label="Fermer le menu"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Liens verticaux -->
                    <nav class="flex flex-col mt-12 space-y-2 text-lg font-medium text-gray-800">
                        <button @click="handleNav(route('dashboard'))" class="nav-item">Accueil</button>
                        <button @click="handleNav(route('public.trips.index'))" class="nav-item">Inspirations</button>
                        <button @click="handleNav(route('trips.index'))" class="nav-item">Mes voyages</button>

                        <hr class="my-3 border-outline/60" />

                        <button @click="handleNav(route('login'))" class="nav-item">Connexion</button>
                        <button
                            @click="handleNav(route('register'))"
                            class="nav-item bg-accent text-white hover:bg-accent-dark transition-colors"
                        >
                            Inscription
                        </button>
                    </nav>

                    <!-- Footer -->
                    <div class="mt-auto pt-8 border-t border-outline/30 text-center text-sm text-gray-400">
                        &copy; 2025 MyRoadbook
                    </div>
                </aside>
            </div>
        </transition>
    </div>
</template>

<style scoped>
/* Animation du drawer */
.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

/* Style Material des items */
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
