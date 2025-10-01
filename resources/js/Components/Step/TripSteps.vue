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

// Helper pour formater la durée (minutes → h + min)
function formatDuration(minutes) {
    if (!minutes) return null
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    return h > 0 ? `${h}h ${m}min` : `${m}min`
}
</script>

<template>
    <div class="space-y-8">
        <h2 class="text-2xl font-semibold text-gray-800">Itinéraire</h2>

        <!-- Ajouter avant la première -->
        <AddStepButton :trip-id="trip.id" :at-start="true" label="➕ Ajouter avant la première" />

        <!-- Timeline -->
        <div v-if="trip.steps.length" class="relative">
            <!-- Ligne verticale -->
            <div class="absolute top-0 left-6 bottom-0 w-1 bg-gray-200"></div>

            <div
                v-for="step in trip.steps"
                :key="step.id"
                class="relative flex items-start space-x-6 mb-12"
            >
                <!-- Bulle sur la timeline -->
                <div class="relative z-10">
                    <div class="w-6 h-6 rounded-full bg-pink-600 border-2 border-white shadow"></div>
                </div>

                <!-- Carte étape -->
                <div class="flex-1 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

                    <!-- Image (seulement si présente) -->
                    <div v-if="step.image" class="h-40 overflow-hidden">
                        <img
                            :src="step.image"
                            alt="Image de l’étape"
                            class="w-full h-full object-cover"
                        />
                    </div>

                    <!-- Contenu -->
                    <div class="p-6 relative">
                        <!-- Menu contextuel -->
                        <button
                            @click="toggleMenu(step.id)"
                            class="absolute top-4 right-4 p-2 rounded-full hover:bg-gray-100"
                        >
                            ⋮
                        </button>

                        <div
                            v-if="openMenuId === step.id"
                            class="absolute right-4 top-12 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-10"
                        >
                            <ul class="py-1 text-sm text-gray-700">
                                <li>
                                    <Link :href="route('steps.edit', step.id)" class="block px-4 py-2 hover:bg-gray-50">
                                        ✏️ Modifier
                                    </Link>
                                </li>
                                <li>
                                    <Link :href="route('steps.accommodations.create', step.id)" class="block px-4 py-2 hover:bg-gray-50">
                                        🏨 Ajouter logement
                                    </Link>
                                </li>
                                <li>
                                    <button
                                        @click="deleteStep(step)"
                                        class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50"
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
                                        class="block w-full text-left px-4 py-2 hover:bg-gray-50"
                                    >
                                        ⬆️ Monter
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        :href="route('steps.move-down', step.id)"
                                        method="patch"
                                        as="button"
                                        class="block w-full text-left px-4 py-2 hover:bg-gray-50"
                                    >
                                        ⬇️ Descendre
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <!-- Jour + titre -->
                        <div class="mb-4">
                            <span class="text-xs font-semibold bg-pink-100 text-pink-700 px-3 py-1 rounded-full">
                                Jour {{ step.order }}
                            </span>
                            <h3 class="mt-2 text-xl font-bold text-gray-800">
                                {{ step.title }}
                            </h3>
                        </div>

                        <!-- Infos étape -->
                        <div class="mt-2 space-y-1">
                            <p class="text-lg font-semibold text-pink-700 flex items-center">
                                📍 {{ step.location || 'Lieu non précisé' }}
                                <span v-if="step.country" class="ml-2 text-sm text-gray-500">({{ step.country }})</span>
                            </p>
                            <p class="text-sm text-gray-600">
                                📅 {{ step.start_date }} → {{ step.end_date }}
                            </p>
                            <p v-if="step.transport_mode" class="text-sm text-gray-700">
                                🚗 Transport : {{ step.transport_mode }}
                            </p>
                            <p v-if="step.nights > 0" class="text-sm text-gray-700">
                                🌙 {{ step.nights }} nuit<span v-if="step.nights > 1">s</span>
                            </p>
                            <p v-if="step.distance_to_next" class="text-sm text-gray-700">
                                📏 Distance → {{ step.distance_to_next.toFixed(1) }} km
                            </p>
                            <p v-if="step.duration_to_next" class="text-sm text-gray-700">
                                ⏱️ Durée estimée → {{ formatDuration(step.duration_to_next) }}
                            </p>

                            <a
                                v-if="step.latitude && step.longitude"
                                :href="`https://www.google.com/maps/dir/?api=1&destination=${step.latitude},${step.longitude}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="block text-sm text-pink-600 hover:underline mt-1"
                            >
                                🗺️ Itinéraire Google Maps
                            </a>
                        </div>

                        <!-- Logements -->
                        <div class="mt-6">
                            <h4 class="font-semibold text-sm text-gray-700 mb-2">🏨 Logement(s)</h4>
                            <div v-if="step.accommodations.length" class="space-y-3">
                                <div
                                    v-for="acc in step.accommodations"
                                    :key="acc.id"
                                    class="rounded-lg border border-gray-100 bg-gray-50 p-3 flex justify-between items-start"
                                >
                                    <div>
                                        <p class="font-medium text-sm text-gray-800">{{ acc.title || 'Sans titre' }}</p>
                                        <p class="text-xs text-gray-600">{{ acc.location || 'Lieu inconnu' }}</p>
                                        <p class="text-xs text-gray-500">📅 {{ acc.start_date }} → {{ acc.end_date }}</p>
                                    </div>
                                    <Link
                                        :href="route('accommodations.edit', acc.id)"
                                        class="text-xs px-2 py-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded"
                                    >
                                        ✏️ Modifier
                                    </Link>
                                </div>
                            </div>
                            <p v-else class="text-sm text-gray-400 italic">Aucun logement enregistré.</p>
                        </div>
                    </div>

                    <!-- Ajouter après -->
                    <div class="bg-gray-50 p-3 border-t text-center">
                        <AddStepButton :trip-id="trip.id" :after-id="step.id" label="➕ Ajouter une étape" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Vide -->
        <div v-else class="flex flex-col items-center justify-center py-12 text-gray-500">
            <p class="text-lg mb-4">🚀 Aucune étape enregistrée pour ce voyage.</p>
            <AddStepButton :trip-id="trip.id" label="➕ Créer la première étape" />
        </div>
    </div>
</template>
