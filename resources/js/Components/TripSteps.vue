<script setup>
import { Link, router } from '@inertiajs/vue3'
import AddStepButton from '@/Components/AddStepButton.vue'

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
        <h2 class="text-xl font-semibold">Étapes du voyage</h2>

        <!-- Ajouter au tout début -->
        <AddStepButton :trip-id="trip.id" :at-start="true" label="Ajouter avant la première" />


        <!-- Step list -->
        <div v-if="trip.steps.length" class="space-y-8 mt-4">
            <div
                v-for="(step, index) in trip.steps"
                :key="step.id"
            >
                <div class="flex justify-between gap-4 p-4 border rounded-lg shadow-sm bg-white">
                    <!-- Infos étape -->
                    <div class="flex-1">
                        <h3 class="font-bold text-lg">
                            Étape {{ step.order }} — {{ step.title }}
                        </h3>
                        <p v-if="step.description" class="text-sm text-gray-600 mt-1">{{ step.description }}</p>
                        <p class="text-sm text-gray-500 mt-1">📍 {{ step.location || 'Lieu non précisé' }}</p>
                        <p class="text-sm text-gray-500">📅 {{ step.start_date }} → {{ step.end_date }}</p>

                        <!-- 🏨 Logements -->
                        <div v-if="step.accommodations.length" class="mt-4 space-y-3">
                            <h4 class="text-sm font-semibold text-gray-700">Logement(s) lié(s) :</h4>

                            <div
                                v-for="acc in step.accommodations"
                                :key="acc.id"
                                class="border border-gray-200 bg-gray-50 rounded-lg p-3 shadow-sm flex items-start justify-between"
                            >
                                <div>
                                    <p class="font-semibold text-sm">{{ acc.title || 'Sans titre' }}</p>
                                    <p class="text-sm text-gray-600">{{ acc.location || 'Lieu inconnu' }}</p>
                                    <p class="text-sm text-gray-500">📅 {{ acc.start_date }} → {{ acc.end_date }}</p>
                                </div>

                                <div class="ml-4">
                                    <Link
                                        :href="route('accommodations.edit', acc.id)"
                                        class="text-xs px-3 py-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded"
                                    >
                                        ✏️ Modifier
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div v-else class="mt-4 text-sm text-gray-500 italic">Aucun logement enregistré.</div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col items-end gap-2 text-xs whitespace-nowrap">
                        <Link
                            :href="`/steps/${step.id}/edit`"
                            class="px-3 py-1 bg-blue-100 hover:bg-blue-200 rounded"
                        >
                            ✏️ Modifier l’étape
                        </Link>

                        <Link
                            :href="route('steps.accommodations.create', step.id)"
                            class="px-3 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded"
                        >
                            🏨 Ajouter un logement
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

                <!-- Bouton pour ajouter une étape APRÈS celle-ci -->
                <div class="mt-4">
                    <AddStepButton :trip-id="trip.id" :after-id="step.id" label="Ajouter une étape" />
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="text-gray-500 mt-6">Aucune étape enregistrée pour ce voyage.</div>
    </div>
</template>
