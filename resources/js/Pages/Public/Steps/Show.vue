<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import PoiExplorer from '@/Components/PoiExplorer.vue'
import RootLayout from '@/Layouts/RootLayout.vue'

defineOptions({ layout: RootLayout })

const props = defineProps({
    step: Object,
    trip: Object,
    activities: {
        type: Array,
        default: () => [],
    },
})

// --- Helpers ---
function getFlagEmoji(code) {
    if (!code) return ''
    try {
        return String.fromCodePoint(...[...code.toUpperCase()].map(c => 0x1f1e6 + c.charCodeAt(0) - 65))
    } catch {
        return ''
    }
}
function normalizeDateTime(dt) {
    if (!dt) return null
    return new Date(dt.replace(' ', 'T'))
}

// --- Génération des jours entre start et end ---
function generateDays(start, end) {
    if (!start || !end) return []
    const days = []
    const cur = new Date(start)
    const endDate = new Date(end)
    while (cur <= endDate) {
        days.push(cur.toISOString().slice(0, 10))
        cur.setDate(cur.getDate() + 1)
    }
    return days
}
const days = computed(() => generateDays(props.step.start_date, props.step.end_date))

// --- Activités triées par jour ---
function activitiesForDay(day) {
    const list = props.activities.filter(a => a.start_at?.slice(0, 10) === day)
    return list.sort((a, b) => {
        const getMinutes = (x) => {
            if (!x.start_at) return null
            const d = normalizeDateTime(x.start_at)
            return d.getHours() * 60 + d.getMinutes()
        }
        const aM = getMinutes(a)
        const bM = getMinutes(b)
        if (aM === null && bM === null) return 0
        if (aM === null) return -1
        if (bM === null) return 1
        return aM - bM
    })
}

// --- Helpers d’affichage ---
function formatHour(dt) {
    if (!dt) return null
    const d = normalizeDateTime(dt)
    return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
}

// --- Style catégories ---
function categoryStyle(cat) {
    const map = {
        restaurant: { color: 'text-red-600', bg: 'bg-red-50', icon: 'restaurant' },
        hotel: { color: 'text-blue-600', bg: 'bg-blue-50', icon: 'hotel' },
        bar: { color: 'text-amber-600', bg: 'bg-amber-50', icon: 'local_bar' },
        museum: { color: 'text-purple-600', bg: 'bg-purple-50', icon: 'museum' },
        park: { color: 'text-green-600', bg: 'bg-green-50', icon: 'park' },
        attraction: { color: 'text-pink-600', bg: 'bg-pink-50', icon: 'star' },
        autre: { color: 'text-gray-600', bg: 'bg-gray-50', icon: 'location_on' },
    }
    return map[cat?.toLowerCase()] || map.autre
}
</script>

