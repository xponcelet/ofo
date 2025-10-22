<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/StepMapPreview.vue'
import InputError from '@/Components/InputError.vue'
import TripCreationProgress from '@/Components/Trip/TripCreationProgress.vue'

const form = useForm({
    departure: '',
    latitude: null,
    longitude: null,
    country: '',
    country_code: '',
})

// 🧭 Met à jour les coordonnées envoyées par l'autocomplete
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

// ✅ Soumission "classique" avec un point de départ
function submit() {
    form.post(route('trips.start.store'), { preserveScroll: true })
}

// 🕓 Passer l’étape (départ optionnel)
function skip() {
    router.post(route('trips.start.store'), { skip: true })
}

// ⬅️ Retour à la destination
function goBack() {
    router.visit(route('trips.destination'))
}
</script>

<template>
    <Head title="Où commence votre voyage ?" />

    <div class="max-w-3xl mx-auto py-10 px-4 space-y-8">
        <TripCreationProgress :current-step="2" />

        <div>
            <h1 class="text-2xl font-bold text-gray-900">📍 Point de départ</h1>
            <p class="text-sm text-gray-500 mt-1">
                Choisissez l’endroit où commence votre voyage ou passez cette étape.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Lieu de départ
                </label>
                <MapboxAutocomplete
                    v-model="form.departure"
                    @update:coords="updateCoords"
                    @update:country="updateCountry"
                    @update:countryCode="updateCountryCode"
                    placeholder="Ex : Bruxelles, Belgique"
                />
                <InputError :message="form.errors.departure" />
            </div>

            <StepMapPreview
                class="mt-4"
                :latitude="form.latitude ?? 50.8503"
                :longitude="form.longitude ?? 4.3517"
            />

            <div class="flex items-center justify-between pt-4">
                <button
                    type="button"
                    @click="goBack"
                    class="text-sm text-gray-600 hover:underline"
                >
                    ← Retour
                </button>

                <div class="flex items-center gap-3">
                    <!-- 🕓 Passer -->
                    <button
                        type="button"
                        @click="skip"
                        class="text-sm text-gray-500 underline hover:text-gray-700"
                        :disabled="form.processing"
                    >
                        Choisir plus tard
                    </button>

                    <!-- ✅ Continuer -->
                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        Continuer
                    </button>
                </div>
            </div>
        </form>

        <div
            class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-gray-400 border-t border-gray-200 pt-6"
        >
            <div class="flex items-start gap-2">
                <span class="text-emerald-500">✓</span>
                <span>Les étapes sont visibles uniquement par toi</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-emerald-500">✓</span>
                <span>Tu peux modifier le départ plus tard</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-emerald-500">✓</span>
                <span>On crée le voyage à l'étape suivante</span>
            </div>
        </div>
    </div>
</template>
