<script setup>
import { Head, router } from '@inertiajs/vue3'
import RootLayout from "@/Layouts/RootLayout.vue";

const props = defineProps({
    trip: Object,
    isFavorite: Boolean,
})

function toggleFavorite() {
    const method = props.isFavorite ? 'delete' : 'post'
    router[method](route(props.isFavorite ? 'favorites.destroy' : 'favorites.store', props.trip.id), {}, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['isFavorite'] }), // recharge juste la prop
    })
}

</script>

<template>
    <Head :title="props.trip.title ?? 'Voyage'" />

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="rounded-2xl border shadow-sm overflow-hidden">
            <img v-if="props.trip.image" :src="props.trip.image" alt="" class="w-full h-60 object-cover" />
            <div class="p-6">
                <h1 class="text-2xl font-semibold">{{ props.trip.title || 'Sans titre' }}</h1>
                <p class="text-gray-600 mt-2">{{ props.trip.description }}</p>
                <p class="text-sm text-gray-500 mt-3">
                    {{ props.trip.start_date ?? '—' }} → {{ props.trip.end_date ?? '—' }}
                </p>
            </div>
        </div>

        <button
            @click="toggleFavorite"
            class="px-3 py-1 rounded text-white"
            :class="isFavorite ? 'bg-red-500' : 'bg-gray-500'"
        >
            {{ isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' }}
        </button>

        <div class="mt-8">
            <h2 class="text-lg font-medium mb-3">Étapes</h2>
            <ol class="space-y-3">
                <li v-for="step in props.trip.steps" :key="step.id" class="p-4 rounded-xl border">
                    <div class="font-medium">#{{ step.order ?? '-' }} — {{ step.title ?? step.location ?? 'Étape' }}</div>
                    <div class="text-sm text-gray-500">{{ step.location }}</div>
                </li>
            </ol>
        </div>
    </div>
</template>
