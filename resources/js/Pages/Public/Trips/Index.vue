<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import TripForm from "@/Components/TripForm.vue";
import GuestLayout from '@/Layouts/GuestLayout.vue'

const props = defineProps({
    trips: Object,    // pagination payload Laravel
    filters: Object,  // { q, sort }
})

const q = ref(props.filters.q ?? '')
const sort = ref(props.filters.sort ?? 'latest')

// Déclenche une navigation propre (querystring conservé)
function applyFilters() {
    router.get(route('public.trips.index'), { q: q.value, sort: sort.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

// UX: recherche après 300ms
let t
watch([q, sort], () => {
    clearTimeout(t)
    t = setTimeout(applyFilters, 300)
})
</script>

<template>
    <GuestLayout title="Voyages publics">
        <Head title="Voyages publics" />
        <div class="max-w-6xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-semibold mb-4">Voyages publics</h1>

            <div class="flex flex-col sm:flex-row gap-3 mb-6">
                <input
                    v-model="q"
                    type="search"
                    placeholder="Rechercher un voyage…"
                    class="w-full sm:flex-1 rounded-lg border px-3 py-2"
                />
                <select v-model="sort" class="rounded-lg border px-3 py-2">
                    <option value="latest">Plus récents</option>
                    <option value="oldest">Plus anciens</option>
                    <option value="title">Titre (A→Z)</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <article v-for="trip in trips.data" :key="trip.id" class="rounded-2xl border shadow-sm overflow-hidden">
                    <Link :href="route('public.trips.show', trip.id)">
                    <img v-if="trip.image" :src="trip.image" alt="" class="w-full h-40 object-cover" />
                        <div class="p-4">
                            <h2 class="text-lg font-medium line-clamp-1">{{ trip.title || 'Sans titre' }}</h2>
                            <p class="text-sm text-gray-600 line-clamp-2 mt-1">{{ trip.description }}</p>
                            <p class="text-xs text-gray-500 mt-2">
                                {{ trip.start_date ?? '—' }} → {{ trip.end_date ?? '—' }}
                            </p>
                        </div>
                    </Link>
                </article>
            </div>

            <!-- Pagination simple -->
            <div class="flex items-center justify-center gap-2 mt-8">
                <Link
                    v-if="trips.prev_page_url"
                    :href="trips.prev_page_url"
                    preserve-scroll
                    class="px-3 py-2 rounded border"
                >Précédent</Link>

                <span class="text-sm">Page {{ trips.current_page }} / {{ trips.last_page }}</span>

                <Link
                    v-if="trips.next_page_url"
                    :href="trips.next_page_url"
                    preserve-scroll
                    class="px-3 py-2 rounded border"
                >Suivant</Link>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;
}
.line-clamp-2 {
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
</style>
