<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    trips: Array,
})
</script>

<template>
    <AppLayout title="Mes voyages">
        <div class="max-w-6xl mx-auto py-10 px-4">
            <h1 class="text-3xl font-semibold mb-6">Mes voyages</h1>

            <div class="mb-4">
                <Link href="/trips/create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Nouveau voyage
                </Link>
            </div>

            <div v-if="trips.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="trip in trips" :key="trip.id" class="bg-white shadow rounded-xl p-4 border">
                    <h2 class="text-xl font-bold">{{ trip.title }}</h2>
                    <p class="text-gray-600">{{ trip.destination }}</p>
                    <p class="text-sm text-gray-400">{{ trip.start_date }} → {{ trip.end_date }}</p>
                    <Link :href="`/trips/${trip.id}`" class="text-blue-600 hover:underline text-sm mt-2 block">
                        Voir le voyage
                    </Link>
                </div>
            </div>

            <div v-else class="text-gray-500">Aucun voyage encore créé.</div>
        </div>
    </AppLayout>
</template>
