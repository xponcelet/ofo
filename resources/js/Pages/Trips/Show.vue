<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import TripSteps from '@/Components/TripSteps.vue'
import StepsMap from '@/Components/StepsMap.vue'
import GoogleMapsFullTripLink from '@/Components/GoogleMapsFullTripLink.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    trip: Object,
    steps: Array,
    favs: Number,    // <- total des likes (fourni par le contrôleur)
    likers: Object,  // <- paginator OU null si non-proprio (fourni par le contrôleur)
})

const currentTab = ref('infos')
</script>

<template>
    <AppLayout :title="trip.title">
        <div class="max-w-5xl mx-auto py-10 px-4 space-y-6">
            <!-- Onglets -->
            <div class="flex gap-4 border-b mb-6">
                <button
                    @click="currentTab = 'infos'"
                    :class="['pb-2', currentTab === 'infos' ? 'border-b-2 border-blue-600 text-blue-600 font-medium' : 'text-gray-600']"
                >
                    Infos générales
                </button>
                <button
                    @click="currentTab = 'steps'"
                    :class="['pb-2', currentTab === 'steps' ? 'border-b-2 border-blue-600 text-blue-600 font-medium' : 'text-gray-600']"
                >
                    Étapes
                </button>
                <button
                    @click="currentTab = 'map'"
                    :class="['pb-2', currentTab === 'map' ? 'border-b-2 border-blue-600 text-blue-600 font-medium' : 'text-gray-600']"
                >
                    Carte
                </button>
            </div>

            <!-- Onglet: Infos générales -->
            <div v-if="currentTab === 'infos'" class="space-y-6">
                <div class="bg-white rounded-xl shadow p-6 space-y-4">
                    <p class="text-gray-700">{{ trip.description || 'Aucune description.' }}</p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm text-gray-600">
                        <div class="bg-gray-100 rounded-lg p-3 text-center">
                            <div class="font-semibold text-gray-800">Destination</div>
                            <div>{{ trip.destination }}</div>
                        </div>
                        <div class="bg-gray-100 rounded-lg p-3 text-center">
                            <div class="font-semibold text-gray-800">Dates</div>
                            <div>{{ trip.start_date }} → {{ trip.end_date }}</div>
                        </div>
                        <div class="bg-gray-100 rounded-lg p-3 text-center">
                            <div class="font-semibold text-gray-800">Budget</div>
                            <div>{{ trip.budget }} {{ trip.currency }}</div>
                        </div>
                        <div class="bg-gray-100 rounded-lg p-3 text-center">
                            <div class="font-semibold text-gray-800">Visibilité</div>
                            <div>{{ trip.is_public ? 'Public' : 'Privé' }}</div>
                        </div>
                    </div>

                    <!-- Compteur de likes -->
                    <div class="pt-2 inline-flex items-center gap-2 text-sm text-gray-700" :title="`${favs ?? 0} favoris`" aria-label="Nombre de favoris">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 fill-red-500">
                            <path d="M12 21s-7.5-4.6-9.4-8.5A5.6 5.6 0 0 1 12 5.7a5.6 5.6 0 0 1 9.4 6.8C19.5 16.4 12 21 12 21Z"/>
                        </svg>
                        <span><strong>{{ favs ?? 0 }}</strong> personnes ont aimé ce voyage</span>
                    </div>

                    <div v-if="trip.image" class="mt-4">
                        <img :src="trip.image" alt="Image du voyage" class="rounded-lg shadow w-full max-h-96 object-cover">
                    </div>
                </div>

                <!-- Liste des personnes ayant liké (seulement si le contrôleur fournit `likers`, donc propriétaire) -->
                <section v-if="likers && likers.data?.length" class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-lg font-semibold mb-3">
                        Personnes ayant aimé ({{ likers.total }})
                    </h2>

                    <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <li v-for="u in likers.data" :key="u.id" class="p-3 border rounded-xl flex items-center gap-3">
                            <img
                                :src="u.profile_photo_url || '/img/default-avatar.png'"
                                class="w-10 h-10 rounded-full object-cover"
                                alt=""
                            />
                            <div>
                                <div class="font-medium">{{ u.name }}</div>
                                <div class="text-xs text-gray-500" v-if="u.email">{{ u.email }}</div>
                            </div>
                        </li>
                    </ul>

                    <!-- Pagination likers -->
                    <div class="flex items-center gap-2 mt-4">
                        <Link v-if="likers.prev_page_url" :href="likers.prev_page_url" preserve-scroll class="px-3 py-1 border rounded">
                            Précédent
                        </Link>
                        <span class="text-sm">Page {{ likers.current_page }} / {{ likers.last_page }}</span>
                        <Link v-if="likers.next_page_url" :href="likers.next_page_url" preserve-scroll class="px-3 py-1 border rounded">
                            Suivant
                        </Link>
                    </div>
                </section>
            </div>

            <!-- Onglet: Étapes -->
            <div v-if="currentTab === 'steps'">
                <TripSteps :trip="trip" />
            </div>

            <!-- Onglet: Carte -->
            <div v-if="currentTab === 'map'">
                <StepsMap :steps="steps" />
                <div class="mt-3">
                    <GoogleMapsFullTripLink :steps="steps" class="mt-3" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
