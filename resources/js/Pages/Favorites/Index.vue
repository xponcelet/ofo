<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    favorites: Object // paginator
})
</script>

<template>
    <h1 class="text-2xl font-semibold mb-4">Mes voyages favoris</h1>

    <ul class="space-y-3">
        <li v-for="trip in favorites.data" :key="trip.id" class="p-4 border rounded-xl flex items-center justify-between">
            <div>
                <Link :href="route('public.trips.show', trip.id)" class="font-medium hover:underline">
                    {{ trip.title || 'Sans titre' }}
                </Link>
                <div class="text-sm text-gray-500">{{ trip.favorites_count }} favoris</div>
            </div>
            <img v-if="trip.image" :src="trip.image" class="w-20 h-14 object-cover rounded-md" alt="">
        </li>
    </ul>

    <!-- pagination simple (à adapter à ton composant de pagination) -->
    <div class="mt-6 flex gap-2">
        <Link v-if="favorites.prev_page_url" :href="favorites.prev_page_url" preserve-scroll class="px-3 py-1 border rounded">Précédent</Link>
        <Link v-if="favorites.next_page_url" :href="favorites.next_page_url" preserve-scroll class="px-3 py-1 border rounded">Suivant</Link>
    </div>
</template>
