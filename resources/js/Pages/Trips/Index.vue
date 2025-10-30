<script setup>
import { Head, Link } from "@inertiajs/vue3"
import { ref, computed } from "vue"
import TripMiniMap from "@/Components/Trip/TripMiniMap.vue"

const props = defineProps({
    trips: { type: Object, required: true },
    limits: { type: Object, default: () => ({ count: 0, max: 5 }) },
})

// 🇺🇳 Drapeaux
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
        <!-- 💬 Messages flash -->
        <transition name="fade">
            <div
                v-if="$page.props.flash.error"
                class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800"
            >
                <strong class="font-semibold">⚠️ Limite atteinte :</strong>
                {{ $page.props.flash.error }}
            </div>
        </transition>

        <transition name="fade">
            <div
                v-if="$page.props.flash.success"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                ✅ {{ $page.props.flash.success }}
            </div>
        </transition>

        <transition name="fade">
            <div
                v-if="$page.props.flash.info"
                class="p-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-800"
            >
                {{ $page.props.flash.info }}
            </div>
        </transition>
        <!-- 🚀 Bandeau "limite atteinte" -->
        <div
            v-if="limits.count >= limits.max"
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 mb-6 rounded-xl border border-yellow-300 bg-yellow-50 text-yellow-800 shadow-sm"
        >
            <div class="flex items-center gap-2">
                <span class="material-symbols-rounded text-yellow-500 text-2xl">warning</span>
                <div>
                    <p class="font-semibold">Limite atteinte</p>
                    <p class="text-sm">Vous avez atteint la limite de {{ limits.max }} voyages gratuits.</p>
                </div>
            </div>

            <Link
                :href="route('profile.show', { checkout: 1 })"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg shadow-md hover:shadow-lg transition"
            >
                <span class="material-symbols-rounded text-base">workspace_premium</span>
                Passer Premium
            </Link>
        </div>

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
                :href="limits.count >= limits.max ? '#' : route('trips.destination')"
                :class="[
                    'inline-flex items-center gap-2 font-medium px-5 py-2.5 rounded-lg shadow-md transition',
                    limits.count >= limits.max
                        ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                        : 'bg-gradient-to-r from-blue-500 to-green-500 text-white hover:shadow-lg'
                ]"
                :aria-disabled="limits.count >= limits.max"
            >
                <span class="material-symbols-rounded text-base">add</span>
                Créer un nouveau voyage
            </Link>
        </div>

        <!-- 🧭 Toggle -->
        <div
            class="flex items-center justify-center gap-2 bg-gray-100 p-1 rounded-full max-w-xs mx-auto"
        >
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
                @click="
                  trip.role === 'used' && trip.trip_user_id
                    ? $inertia.visit(route('trips.used.show', { tripUser: trip.trip_user_id }))
                    : $inertia.visit(route('trips.show', trip.id))
                "
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

                        <!--  Pastille "Inspiré" -->
                        <span
                            v-if="trip.role === 'used'"
                            class="text-xs px-2 py-1 rounded-full bg-pink-100 text-pink-700 font-medium"
                        >
                          🌍 Inspiré
                        </span>

                        <span
                            v-else
                            class="material-symbols-rounded text-gray-400 group-hover:text-blue-500 transition"
                        >
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
                :href="limits.count >= limits.max ? '#' : route('trips.destination')"
                :class="[
                    'inline-flex items-center gap-2 mt-3 px-4 py-2 rounded-lg transition',
                    limits.count >= limits.max
                        ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                        : 'bg-blue-600 text-white hover:bg-blue-700'
                ]"
                :aria-disabled="limits.count >= limits.max"
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

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
