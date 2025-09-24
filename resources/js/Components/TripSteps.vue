<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AddStepButton from '@/Components/AddStepButton.vue'

const props = defineProps({
    trip: Object,
})

function deleteStep(step) {
    if (confirm(`Supprimer l'étape "${step.title}" ?`)) {
        router.delete(route('steps.destroy', step.id))
    }
}

const openMenuId = ref(null)
function toggleMenu(stepId) {
    openMenuId.value = openMenuId.value === stepId ? null : stepId
}
</script>

<template>
    <div class="space-y-6">
        <h2 class="text-2xl font-semibold text-gray-800">Étapes du voyage</h2>

        <AddStepButton :trip-id="trip.id" :at-start="true" label="Ajouter avant la première" />

        <div v-if="trip.steps.length" class="space-y-8">
            <div
                v-for="step in trip.steps"
                :key="step.id"
                class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden relative"
            >
                <!-- Image -->
                <div class="h-40 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">
                    📷 Image de l’étape
                </div>

                <!-- Contenu -->
                <div class="p-6 relative">
                    <!-- Bouton menu -->
                    <button
                        @click="toggleMenu(step.id)"
                        class="absolute top-4 right-4 p-2 rounded-full hover:bg-gray-100"
                    >
                        ⋮
                    </button>

                    <!-- Menu déroulant -->
                    <div
                        v-if="openMenuId === step.id"
                        class="absolute right-4 top-12 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-10"
                    >
                        <ul class="py-1 text-sm text-gray-700">
                            <li>
                                <Link
                                    :href="route('steps.edit', step.id)"
                                    class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"
                                >
                                    ✏️ Modifier
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="route('steps.accommodations.create', step.id)"
                                    class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"
                                >
                                    🏨 Ajouter logement
                                </Link>
                            </li>
                            <li>
                                <button
                                    @click="deleteStep(step)"
                                    class="w-full text-left flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50"
                                >
                                    🗑️ Supprimer
                                </button>
                            </li>
                            <li class="border-t my-1"></li>
                            <li>
                                <Link
                                    :href="route('steps.move-up', step.id)"
                                    method="patch"
                                    as="button"
                                    class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"
                                >
                                    ⬆️ Monter
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="route('steps.move-down', step.id)"
                                    method="patch"
                                    as="button"
                                    class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"
                                >
                                    ⬇️ Descendre
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Infos étape -->
                    <h3 class="text-xl font-bold text-gray-800">
                        Étape {{ step.order }} — {{ step.title }}
                    </h3>

                    <p v-if="step.description" class="mt-2 text-gray-600">
                        {{ step.description }}
                    </p>
                    <p v-else class="mt-2 text-gray-400 italic">Pas encore de description.</p>

                    <div class="mt-3 text-sm text-gray-500">
                        📍 {{ step.location || 'Lieu non précisé' }} <br />
                        📅 {{ step.start_date }} → {{ step.end_date }}
                    </div>
                </div>

                <!-- Ajouter après -->
                <div class="bg-gray-50 p-3 border-t text-center">
                    <AddStepButton :trip-id="trip.id" :after-id="step.id" label="Ajouter une étape" />
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-12 text-gray-500">
            <p class="text-lg mb-4">🚀 Aucune étape enregistrée pour ce voyage.</p>
            <AddStepButton :trip-id="trip.id" label="Créer la première étape" />
        </div>
    </div>
</template>
