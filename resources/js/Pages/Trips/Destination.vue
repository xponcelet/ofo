<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/Step/StepMapPreview.vue'
import InputError from '@/Components/InputError.vue'
import TripCreationProgress from '@/Components/Trip/TripCreationProgress.vue'

const form = useForm({
    destination: '',
    latitude: null,
    longitude: null,
    country: '',
    country_code: '',
})

function updateCoords({ latitude, longitude }) {
    form.latitude = latitude
    form.longitude = longitude
}

function updateCountry(country) {
    form.country = country
}

function updateCountryCode(code) {
    form.country_code = code
}

function submit() {
    form.post(route('trips.destination.store'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Quelle est la destination ?" />

    <div class="max-w-3xl mx-auto py-10 px-4 space-y-8">
        <TripCreationProgress :current-step="1" />

        <!-- Titre -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🌍 Destination du voyage</h1>
            <p class="text-sm text-gray-500 mt-1">
                Commencez par choisir votre destination principale.
            </p>
        </div>

        <!-- Formulaire -->
        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Destination *
                </label>
                <MapboxAutocomplete
                    v-model="form.destination"
                    @update:coords="updateCoords"
                    @update:country="updateCountry"
                    @update:countryCode="updateCountryCode"
                />
                <InputError :message="form.errors.destination" />
            </div>

            <StepMapPreview
                class="mt-4"
                :latitude="form.latitude ?? 50.8503"
                :longitude="form.longitude ?? 4.3517"
            />

            <div class="flex justify-end pt-4">
                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded disabled:opacity-50"
                    :disabled="form.processing || !form.destination"
                >
                    Continuer
                </button>
            </div>
        </form>

        <!-- Infos -->
        <div
            class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-gray-400 border-t border-gray-200 pt-6"
        >
            <div class="flex items-start gap-2">
                <span class="text-emerald-500">✓</span>
                <span>Les voyages sont privés par défaut</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-emerald-500">✓</span>
                <span>Tu pourras compléter les infos ensuite</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-emerald-500">✓</span>
                <span>Tu peux ajouter des étapes secondaires</span>
            </div>
        </div>
    </div>
</template>
