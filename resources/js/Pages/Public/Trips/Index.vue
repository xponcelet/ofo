<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    trips: { type: Object, required: true },
})

const items = computed(() =>
    Array.isArray(props.trips?.data) ? props.trips.data : []
)

/** 🇫🇷 Convertit un code pays (FR, ES...) en emoji drapeau */
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
    <Head title="Voyages publics" />

    <div class="max-w-7xl mx-auto px-6 py-10">
        <!-- =======================
             Header
        ======================= -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-3">
            <h1 class="text-3xl font-bold text-gray-800">Voyages publics</h1>

            <Link
                :href="route('public.trips.random')"
                class="inline-flex items-center gap-2 bg-pink-600 text-white px-4 py-2 rounded-xl shadow hover:bg-pink-700 transition"
            >
                🎲 Voyage aléatoire
            </Link>
        </div>

        <!-- =======================
             Grille de voyages
        ======================= -->
        <div
            v-if="items.length"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            <article
                v-for="trip in items"
                :key="trip.id"
                class="relative rounded-2xl text-white overflow-hidden shadow-lg
                       bg-gradient-to-br from-purple-700 to-pink-500"
            >
                <Link
                    :href="route('public.trips.show', trip.id)"
                    class="block p-6 space-y-3"
                >
                    <!-- Titre -->
                    <header class="flex items-start gap-2">
                        <h2 class="text-xl font-semibold line-clamp-1 flex items-center gap-2">
                            <span v-if="trip.destination_country_code" class="mr-1">
                                {{ getFlagEmoji(trip.destination_country_code) }}
                            </span>
                            <span>{{ trip.title || 'Sans titre' }}</span>
                        </h2>
                    </header>

                    <!-- Description uniquement si existante -->
                    <p
                        v-if="trip.description"
                        class="text-sm opacity-90 line-clamp-2"
                    >
                        {{ trip.description }}
                    </p>

                    <!-- Nombre d’étapes et jours -->
                    <p class="text-xs opacity-80">
                        {{ trip.steps_count ?? 0 }} étapes
                        <span v-if="trip.days_count">• {{ trip.days_count }} jours</span>
                    </p>
                </Link>
            </article>
        </div>

        <!-- =======================
             Vide
        ======================= -->
        <div
            v-else
            class="text-gray-500 border rounded-xl p-6 mt-6 text-center"
        >
            🌍 Aucun voyage public pour le moment.
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
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
