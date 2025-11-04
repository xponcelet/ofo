<script setup>
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    step: Object,
    trip: Object,
    activities: Array,
})
</script>

<template>
    <Head :title="`Activités – ${step.title}`" />

    <div class="max-w-5xl mx-auto py-10 px-4">
        <div class="mb-6">
            <Link :href="route('public.trips.show', trip.id)" class="text-pink-600 hover:underline text-sm">
                ← Retour au voyage "{{ trip.title }}"
            </Link>
        </div>

        <h1 class="text-3xl font-bold text-pink-600 mb-6">
            Activités – {{ step.title }}
        </h1>

        <div v-if="activities.length">
            <div
                v-for="activity in activities"
                :key="activity.id"
                class="border border-gray-200 rounded-xl p-4 mb-4 shadow-sm hover:shadow transition"
            >
                <h2 class="text-lg font-semibold text-gray-800">{{ activity.title }}</h2>
                <p v-if="activity.description" class="text-gray-600 text-sm mt-1">{{ activity.description }}</p>
                <p v-if="activity.start_at" class="text-xs text-gray-500 mt-2">
                    🕒 {{ new Date(activity.start_at).toLocaleString('fr-FR') }}
                </p>
                <a
                    v-if="activity.external_link"
                    :href="activity.external_link"
                    target="_blank"
                    rel="noopener"
                    class="text-sm text-pink-600 hover:underline mt-2 inline-block"
                >
                    🔗 Lien externe
                </a>
            </div>
        </div>

        <p v-else class="text-gray-500 italic text-center mt-12">
            Aucune activité publique pour cette étape.
        </p>
    </div>
</template>