<template>
    <Head :title="step.title || 'Étape'" />

    <div class="max-w-7xl mx-auto px-4 py-10 grid lg:grid-cols-2 gap-8">
        <!-- =======================
             Colonne gauche : infos + timeline
        ======================= -->
        <div class="space-y-6">
            <!-- En-tête -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-2">
                        🧭 {{ step.title || step.location || 'Étape sans titre' }}
                    </h1>
                    <p class="text-gray-500 mt-1">
                        Voyage : <span class="font-medium text-gray-700">{{ trip?.title }}</span>
                    </p>
                </div>
            </div>

            <!-- Informations -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <h2 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                    <span class="material-symbols-rounded text-pink-500">info</span>
                    Informations sur l’étape
                </h2>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>
                        <span class="material-symbols-rounded text-sm text-pink-400 align-middle">location_on</span>
                        {{ step.location || 'Lieu non défini' }}
                    </li>
                    <li v-if="step.country">
                        <span class="material-symbols-rounded text-sm text-pink-400 align-middle">flag</span>
                        {{ getFlagEmoji(step.country_code) }} {{ step.country }}
                    </li>
                    <li v-if="step.distance_to_next">
                        <span class="material-symbols-rounded text-sm text-pink-400 align-middle">straighten</span>
                        {{ step.distance_to_next }} km jusqu’à la suivante
                    </li>
                </ul>
            </div>

            <!-- Timeline sans dates -->
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="material-symbols-rounded text-pink-500">event</span>
                    Activités
                </h2>

                <div v-if="!days.length" class="text-sm text-gray-500 italic">
                    Aucune date définie pour cette étape.
                </div>

                <div v-else class="relative">
                    <div class="absolute left-8 top-0 bottom-0 w-[2px] bg-gray-200"></div>

                    <div v-for="(day, i) in days" :key="day" class="relative pl-14 pb-10">
                        <div class="absolute left-[5px] top-1 w-5 h-5 rounded-full bg-pink-600 flex items-center justify-center text-white text-xs font-semibold shadow">
                            {{ i + 1 }}
                        </div>

                        <h3 class="font-semibold text-gray-900 mb-5">
                            Jour {{ i + 1 }}
                        </h3>

                        <!-- Activités -->
                        <div v-if="step.activities?.length" class="mt-3">
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <li
                                    v-for="a in [...step.activities].sort((a,b) => new Date(a.start_at) - new Date(b.start_at))"
                                    :key="a.id"
                                    class="bg-gray-50 border border-gray-200 rounded-xl p-3 hover:bg-gray-100 transition"
                                >
                                    <div class="flex justify-between items-start">
                                        <!-- 🔗 Titre cliquable si localisation -->
                                        <div class="font-medium text-gray-800 truncate">
                                            <template v-if="a.latitude && a.longitude">
                                                <a
                                                    :href="`https://www.google.com/maps?q=${a.latitude},${a.longitude}`"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="hover:text-pink-600 transition"
                                                    title="Ouvrir dans Google Maps"
                                                >
                                                    {{ a.title }}
                                                </a>
                                            </template>

                                            <template v-else-if="a.location">
                                                <a
                                                    :href="`https://www.google.com/maps/search/${encodeURIComponent(a.location)}`"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="hover:text-pink-600 transition"
                                                    title="Ouvrir dans Google Maps"
                                                >
                                                    {{ a.title }}
                                                </a>
                                            </template>

                                            <template v-else>
                                                {{ a.title }}
                                            </template>
                                        </div>

                                        <span v-if="a.start_at" class="text-xs text-gray-500 whitespace-nowrap">
                    {{ new Date(a.start_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                </span>
                                    </div>

                                    <p v-if="a.description" class="text-xs text-gray-600 line-clamp-2 mt-1">
                                        {{ a.description }}
                                    </p>

                                    <!-- 📍 Adresse affichée sous le titre si présente -->
                                    <p
                                        v-if="a.location"
                                        class="text-[11px] text-gray-500 mt-1 flex items-center gap-1 truncate"
                                    >
                                        <span class="material-symbols-rounded text-[14px] text-gray-400">location_on</span>
                                        {{ a.location }}
                                    </p>
                                </li>
                            </ul>
                        </div>


                        <!-- Aucun contenu -->
                        <div v-else class="text-sm text-gray-400 italic ml-6">
                            Aucune activité pour ce jour.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =======================
             Colonne droite : carte + explorer
        ======================= -->
        <div class="flex flex-col gap-4">
            <!-- Bouton retour -->
            <a
                :href="route('public.trips.show', step.trip_id)"
                class="self-end inline-flex items-center gap-1 text-sm text-gray-600 hover:text-pink-600 transition"
            >
                <span class="material-symbols-rounded text-base">arrow_back</span>
                Retour au voyage
            </a>

            <!-- Carte + Explorer -->
            <PoiExplorer
                :latitude="step.latitude"
                :longitude="step.longitude"
                public-view
            />
        </div>
    </div>
</template>

<style scoped>
.material-symbols-rounded {
    font-variation-settings:
        "FILL" 1,
        "wght" 400,
        "GRAD" 0,
        "opsz" 24;
}
</style>
