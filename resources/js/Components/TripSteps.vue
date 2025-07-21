<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    trip: Object,
})

function deleteStep(step) {
    if (confirm(`Supprimer l'étape "${step.title}" ?`)) {
        router.delete(`/steps/${step.id}`)
    }
}
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Étapes du voyage</h2>
            <Link
                :href="`/trips/${trip.id}/steps/create`"
                class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm"
            >
                + Ajouter une étape
            </Link>
        </div>

        <!-- Step list -->
        <div v-if="trip.steps.length" class="space-y-4">
            <div
                v-for="step in trip.steps"
                :key="step.id"
                class="p-4 border rounded-lg shadow-sm bg-white"
            >
                <div class="flex justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-lg">
                            Étape {{ step.order }} — {{ step.title }}
                        </h3>
                        <p v-if="step.description" class="text-sm text-gray-600 mt-1">{{ step.description }}</p>
                        <p class="text-sm text-gray-500 mt-1">📍 {{ step.location || 'Lieu non précisé' }}</p>
                        <p class="text-sm text-gray-500">📅 {{ step.start_date }} → {{ step.end_date }}</p>
                    </div>

                    <div class="flex flex-col items-end gap-2 text-xs">
                        <Link
                            :href="`/steps/${step.id}/edit`"
                            class="px-3 py-1 bg-blue-100 hover:bg-blue-200 rounded"
                        >
                            ✏️ Modifier
                        </Link>
                        <button
                            @click="deleteStep(step)"
                            class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-600 rounded"
                        >
                            🗑 Supprimer
                        </button>
                        <Link
                            :href="route('steps.move-up', step.id)"
                            method="patch"
                            as="button"
                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded"
                        >
                            ↑ Monter
                        </Link>
                        <Link
                            :href="route('steps.move-down', step.id)"
                            method="patch"
                            as="button"
                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded"
                        >
                            ↓ Descendre
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="text-gray-500">Aucune étape enregistrée pour ce voyage.</div>
    </div>
</template>
