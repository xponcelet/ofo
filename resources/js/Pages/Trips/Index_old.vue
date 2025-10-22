<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import TripFilterToggle from '@/Components/TripFilterToggle.vue'

const props = defineProps({
    trips: { type: Object, required: true },
    limits: { type: Object, default: () => ({ max: 5, count: 0 }) },
    can: { type: Object, default: () => ({ create_trip: true }) },
})

const { props: pageProps } = usePage()
const user = computed(() => pageProps.auth?.user)

const items = computed(() => Array.isArray(props.trips?.data) ? props.trips.data : [])
const canCreate = computed(() => !!props.can?.create_trip)

// --- Toggle state
const currentFilter = ref('active')

// --- Filtered list
const filteredTrips = computed(() => {
    const list = Array.isArray(items.value) ? [...items.value] : []
    const today = new Date()

    const filtered = list.filter(trip => {
        const status = computeStatus(trip)
        return currentFilter.value === 'active'
            ? status !== 'Terminé'
            : status === 'Terminé'
    })

    if (currentFilter.value === 'active') {
        // Séparer voyages en cours et à venir
        const ongoing = []
        const upcoming = []

        for (const trip of filtered) {
            const start = trip.start_date ? new Date(trip.start_date) : null
            const end = trip.end_date ? new Date(trip.end_date) : null
            if (!start || !end) continue

            if (today >= start && today <= end) {
                ongoing.push(trip)
            } else if (today < start) {
                upcoming.push(trip)
            }
        }

        // Tri des voyages en cours → fin la plus proche
        ongoing.sort((a, b) => new Date(a.end_date) - new Date(b.end_date))

        // Tri des voyages à venir → début le plus proche
        upcoming.sort((a, b) => new Date(a.start_date) - new Date(b.start_date))

        // Regrouper : en cours d’abord, puis à venir
        return [...ongoing, ...upcoming]
    } else {
        // Voyages terminés → les plus récents en premier
        return filtered.sort((a, b) => new Date(b.end_date) - new Date(a.end_date))
    }
})


// --- Supprimer un voyage
function destroyTrip(id) {
    if (!confirm('Supprimer ce voyage ?')) return
    router.delete(route('trips.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('trips.index'), {
                replace: true,
                preserveScroll: true,
                preserveState: false,
            })
        },
    })
}

// --- Statut du voyage
function computeStatus(trip) {
    const start = trip.start_date ? new Date(trip.start_date) : null
    const end = trip.end_date ? new Date(trip.end_date) : null
    const today = new Date()
    if (!start || !end) return 'À venir'
    if (today < start) return 'À venir'
    if (today >= start && today <= end) return 'En cours'
    return 'Terminé'
}
/** Calcule la progression réelle du voyage en fonction des dates */
function computeProgress(trip) {
    const start = trip.start_date ? new Date(trip.start_date) : null
    const end = trip.end_date ? new Date(trip.end_date) : null
    const today = new Date()

    // Si dates invalides
    if (!start || !end || isNaN(start) || isNaN(end) || start > end) return 0

    // Calcul du nombre total de jours (inclusifs)
    const totalDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1

    if (today < start) return 0 // pas encore commencé
    if (today > end) return 100 // terminé

    // Jours écoulés (on compte les jours *entiers* passés)
    const elapsedDays = Math.floor((today - start) / (1000 * 60 * 60 * 24))
    const progress = Math.min((elapsedDays / totalDays) * 100, 100)

    return progress
}

// --- 🇫🇷 Emoji drapeau
function getFlagEmoji(code) {
    if (!code) return ''
    return code.toUpperCase().replace(/./g, c =>
        String.fromCodePoint(127397 + c.charCodeAt())
    )
}
</script>

