<script setup>
import { useForm } from '@inertiajs/vue3'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'

const props = defineProps({
    tripId: { type: Number, required: true },
    show: { type: Boolean, default: false },
})
const emit = defineEmits(['close'])

// Formulaire Inertia
const form = useForm({
    start_location: '',
    latitude: null,
    longitude: null,
    departure_date: '',
})

//  Quand l’utilisateur choisit un lieu dans Mapbox
function onPlaceSelected(location) {
    form.start_location = location
}

//  Mise à jour automatique des coordonnées
function onCoordsUpdate(coords) {
    form.latitude = coords.latitude
    form.longitude = coords.longitude
}

//  Soumission du formulaire
function submit() {
    form.post(route('public.trips.use', props.tripId), {
        onSuccess: () => emit('close'),
    })
}

// Date minimum = aujourd’hui
const today = new Date().toISOString().split('T')[0]
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="emit('close')"></div>

        <div class="relative bg-white w-full max-w-lg rounded-xl shadow-xl border p-6">
            <h3 class="text-lg font-semibold mb-4">Utiliser ce voyage</h3>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Autocomplete Mapbox -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Point de départ
                    </label>
                    <div class="rounded-md border border-gray-300">
                        <MapboxAutocomplete
                            v-model="form.start_location"
                            @update:coords="onCoordsUpdate"
                            placeholder="Ex. Bruxelles, Belgique"
                        />
                    </div>
                </div>

                <!-- Date de départ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Date de départ
                    </label>
                    <input
                        v-model="form.departure_date"
                        type="date"
                        required
                        :min="today"
                        class="w-full rounded-md border-gray-300 focus:ring-pink-500 focus:border-pink-500"
                    />
                </div>

                <!-- Messages d’erreur -->
                <div v-if="form.hasErrors" class="text-sm text-red-600">
                    <ul class="list-disc list-inside">
                        <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
                    </ul>
                </div>

                <!-- Boutons -->
                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="px-4 py-2 rounded-md border bg-white text-gray-700 hover:bg-gray-50"
                        @click="emit('close')"
                    >
                        Annuler
                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2 rounded-md bg-pink-600 text-white hover:bg-pink-700 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
