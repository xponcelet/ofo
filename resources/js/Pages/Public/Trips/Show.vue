<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'

import TripShowView from '@/Components/Trip/TripShowView.vue'
import TripSteps from "@/Components/Step/TripSteps.vue"
import FavoriteButton from "@/Components/FavoriteButton.vue"

const props = defineProps({
    trip: Object,
})

// Onglet actif
const currentTab = ref('steps')

// Calcul du nombre de jours
const daysCount = computed(() => {
    if (!props.trip.start_date || !props.trip.end_date) return null
    const start = new Date(props.trip.start_date)
    const end = new Date(props.trip.end_date)
    const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1
    return diff
})

// Helper
function tabClass(tab) {
    return currentTab.value === tab
        ? 'text-pink-600 border-b-2 border-pink-600 font-semibold'
        : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent'
}

function getFlagEmoji(code) {
    if (!code) return ''
    return code
        .toUpperCase()
        .replace(/./g, c => String.fromCodePoint(127397 + c.charCodeAt()))
}
</script>

<template>
    <Head :title="trip.title" />

    <div class="min-h-screen bg-gray-50 text-gray-800">
        <!-- =======================
             HEADER PUBLIC
        ======================= -->
        <section class="border-b border-gray-200 bg-white shadow-sm">
            <div class="max-w-screen-2xl mx-auto px-4 md:px-6 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                <!-- Titre -->
                <div class="flex-1">
                    <h1 class="text-3xl sm:text-4xl font-bold flex items-center gap-2 text-pink-600">
                        <span>{{ trip.title }}</span>
                        <span v-if="trip.destination_country_code" class="text-2xl leading-none">
                            {{ getFlagEmoji(trip.destination_country_code) }}
                        </span>
                    </h1>

                    <p class="text-sm text-gray-500 mt-1">
                        {{ new Date(trip.start_date).toLocaleDateString('fr-FR') }}
                        →
                        {{ new Date(trip.end_date).toLocaleDateString('fr-FR') }}
                        • {{ trip.steps?.length || 0 }} étapes
                    </p>

                    <p v-if="trip.creator" class="text-sm text-gray-500 italic">
                        Par {{ trip.creator.name }}
                    </p>
                </div>

                <!-- Compteurs (jours / étapes / public) -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center flex-col w-16 h-16 rounded-xl bg-gray-50 border border-gray-200">
                        <span class="text-xl font-semibold text-gray-900">{{ daysCount ?? '–' }}</span>
                        <span class="text-xs text-gray-500">Jours</span>
                    </div>
                    <div class="flex items-center justify-center flex-col w-16 h-16 rounded-xl bg-gray-50 border border-gray-200">
                        <span class="text-xl font-semibold text-gray-900">{{ trip.steps?.length || 0 }}</span>
                        <span class="text-xs text-gray-500">Étapes</span>
                    </div>

                    <!-- Bouton favori -->
                    <FavoriteButton
                        :trip-id="trip.id"
                        :is-favorite="trip.is_favorite"
                        class="!bg-white hover:!bg-pink-50 text-pink-500 shadow-sm ml-2"
                    />
                </div>
            </div>
        </section>

        <!-- =======================
             ONGLET PRINCIPAL
        ======================= -->
        <section class="bg-white border-b border-gray-200">
            <nav class="max-w-screen-2xl mx-auto px-4 flex gap-6 overflow-x-auto scrollbar-hide">
                <button
                    @click="currentTab = 'steps'"
                    class="py-3 text-sm transition-colors flex items-center"
                    :class="tabClass('steps')"
                >
                    🧳 Étapes
                </button>
                <button
                    @click="currentTab = 'itineraire'"
                    class="py-3 text-sm transition-colors flex items-center"
                    :class="tabClass('itineraire')"
                >
                    🗺️ Itinéraire
                </button>
            </nav>
        </section>

        <!-- =======================
             CONTENU
        ======================= -->
        <section class="max-w-screen-2xl mx-auto px-4 py-6">
            <!-- Étapes -->
            <div v-if="currentTab === 'steps' && trip.steps?.length">
                <TripSteps :trip="trip" publicView />
            </div>

            <div v-else-if="currentTab === 'steps'">
                <p class="text-gray-500 text-center py-10">Aucune étape disponible.</p>
            </div>

            <!-- Itinéraire -->
            <div v-else-if="currentTab === 'itineraire'">
                <TripShowView :steps="trip.steps" publicView />
            </div>
        </section>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
