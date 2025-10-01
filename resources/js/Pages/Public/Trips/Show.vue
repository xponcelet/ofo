<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import TripShowView from '@/Components/trip/TripShowView.vue'
import StepsMap from '@/Components/StepsMap.vue'
import GoogleMapsFullTripLink from '@/Components/GoogleMapsFullTripLink.vue'

const props = defineProps({
    trip: { type: Object, required: true },   // contient steps
    isFavorite: { type: Boolean, default: false },
})

const currentTab = ref('resume')

// Normalisation pour l’onglet Résumé (3 colonnes)
const stepsSummary = computed(() => {
    const raw = Array.isArray(props.trip?.steps) ? props.trip.steps : []
    return [...raw]
        .sort((a,b) => (a.order ?? 0) - (b.order ?? 0))
        .map((s, idx) => {
            const day = s.order ?? (idx + 1)
            return {
                id: s.id,
                day,
                short_title: `Jour ${day}`,
                title: s.title || s.location || `Étape ${day}`,
                photo_url: s.photo_url ?? null,
                description: s.description ?? null,
                coords: { lat: Number(s.latitude), lng: Number(s.longitude) },
            }
        })
})

const tripMeta = computed(() => ({
    title: props.trip.title,
    country: props.trip.location || props.trip.destination,
    start_date: props.trip.start_date,
    end_date: props.trip.end_date,
    visibility: 'public',
}))

function tabClass(val) {
    return currentTab.value === val
        ? 'bg-white shadow ring-1 ring-gray-200 text-gray-900'
        : 'text-gray-600 hover:text-gray-900'
}
</script>

<template>
    <Head :title="trip.title" />

    <div class="max-w-5xl mx-auto py-10 px-4 space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ trip.title }}</h1>
                <p class="text-sm text-gray-500 mt-1">Aperçu, infos et carte du voyage public.</p>
            </div>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium bg-emerald-50 text-emerald-700">
      Public
    </span>
        </div>

        <!-- Tabs -->
        <div class="mb-2">
            <nav class="bg-gray-50 rounded-xl p-1 flex flex-wrap gap-1">
                <button @click="currentTab = 'resume'" class="px-3 py-2 rounded-lg text-sm font-medium" :class="tabClass('resume')">🧾 Résumé</button>
                <button @click="currentTab = 'infos'" class="px-3 py-2 rounded-lg text-sm font-medium" :class="tabClass('infos')">ℹ️ Infos</button>
                <button @click="currentTab = 'map'" class="px-3 py-2 rounded-lg text-sm font-medium" :class="tabClass('map')">🗺️ Carte</button>
            </nav>
        </div>

        <!-- Onglet: Résumé (3 colonnes) -->
        <div v-if="currentTab === 'resume'">
            <div class="mb-6 flex items-center justify-between gap-4">
                <p class="text-sm text-gray-500">
                    {{ tripMeta.country }}
                    <span v-if="tripMeta.start_date && tripMeta.end_date"> · {{ tripMeta.start_date }} → {{ tripMeta.end_date }}</span>
                </p>
            </div>
            <TripShowView
                :trip-meta="tripMeta"
                :steps="stepsSummary"
                center-mode="description"
                :trip-description="trip.description"
                :trip-description-is-html="false"
            />

        </div>

        <!-- Onglet: Infos -->
        <div v-else-if="currentTab === 'infos'" class="space-y-6">
            <section class="bg-white rounded-2xl shadow p-6 ring-1 ring-gray-100 space-y-4">
                <p class="text-gray-700">{{ trip.description || 'Aucune description.' }}</p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm text-gray-600">
                    <div class="rounded-xl p-3 text-center ring-1 ring-gray-100">
                        <div class="font-semibold text-gray-800">Destination</div>
                        <div>{{ trip.destination || trip.location || '—' }}</div>
                    </div>
                    <div class="rounded-xl p-3 text-center ring-1 ring-gray-100">
                        <div class="font-semibold text-gray-800">Dates</div>
                        <div>{{ trip.start_date }} → {{ trip.end_date }}</div>
                    </div>
                    <div class="rounded-xl p-3 text-center ring-1 ring-gray-100">
                        <div class="font-semibold text-gray-800">Étapes</div>
                        <div>{{ (trip.steps || []).length }}</div>
                    </div>
                    <div class="rounded-xl p-3 text-center ring-1 ring-gray-100">
                        <div class="font-semibold text-gray-800">Visibilité</div>
                        <div>Public</div>
                    </div>
                </div>

                <div v-if="trip.image" class="mt-2">
                    <img :src="trip.image" alt="Image du voyage" class="rounded-xl shadow w-full max-h-96 object-cover">
                </div>
            </section>
        </div>

        <!-- Onglet: Carte -->
        <div v-else-if="currentTab === 'map'">
            <section class="bg-white rounded-2xl shadow p-6 ring-1 ring-gray-100">
                <header class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">🗺️ Carte du voyage</h2>
                    <GoogleMapsFullTripLink :steps="trip.steps" />
                </header>
                <div class="rounded-xl overflow-hidden ring-1 ring-gray-100">
                    <StepsMap :steps="trip.steps" />
                </div>
            </section>
        </div>
    </div>
</template>
