<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import TripSteps from '@/Components/TripSteps.vue'
import StepMapPreview from '@/Components/StepMapPreview.vue'
import GoogleMapsNavigationLink from "@/Components/GoogleMapsNavigationLink.vue";

const props = defineProps({
    trip: Object,
    steps: Array,
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

            <!-- Contenu des onglets -->
            <div v-if="currentTab === 'infos'" class="space-y-4">
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

                    <div v-if="trip.image" class="mt-4">
                        <img :src="trip.image" alt="Image du voyage" class="rounded-lg shadow w-full max-h-96 object-cover">
                    </div>
                </div>
            </div>

            <div v-if="currentTab === 'steps'">
                <TripSteps :trip="trip" />
            </div>

            <div v-if="currentTab === 'map'">
                <StepMapPreview :steps="steps" />

                <div class="mt-3">
                    <GoogleMapsNavigationLink
                        :latitude="steps?.[0]?.latitude"
                        :longitude="steps?.[0]?.longitude"
                    />
                </div>
            </div>

        </div>
    </AppLayout>
</template>
