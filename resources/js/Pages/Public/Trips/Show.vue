<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'

import TripShowView from '@/Components/Trip/TripShowView.vue'
import TripSteps from "@/Components/Step/TripSteps.vue"
import FavoriteButton from "@/Components/FavoriteButton.vue"
import PublicUseModal from "@/Components/Trip/PublicUseModal.vue"

const props = defineProps({
    trip: Object,
    tripUser: { type: Object, default: null }, // état "voyage utilisé"
    auth: { type: Object, default: null },     // infos utilisateur connecté
})

// Onglet actif
const currentTab = ref('steps')

// État UI modale "Utiliser ce voyage"
const showUseModal = ref(false)

// Helper: classes d’onglet
function tabClass(tab) {
    return currentTab.value === tab
        ? 'text-pink-600 border-b-2 border-pink-600 font-semibold'
        : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent'
}

// Drapeau pays
function getFlagEmoji(code) {
    if (!code) return ''
    return code
        .toUpperCase()
        .replace(/./g, c => String.fromCodePoint(127397 + c.charCodeAt()))
}

// Jours entre start/end du voyage public (info purement indicative)
const daysCount = computed(() => {
    if (!props.trip.start_date || !props.trip.end_date) return null
    const start = new Date(props.trip.start_date)
    const end = new Date(props.trip.end_date)
    const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1
    return diff
})

// L’utilisateur utilise-t-il ce voyage ?
const isUsed = computed(() => !!props.tripUser)

// CSRF (utile si tu réactives des <form> plus tard)
const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
</script>

<template>
    <Head :title="trip.title" />

    <div class="min-h-screen bg-gray-50 text-gray-800">
        <!-- =======================
             HEADER PUBLIC
        ======================= -->
        <section class="border-b border-gray-200 bg-white shadow-sm">
            <div
                class="max-w-screen-2xl mx-auto px-4 md:px-6 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6"
            >
                <!-- Titre -->
                <div class="flex-1">
                    <h1
                        class="text-3xl sm:text-4xl font-bold flex items-center gap-2 text-pink-600"
                    >
                        <span>{{ trip.title }}</span>
                        <span
                            v-if="trip.destination_country_code"
                            class="text-2xl leading-none"
                        >
              {{ getFlagEmoji(trip.destination_country_code) }}
            </span>
                    </h1>
                    <p v-if="trip.creator" class="text-sm text-gray-500 italic">
                        Par {{ trip.creator.name }}
                    </p>

                    <!-- Bandeau d'état si le voyage est "utilisé" -->
                    <div
                        v-if="isUsed"
                        class="mt-3 p-3 rounded-lg border bg-pink-50 text-pink-800 border-pink-200 text-sm flex flex-col sm:flex-row sm:items-center gap-2"
                    >
                        <div class="flex-1">
                            ✈️ Vous utilisez ce voyage
                            <span v-if="tripUser?.start_location"
                            >— départ :
                <strong>{{ tripUser.start_location }}</strong></span
                            >
                            <span v-if="tripUser?.departure_date">
                · du
                <strong>{{
                        new Date(tripUser.departure_date).toLocaleDateString('fr-FR')
                    }}</strong>
              </span>
                            <span v-if="tripUser?.end_date">
                au
                <strong>{{
                        new Date(tripUser.end_date).toLocaleDateString('fr-FR')
                    }}</strong>
              </span>
                        </div>

                        <!-- Actions associées -->
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="px-3 py-1.5 rounded-md border border-pink-300 bg-white text-pink-700 hover:bg-pink-50"
                                @click="showUseModal = true"
                            >
                                Modifier le départ
                            </button>

                            <!-- (Optionnel) Bouton Dupliquer ici si tu veux le rendre visible plus tard -->
                            <!--
                            <form :action="route('trips.duplicate', trip.id)" method="post">
                              <input type="hidden" name="_token" :value="csrfToken" />
                              <button
                                type="submit"
                                class="px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
                              >
                                Dupliquer
                              </button>
                            </form>
                            -->
                        </div>
                    </div>
                </div>

                <!-- Compteurs & Actions -->
                <div class="flex items-center gap-4">
                    <div
                        class="flex items-center justify-center flex-col w-16 h-16 rounded-xl bg-gray-50 border border-gray-200"
                    >
            <span class="text-xl font-semibold text-gray-900">{{
                    daysCount ?? '–'
                }}</span>
                        <span class="text-xs text-gray-500">Jours</span>
                    </div>
                    <div
                        class="flex items-center justify-center flex-col w-16 h-16 rounded-xl bg-gray-50 border border-gray-200"
                    >
            <span class="text-xl font-semibold text-gray-900">{{
                    trip.steps?.length || 0
                }}</span>
                        <span class="text-xs text-gray-500">Étapes</span>
                    </div>

                    <!-- Groupe d’actions -->
                    <div class="flex items-center gap-2">
                        <FavoriteButton
                            :trip-id="trip.id"
                            :is-favorite="trip.is_favorite"
                            class="!bg-white hover:!bg-pink-50 text-pink-500 shadow-sm"
                        />

                        <!-- Bouton "Utiliser" aligné avec Favori -->
                        <button
                            v-if="props.auth?.user && !isUsed"
                            type="button"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-pink-600 text-white hover:bg-pink-700 shadow-sm"
                            @click="showUseModal = true"
                        >
                            <span>✈️</span>
                            <span>Utiliser</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- =======================
             ONGLET PRINCIPAL
        ======================= -->
        <section class="bg-white border-b border-gray-200">
            <nav
                class="max-w-screen-2xl mx-auto px-4 flex gap-6 overflow-x-auto scrollbar-hide"
            >
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
            <div v-if="currentTab === 'steps' && trip.steps?.length">
                <TripSteps :trip="trip" publicView />
            </div>

            <div v-else-if="currentTab === 'steps'">
                <p class="text-gray-500 text-center py-10">
                    Aucune étape disponible.
                </p>
            </div>

            <div v-else-if="currentTab === 'itineraire'">
                <TripShowView :steps="trip.steps" publicView />
            </div>
        </section>

        <!-- =======================
             MODALE "Utiliser ce voyage"
        ======================= -->
        <PublicUseModal
            :trip-id="trip.id"
            :show="showUseModal"
            @close="showUseModal = false"
        />
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
