<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    trips:  { type: Object, required: true },
    limits: { type: Object, default: () => ({ max: 5, count: 0 }) },
    can:    { type: Object, default: () => ({ create_trip: true }) },
})

const items = computed(() => Array.isArray(props.trips?.data) ? props.trips.data : [])
const canCreate = computed(() => !!props.can?.create_trip)

/** Supprimer un voyage */
function destroyTrip(id) {
    if (!confirm('Supprimer ce voyage ?')) return

    router.delete(route('trips.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('trips.index'), { replace: true, preserveScroll: true, preserveState: false })
        },
    })
}

/**  Statut du voyage */
function computeStatus(trip) {
    const start = trip.start_date ? new Date(trip.start_date) : null
    const end   = trip.end_date ? new Date(trip.end_date) : null
    const today = new Date()

    if (!start || !end) return 'À venir'
    if (today < start) return 'À venir'
    if (today >= start && today <= end) return 'En cours'
    return 'Terminé'
}

/** 🇫🇷 Convertit le code pays (FR, IT, ES...) en emoji drapeau */
function getFlagEmoji(code) {
    if (!code) return ''
    return code
        .toUpperCase()
        .replace(/./g, char =>
            String.fromCodePoint(127397 + char.charCodeAt())
        )
}
</script>

<template>
    <Head title="Mes voyages" />

    <div class="max-w-7xl mx-auto px-6 py-10">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Mes Voyages</h1>

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
        </div>

        <!-- Grille de voyages -->
        <div v-if="items.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <article
                v-for="trip in items"
                :key="trip.id"
                class="relative rounded-2xl text-white overflow-hidden shadow-lg"
                :class="[
                    'bg-gradient-to-br',
                    computeStatus(trip) === 'Terminé' ? 'from-indigo-700 to-blue-500' :
                    computeStatus(trip) === 'En cours' ? 'from-purple-700 to-pink-500' :
                    'from-slate-700 to-purple-500'
                ]"
            >
                <!-- Contenu -->
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

                    <!-- Dates + étapes -->
                    <div class="text-sm">
                        <p v-if="trip.start_date && trip.end_date">
                            {{ trip.start_date }} → {{ trip.end_date }}
                        </p>
                        <p>
                            {{ trip.days_count }} jours · {{ trip.steps_count }} étapes
                        </p>
                    </div>

                    <!-- Barre de progression -->
                    <div class="w-full bg-white/20 h-1 rounded-full mt-3">
                        <div
                            class="h-1 rounded-full bg-white"
                            :style="{ width: trip.steps_count ? Math.min(trip.steps_count * 10, 100) + '%' : '0%' }"
                        />
                    </div>
                </Link>

                <!-- Actions -->
                <div class="flex justify-end gap-2 px-6 pb-4">
                    <Link
                        :href="route('trips.edit', trip.id)"
                        class="text-xs px-2 py-1 rounded bg-white/20 hover:bg-white/30"
                    >
                        Modifier
                    </Link>
                    <button
                        @click.stop="destroyTrip(trip.id)"
                        class="text-xs px-2 py-1 rounded bg-red-600 hover:bg-red-700"
                    >
                        Supprimer
                    </button>
                </div>
            </article>
        </div>

        <!-- Vide -->
        <div v-else class="text-gray-500 border rounded-xl p-6 mt-6 text-center">
            🚀 Aucun voyage encore créé.
        </div>
    </div>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
