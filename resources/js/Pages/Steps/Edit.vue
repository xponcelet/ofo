<template>
    <div class="max-w-4xl mx-auto py-10 px-4 space-y-12">
        <h1 class="text-2xl font-bold">Modifier l’étape</h1>

        <!-- Formulaire d’édition de l’étape -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Titre -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Titre</label>
                <input
                    v-model="form.title"
                    type="text"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                />
                <InputError :message="form.errors.title" />
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                    v-model="form.description"
                    rows="4"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                />
                <InputError :message="form.errors.description" />
            </div>

            <!-- Lieu -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Lieu *</label>
                <MapboxAutocomplete
                    :key="`step-${step.id}`"
                    v-model="form.location"
                    @update:coords="updateCoordsFromSearch"
                />
                <InputError :message="form.errors.location" />
                <p class="mt-2 text-xs text-gray-500">
                    Astuce : cliquez sur un <strong>nom de ville</strong> sur la carte pour le sélectionner.
                </p>
            </div>

            <!-- Carte (clic sur nom de ville uniquement) -->
            <StepPickMap
                class="mt-4"
                :latitude="form.latitude"
                :longitude="form.longitude"
                :recenter-on-update="true"
                :label-layers="['settlement-major-label','settlement-minor-label','place-label']"
                :click-padding-px="12"
                @pick="updateCoordsFromMap"
                @reverse-geocoded="applyLabelName"
            />





            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de début</label>
                    <input
                        v-model="form.start_date"
                        type="date"
                        class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                    />
                    <InputError :message="form.errors.start_date" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                    <input
                        v-model="form.end_date"
                        type="date"
                        class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                    />
                    <InputError :message="form.errors.end_date" />
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-4">
                <Link
                    :href="route('trips.show', step.trip_id)"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded hover:bg-gray-200"
                >
                    Annuler
                </Link>
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 disabled:opacity-60"
                    :disabled="form.processing"
                >
                    Enregistrer
                </button>
            </div>
        </form>

        <!-- Logements liés -->
        <div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">🏨 Logements liés à cette étape</h2>
                <Link
                    :href="route('steps.accommodations.create', step.id)"
                    class="text-sm bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700"
                >
                    + Ajouter un logement
                </Link>
            </div>

            <div v-if="step.accommodations.length" class="space-y-4">
                <div
                    v-for="accommodation in step.accommodations"
                    :key="accommodation.id"
                    class="border rounded p-4 bg-white shadow-sm"
                >
                    <h3 class="text-base font-semibold">{{ accommodation.title || 'Sans titre' }}</h3>
                    <p class="text-sm text-gray-600">{{ accommodation.location || 'Lieu inconnu' }}</p>
                    <p v-if="accommodation.start_date && accommodation.end_date" class="text-sm text-gray-500">
                        📅 {{ accommodation.start_date }} → {{ accommodation.end_date }}
                    </p>
                    <div class="mt-2 flex gap-2">
                        <Link
                            :href="route('accommodations.edit', accommodation.id)"
                            class="text-xs px-3 py-1 bg-blue-100 hover:bg-blue-200 rounded"
                        >
                            ✏️ Modifier
                        </Link>
                        <button
                            @click="deleteAccommodation(accommodation.id)"
                            class="text-xs px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded"
                        >
                            🗑 Supprimer
                        </button>
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-gray-500">Aucun logement enregistré pour cette étape.</p>
        </div>
    </div>
</template>

<script setup>
import { useForm, router, Link } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepPickMap from '@/Components/StepPickMap.vue'

const props = defineProps({ step: Object })

// Coercition en number pour éviter les strings depuis la DB
const form = useForm({
    title: props.step.title ?? '',
    description: props.step.description ?? '',
    location: props.step.location ?? '',
    latitude: props.step.latitude != null ? Number(props.step.latitude) : null,
    longitude: props.step.longitude != null ? Number(props.step.longitude) : null,
    start_date: props.step.start_date ?? '',
    end_date: props.step.end_date ?? '',
})

// Recherche → coords
function updateCoordsFromSearch({ latitude, longitude }) {
    form.latitude = latitude != null ? Number(latitude) : null
    form.longitude = longitude != null ? Number(longitude) : null
}

// Carte (clic sur label ville) → coords
function updateCoordsFromMap({ latitude, longitude }) {
    form.latitude = latitude != null ? Number(latitude) : null
    form.longitude = longitude != null ? Number(longitude) : null
}

// Carte (label choisi) → nom
function applyLabelName({ place }) {
    if (place) form.location = place
}

function submit() {
    form.put(route('steps.update', props.step.id))
}

function deleteAccommodation(id) {
    if (confirm('Supprimer ce logement ?')) {
        router.delete(route('accommodations.destroy', id))
    }
}
</script>
