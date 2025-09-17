<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/StepMapPreview.vue'
import InputError from '@/Components/InputError.vue'
import TripCreationProgress from '@/Components/Trip/TripCreationProgress.vue'

const form = useForm({
    destination: '',
    latitude: null,
    longitude: null,
})

function updateCoords({ latitude, longitude }) {
    form.latitude = latitude
    form.longitude = longitude
}

function submit() {
    form.post(route('trips.destination.store'))
}

function goBack() {
    router.visit(route('trips.index'))
}
</script>

<template>
    <Head title="Destination du voyage" />

    <div class="max-w-3xl mx-auto py-10 px-4 space-y-8">
        <!-- Progression -->
        <TripCreationProgress :current-step="1" />

        <!-- Titre -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900">✈️ Quelle est votre destination ?</h1>
            <p class="text-sm text-gray-500 mt-1">Indiquez le pays ou la région que vous souhaitez explorer.</p>
        </div>

        <!-- Formulaire -->
        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Destination *</label>
                <MapboxAutocomplete
                    v-model="form.destination"
                    @update:coords="updateCoords"
                />
                <InputError :message="form.errors.destination" />
            </div>

            <StepMapPreview
                class="mt-4"
                :latitude="form.latitude ?? 48.8566"
                :longitude="form.longitude ?? 2.3522"
            />

            <div class="flex items-center justify-between pt-4">
                <button
                    type="button"
                    @click="goBack"
                    class="text-sm text-gray-600 hover:underline"
                >
                    ← Retour
                </button>

                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded disabled:opacity-50"
                    :disabled="form.processing || !form.destination"
                >
                    Continuer
                </button>
            </div>
        </form>

        <!-- Garanties -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-gray-400 border-t border-gray-700 pt-6">
            <div class="flex items-start gap-2">
                <span class="text-emerald-400">✓</span>
                <span>Les voyages sont privés par défaut</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-emerald-400">✓</span>
                <span>Tu pourras modifier les infos ensuite</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-emerald-400">✓</span>
                <span>Les étapes seront ajoutées plus tard</span>
            </div>
        </div>
    </div>
</template>
