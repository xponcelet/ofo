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
})

function updateCoords({ latitude, longitude }) {
    form.latitude = latitude
    form.longitude = longitude
}

function submit() {
    form.post(route('trips.start.store'), {
        preserveScroll: true,
    })
}

function goBack() {
    router.visit(route('trips.destination'))
}
</script>

<template>
    <Head title="Où commence votre voyage ?" />
    <div class="max-w-3xl mx-auto py-10 px-4 space-y-8">
        <!-- Étape 2 / 3 -->
        <TripCreationProgress :current-step="2" />

        <!-- Titre -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📍 Point de départ</h1>
            <p class="text-sm text-gray-500 mt-1">Choisissez l’endroit où commence votre voyage.</p>
        </div>

        <!-- Formulaire -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Point de départ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lieu de départ *</label>
                <MapboxAutocomplete
                    v-model="form.departure"
                    @update:coords="updateCoords"
                />
                <InputError :message="form.errors.departure" />
            </div>

            <!-- Carte -->
            <StepMapPreview
                class="mt-4"
                :latitude="form.latitude ?? 50.8503"
                :longitude="form.longitude ?? 4.3517"
            />

            <!-- Boutons -->
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
                    :disabled="form.processing || !form.departure"
                >
                    Continuer
                </button>
            </div>
        </form>

        <!-- Garanties -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-gray-400 border-t border-gray-200 pt-6">
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
