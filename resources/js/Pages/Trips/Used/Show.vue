<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'

// Composants
import TripSteps from '@/Components/Step/TripSteps.vue'
import TripShowView from '@/Components/Trip/TripShowView.vue'
import TripChecklist from '@/Components/Trip/TripChecklist.vue'
import FavoriteButton from '@/Components/FavoriteButton.vue'

const props = defineProps({
    trip: Object,
    tripUser: Object,
    states: { type: Object, default: () => ({}) },
})

// Onglet actif
const currentTab = ref('steps')

// Classes d’onglets
function tabClass(tab) {
    return currentTab.value === tab
        ? 'text-pink-600 border-b-2 border-pink-600 font-semibold'
        : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent'
}

// Drapeau pays
function getFlagEmoji(code) {
    if (!code) return ''
    return code.toUpperCase().replace(/./g, c => String.fromCodePoint(127397 + c.charCodeAt()))
}

// Calcul durée
const daysCount = computed(() => {
    if (!props.trip.start_date || !props.trip.end_date) return null
    const start = new Date(props.trip.start_date)
    const end = new Date(props.trip.end_date)
    return Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1
})
</script>

<template>
    <Head :title="`Mon voyage inspiré : ${trip.title}`" />

    <div class="min-h-screen bg-gray-50 text-gray-800">
        <!-- HEADER -->
        <section class="border-b border-gray-200 bg-white shadow-sm">
            <div
                class="max-w-screen-2xl mx-auto px-4 md:px-6 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6"
            >
                <div class="flex-1">
                    <h1
                        class="text-3xl sm:text-4xl font-bold text-pink-600 flex items-center gap-2"
                    >
                        <span>{{ trip.title }}</span>
                        <span v-if="trip.destination_country_code" class="text-2xl leading-none">
                            {{ getFlagEmoji(trip.destination_country_code) }}
                        </span>
                    </h1>

                    <p class="text-sm text-gray-500 italic">
                        Inspiré d’un voyage public
                    </p>

                    <div class="mt-2 text-sm text-gray-600 space-y-1">
                        <p v-if="tripUser.start_location">
                            <span class="font-medium">Départ :</span>
                            {{ tripUser.start_location }}
                        </p>
                        <p v-if="tripUser.departure_date">
                            <span class="font-medium">Date de départ :</span>
                            {{ new Date(tripUser.departure_date).toLocaleDateString('fr-FR') }}
                        </p>
                        <p v-if="trip.start_date && trip.end_date">
                            <span class="font-medium">Période du voyage :</span>
                            {{ new Date(trip.start_date).toLocaleDateString('fr-FR') }} →
                            {{ new Date(trip.end_date).toLocaleDateString('fr-FR') }}
                        </p>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="flex items-center gap-4">
                    <div
                        class="flex flex-col items-center justify-center w-16 h-16 bg-gray-50 border border-gray-200 rounded-xl"
                    >
                        <span class="text-xl font-semibold text-gray-900">{{ daysCount ?? '–' }}</span>
                        <span class="text-xs text-gray-500">Jours</span>
                    </div>
                    <div
                        class="flex flex-col items-center justify-center w-16 h-16 bg-gray-50 border border-gray-200 rounded-xl"
                    >
                        <span class="text-xl font-semibold text-gray-900">{{ trip.steps?.length || 0 }}</span>
                        <span class="text-xs text-gray-500">Étapes</span>
                    </div>

                    <FavoriteButton
                        :trip-id="trip.id"
                        :is-favorite="trip.is_favorite"
                        class="!bg-white hover:!bg-pink-50 text-pink-500 shadow-sm"
                    />
                </div>
            </div>
        </section>

        <!-- ONGLET -->
        <section class="bg-white border-b border-gray-200">
            <nav class="max-w-screen-2xl mx-auto px-4 flex gap-6 overflow-x-auto scrollbar-hide">
                <button
                    @click="currentTab = 'steps'"
                    :class="tabClass('steps')"
                    class="py-3 text-sm flex items-center"
                >🧳 Étapes</button>

                <button
                    @click="currentTab = 'itineraire'"
                    :class="tabClass('itineraire')"
                    class="py-3 text-sm flex items-center"
                >🗺️ Itinéraire</button>

                <button
                    @click="currentTab = 'checklist'"
                    :class="tabClass('checklist')"
                    class="py-3 text-sm flex items-center"
                >✅ Checklist</button>
            </nav>
        </section>

        <!-- CONTENU -->
        <section class="max-w-screen-2xl mx-auto px-4 py-6">
            <div v-if="currentTab === 'steps'">
                <TripSteps :trip="trip" publicView />
            </div>

            <div v-if="currentTab === 'itineraire'">
                <TripShowView :steps="trip.steps" publicView />
            </div>

            <div v-if="currentTab === 'checklist'">
                <TripChecklist
                    :trip="trip"
                    :items="trip.checklist_items ?? []"
                    :states="states ?? {}"
                    title="Ma checklist personnalisée"
                    width="max-w-3xl"
                />
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
