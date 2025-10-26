<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import FavoriteButton from '@/Components/FavoriteButton.vue'

const props = defineProps({
    trips: { type: Object, required: true },
})

// Pagination compatible avec Inertia
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
                       bg-gradient-to-br from-purple-700 to-pink-500 hover:scale-[1.02] transition-transform"
            >
                <!-- Lien vers le détail -->
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

                    <!-- Description -->
                    <p
                        v-if="trip.description"
                        class="text-sm opacity-90 line-clamp-2"
                    >
                        {{ trip.description }}
                    </p>

                    <!-- Étapes / Jours -->
                    <p class="text-xs opacity-80">
                        {{ trip.steps_count ?? 0 }} étapes
                        <span v-if="trip.days_count">• {{ trip.days_count }} jours</span>
                    </p>

                    <!-- Créateur -->
                    <p v-if="trip.creator" class="text-sm italic opacity-90">
                        Par {{ trip.creator.name }}
                    </p>
                </Link>

                <!-- Bouton favori -->
                <div class="absolute top-3 right-3">
                    <FavoriteButton
                        :key="trip.id + '-' + String(trip.is_favorite)"
                        :trip-id="trip.id"
                        :is-favorite="trip.is_favorite"
                    />
                </div>
            </article>
        </div>

        <!-- =======================
             Pagination
        ======================= -->
        <div
            v-if="props.trips?.links?.length > 3"
            class="flex justify-center mt-10 gap-2 flex-wrap"
        >
            <Link
                v-for="link in props.trips.links"
                :key="link.label"
                :href="link.url || '#'"
                v-html="link.label"
                class="px-3 py-2 rounded-lg border text-sm font-medium"
                :class="{
                    'bg-pink-600 text-white border-pink-600': link.active,
                    'text-gray-600 hover:bg-gray-100': !link.active,
                    'opacity-50 cursor-not-allowed': !link.url
                }"
            />
        </div>

        <!-- =======================
             Aucun résultat
        ======================= -->
        <div
            v-else-if="!items.length"
            class="text-gray-500 border rounded-xl p-6 mt-6 text-center bg-gray-50"
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
