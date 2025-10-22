<script setup>
import { Head, Link } from "@inertiajs/vue3"
import { ref, computed } from "vue"
import TripMiniMap from "@/Components/Trip/TripMiniMap.vue"

const props = defineProps({
    trips: { type: Object, required: true },
})

function getFlagEmoji(code) {
    if (!code) return ""
    return code
        .toUpperCase()
        .replace(/./g, (char) => String.fromCodePoint(127397 + char.charCodeAt()))
}

// Filtre actif
const activeFilter = ref("active")

// Groupes
const activeTrips = computed(() =>
    props.trips.data.filter(
        (t) =>
            t.status === "a_venir" || t.status === "en_cours" || t.status === "sans_date"
    )
)
const doneTrips = computed(() => props.trips.data.filter((t) => t.status === "termine"))

const filteredTrips = computed(() =>
    activeFilter.value === "active" ? activeTrips.value : doneTrips.value
)
</script>

<template>
    <Head title="Mes voyages" />

    <div class="max-w-6xl mx-auto px-6 py-10 space-y-10">
        <!-- 💬 Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                    <span class="text-gray-800">Mes</span>
                    <span class="text-blue-600"> voyages</span>
                </h1>
                <p class="text-gray-500 mt-1">Gérez tous vos roadbooks en un seul endroit.</p>
            </div>

            <Link
                :href="route('trips.create')"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-500 to-green-500 text-white font-medium px-5 py-2.5 rounded-lg shadow-md hover:shadow-lg transition"
            >
                <span class="material-symbols-rounded text-base">add</span>
                Créer un nouveau voyage
            </Link>
        </div>

        <!-- 🧭 Toggle -->
        <div class="flex items-center justify-center gap-2 bg-gray-100 p-1 rounded-full max-w-xs mx-auto">
            <button
                @click="activeFilter = 'active'"
                :class="[
          'flex-1 py-2 rounded-full text-sm font-medium transition',
          activeFilter === 'active'
            ? 'bg-white shadow text-gray-900'
            : 'text-gray-500 hover:text-gray-700'
        ]"
            >
                En cours & À venir
            </button>

            <button
                @click="activeFilter = 'done'"
                :class="[
          'flex-1 py-2 rounded-full text-sm font-medium transition',
          activeFilter === 'done'
            ? 'bg-white shadow text-gray-900'
            : 'text-gray-500 hover:text-gray-700'
        ]"
            >
                Terminés
            </button>
        </div>

        <!-- 📋 Liste -->
        <div v-if="filteredTrips.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="trip in filteredTrips"
                :key="trip.id"
                @click="$inertia.visit(route('trips.show', trip.id))"
                class="group relative bg-white rounded-2xl shadow-sm hover:shadow-lg transition cursor-pointer border border-gray-200 hover:border-blue-300 overflow-hidden"
            >
                <!-- 🗺️ Mini carte -->
                <TripMiniMap
                    v-if="trip.latitude && trip.longitude"
                    :latitude="trip.latitude"
                    :longitude="trip.longitude"
                />

                <!-- Contenu -->
                <div class="p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 truncate group-hover:text-blue-600">
                            {{ trip.title }}
                        </h3>
                        <span class="material-symbols-rounded text-gray-400 group-hover:text-blue-500 transition">
              chevron_right
            </span>
                    </div>

                    <div class="text-sm text-gray-600 flex items-center gap-1">
                        <span class="material-symbols-rounded text-gray-400 text-base">location_on</span>
                        {{
                            trip.destination_country
                                ? `${trip.destination_country} ${getFlagEmoji(trip.destination_country_code)}`
                                : "Destination inconnue"
                        }}
                    </div>

                    <div class="text-sm text-gray-500 flex items-center gap-1">
                        <span class="material-symbols-rounded text-gray-400 text-base">calendar_month</span>
                        {{
                            trip.departure_date
                                ? new Date(trip.departure_date).toLocaleDateString("fr-FR")
                                : "Date à définir"
                        }}
                    </div>
                </div>

                <!-- Ligne de statut -->
                <div
                    class="h-1 rounded-b-2xl"
                    :class="{
            'bg-blue-500': trip.status === 'a_venir' || trip.status === 'sans_date',
            'bg-green-500': trip.status === 'en_cours',
            'bg-gray-400': trip.status === 'termine',
          }"
                />
            </div>
        </div>

        <!-- 🚫 Aucun voyage -->
        <div v-else class="text-center py-24 text-gray-500">
            <p class="text-lg mb-2">
                Aucun voyage
                {{
                    activeFilter === "done"
                        ? "terminé pour le moment."
                        : "en cours ou à venir pour l’instant."
                }}
            </p>
            <Link
                :href="route('trips.create')"
                class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
                <span class="material-symbols-rounded">add</span>
                Créer un voyage
            </Link>
        </div>
    </div>
</template>

<style scoped>
.material-symbols-rounded {
    font-variation-settings:
        "FILL" 0,
        "wght" 400,
        "GRAD" 0,
        "opsz" 24;
}
</style>
