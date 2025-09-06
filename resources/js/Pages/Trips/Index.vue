<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    trips: { type: Object, required: true }, // paginator attendu
})

// Normalise la liste pour éviter "undefined"
const items = computed(() => Array.isArray(props.trips?.data) ? props.trips.data : [])
</script>

<template>
    <AppLayout title="Mes voyages">
        <Head title="Mes voyages" />

        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-semibold">Mes voyages</h1>
                <Link
                    :href="route('trips.start')"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
                >
                    <span class="text-lg leading-none">＋</span>
                    Nouveau voyage
                </Link>
            </div>

            <div v-if="items.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <article
                    v-for="trip in items"
                    :key="trip.id"
                    class="relative rounded-2xl border shadow-sm overflow-hidden"
                >
                    <Link :href="route('trips.show', trip.id)">
                        <img v-if="trip.image" :src="trip.image" alt="" class="w-full h-40 object-cover" />

                        <!-- Badge favoris -->
                        <div
                            class="absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-1 rounded-full text-xs
                     flex items-center gap-1 shadow border"
                            :title="`${trip.favs ?? 0} favoris`"
                        >
                            <svg viewBox="0 0 24 24" class="w-4 h-4 fill-red-500">
                                <path d="M12 21s-7.5-4.6-9.4-8.5A5.6 5.6 0 0 1 12 5.7a5.6 5.6 0 0 1 9.4 6.8C19.5 16.4 12 21 12 21Z"/>
                            </svg>
                            <span>{{ trip.favs ?? 0 }}</span>
                        </div>

                        <div class="p-4">
                            <h2 class="text-lg font-medium line-clamp-1">{{ trip.title || 'Sans titre' }}</h2>
                            <p v-if="trip.description" class="text-sm text-gray-600 line-clamp-2 mt-1">{{ trip.description }}</p>

                            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                <span>{{ trip.start_date ?? '—' }} → {{ trip.end_date ?? '—' }}</span>
                                <span class="inline-flex items-center gap-1" :title="`${trip.steps_count ?? 0} étapes`">
                  <svg viewBox="0 0 24 24" class="w-4 h-4">
                    <path fill="currentColor" d="M8 6h13v2H8zM3 6h3v2H3zM8 11h13v2H8zM3 11h3v2H3zM8 16h13v2H8zM3 16h3v2H3z"/>
                  </svg>
                  <span>{{ trip.steps_count ?? 0 }}</span>
                </span>
                            </div>
                        </div>
                    </Link>

                    <div class="px-4 pb-4 flex gap-2">
                        <Link :href="route('trips.edit', trip.id)" class="text-xs px-2 py-1 rounded border">
                            Modifier
                        </Link>
                        <Link :href="route('public.trips.show', trip.id)" class="text-xs px-2 py-1 rounded border">
                            Voir public
                        </Link>
                    </div>
                </article>
            </div>

            <div v-else class="text-gray-500 border rounded-xl p-6">
                Aucun voyage encore créé.
            </div>

            <!-- Pagination (safe guards) -->
            <div v-if="trips?.current_page" class="flex items-center justify-center gap-2 mt-8">
                <Link
                    v-if="trips?.prev_page_url"
                    :href="trips.prev_page_url"
                    preserve-scroll
                    class="px-3 py-2 rounded border"
                >
                    Précédent
                </Link>

                <span class="text-sm">Page {{ trips.current_page }} / {{ trips.last_page }}</span>

                <Link
                    v-if="trips?.next_page_url"
                    :href="trips.next_page_url"
                    preserve-scroll
                    class="px-3 py-2 rounded border"
                >
                    Suivant
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;
}
.line-clamp-2 {
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
</style>
