<template>
    <div class="max-w-3xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold mb-6">Modifier le logement lié à : {{ step.title }}</h1>

        <!-- Alerte si erreur de dates -->
        <div v-if="form.errors.start_date || form.errors.end_date" class="mb-6">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <p v-if="form.errors.start_date">{{ form.errors.start_date }}</p>
                <p v-if="form.errors.end_date">{{ form.errors.end_date }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Titre -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Titre du logement *</label>
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
                    v-model="form.location"
                    @update:coords="updateCoords"
                />
                <InputError :message="form.errors.location" />
            </div>

            <!-- Carte -->
            <StepMapPreview
                class="mt-4"
                :latitude="form.latitude"
                :longitude="form.longitude"
            />

            <!-- Lien externe -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Lien externe (Airbnb, site hôtel...)</label>
                <input
                    v-model="form.external_link"
                    type="url"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                />
                <InputError :message="form.errors.external_link" />
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date d’arrivée</label>
                    <input
                        v-model="form.start_date"
                        type="date"
                        class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                    />
                    <InputError :message="form.errors.start_date" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de départ</label>
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
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700"
                    :disabled="form.processing"
                >
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/StepMapPreview.vue'
import RootLayout from "@/Layouts/RootLayout.vue";

const props = defineProps({
    accommodation: Object,
    step: Object,
})

const form = useForm({
    title: props.accommodation.title || '',
    description: props.accommodation.description || '',
    location: props.accommodation.location || props.step.location || '',
    latitude: props.accommodation.latitude || props.step.latitude || null,
    longitude: props.accommodation.longitude || props.step.longitude || null,
    external_link: props.accommodation.external_link || '',
    start_date: props.accommodation.start_date || props.step.start_date || '',
    end_date: props.accommodation.end_date || props.step.end_date || '',
})

function updateCoords({ latitude, longitude }) {
    form.latitude = latitude
    form.longitude = longitude
}

function submit() {
    form.clearErrors()

    if (props.step.start_date && form.start_date && form.start_date < props.step.start_date) {
        form.setError('start_date', `La date d’arrivée ne peut pas être avant celle de l’étape (${props.step.start_date}).`)
        return
    }

    if (props.step.end_date && form.end_date && form.end_date > props.step.end_date) {
        form.setError('end_date', `La date de départ ne peut pas dépasser celle de l’étape (${props.step.end_date}).`)
        return
    }

    form.put(route('accommodations.update', props.accommodation.id))
}
</script>