<template>
    <Head title="Mes voyages" />

    <div class="max-w-7xl mx-auto px-6 py-10">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                Mes <span class="text-pink-600">voyages</span>
            </h1>

            <template v-if="user">
                <Link
                    v-if="canCreate"
                    :href="route('trips.destination')"
                    class="inline-flex items-center gap-2 bg-pink-600 text-white px-4 py-2 rounded-xl shadow hover:bg-pink-700"
                >
                    <span class="text-lg leading-none">＋</span>
                    Nouveau voyage
                </Link>

                <button
                    v-else
                    class="inline-flex items-center gap-2 bg-gray-300 text-gray-600 px-4 py-2 rounded-xl cursor-not-allowed"
                    :title="`Vous avez atteint la limite de ${limits.max} voyages`"
                >
                    <span class="text-lg leading-none">＋</span>
                    Nouveau voyage
                </button>
            </template>
        </div>

        <!-- Non connecté -->
        <div v-if="!user" class="bg-white rounded-2xl shadow-sm p-10 text-center border border-gray-100">
            <p class="text-lg text-gray-700 mb-6">
                Créez-vous un compte pour commencer à organiser votre prochain voyage ✈️
            </p>
            <Link
                :href="route('register')"
                class="inline-flex items-center justify-center gap-2 bg-pink-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-pink-700 transition"
            >
                <span class="text-lg leading-none">＋</span>
                Créer un compte
            </Link>
        </div>

        <!-- Connecté -->
        <template v-else>
            <!-- Toggle -->
            <TripFilterToggle v-model="currentFilter" />

            <!-- Liste -->
            <Transition name="fade" mode="out-in">
                <div
                    :key="currentFilter"
                    v-if="filteredTrips.length"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <article
                        v-for="trip in filteredTrips"
                        :key="trip.id"
                        class="relative rounded-2xl text-white overflow-hidden shadow-lg"
                        :class="[
                                  'bg-gradient-to-br',
                                  computeStatus(trip) === 'Terminé' ? 'from-indigo-700 to-blue-500' :
                                  computeStatus(trip) === 'En cours' ? 'from-purple-700 to-pink-500' :
                                  'from-slate-700 to-purple-500'
                                ]"
                    >
                        <Link :href="route('trips.show', trip.id)" class="block p-6 space-y-3">
                            <header class="flex justify-between items-start">
                                <h2 class="text-xl font-semibold line-clamp-1 flex items-center gap-2">
                                    <span v-if="trip.destination_country_code" class="mr-2">{{ getFlagEmoji(trip.destination_country_code) }}</span>
                                    <span>{{ trip.title || 'Sans titre' }}</span>
                                </h2>
                                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-white/20">
                                  {{ computeStatus(trip) }}
                                </span>



                            </header>

                            <div class="text-sm">
                                <p v-if="trip.start_date && trip.end_date">
                                    {{ trip.start_date }} → {{ trip.end_date }}
                                </p>
                                <p>
                                    {{ trip.days_count }} jours · {{ trip.steps_count }} étapes
                                </p>
                            </div>

                            <!-- Barre de progression -->
                            <div
                                v-if="computeStatus(trip) !== 'Terminé'"
                                class="w-full bg-white/20 h-1 rounded-full mt-3 overflow-hidden"
                            >
                                <div
                                    class="h-1 bg-white transition-all duration-500 ease-out"
                                    :style="{
                                      width:
                                        computeStatus(trip) === 'En cours'
                                          ? computeProgress(trip) + '%'
                                          : '0%',
                                    }"
                                ></div>
                            </div>


                        </Link>

                        <div class="flex justify-end gap-2 px-6 pb-4">
                            <button
                                @click.stop="destroyTrip(trip.id)"
                                class="text-xs px-2 py-1 rounded bg-red-600 hover:bg-red-700"
                            >
                                Supprimer
                            </button>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    :key="'empty-' + currentFilter"
                    class="text-gray-500 border rounded-xl p-6 mt-6 text-center"
                >
                    🚀 Aucun voyage {{ currentFilter === 'active' ? 'en cours ou à venir' : 'terminé' }}.
                </div>
            </Transition>
        </template>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
