<script setup>
import RootLayout from "@/Layouts/RootLayout.vue";
import { Link } from '@inertiajs/vue3'

defineOptions({ layout: RootLayout })

const props = defineProps({
    trip: Object,
    steps: Array,
})
</script>

<template>
    <div class="max-w-5xl mx-auto py-10 px-6">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold">Étapes de {{ trip.title }}</h1>
                <p class="text-sm text-gray-500">{{ steps.length }} étapes au total</p>
            </div>
            <Link
                :href="route('trips.show', trip.id)"
                class="text-sm text-pink-600 hover:underline"
            >
                ← Retour au voyage
            </Link>
        </div>

        <div
            v-if="steps.length"
            class="bg-white rounded-xl shadow divide-y"
        >
            <div
                v-for="(step, i) in steps"
                :key="step.id"
                class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition"
            >
                <div>
                    <h2 class="font-medium">
                        {{ i + 1 }}. {{ step.title || step.location || 'Étape sans nom' }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ step.start_date || '—' }} → {{ step.end_date || '—' }}
                    </p>
                </div>

                <div class="flex gap-3">
                    <Link
                        :href="route('steps.edit', step.id)"
                        class="text-pink-600 hover:underline text-sm"
                    >
                        ✏️ Modifier
                    </Link>
                    <Link
                        :href="route('steps.destroy', step.id)"
                        method="delete"
                        as="button"
                        class="text-red-600 hover:underline text-sm"
                        onclick="return confirm('Supprimer cette étape ?')"
                    >
                        🗑️ Supprimer
                    </Link>
                </div>
            </div>
        </div>

        <p v-else class="text-gray-500">Aucune étape pour l’instant.</p>

        <div class="mt-8">
            <Link
                :href="route('trips.steps.create', trip.id)"
                class="inline-block bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition"
            >
                ➕ Ajouter une étape
            </Link>
        </div>
    </div>
</template>
