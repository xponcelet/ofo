<script setup>
import RootLayout from "@/Layouts/RootLayout.vue";
import { Head, useForm } from '@inertiajs/vue3'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/StepMapPreview.vue'
import InputError from '@/Components/InputError.vue'

const form = useForm({
    location: '',
    latitude: null,
    longitude: null,
})

function updateCoords({ latitude, longitude }) {
    form.latitude = latitude
    form.longitude = longitude
}

function submit() {
    form.post(route('trips.start.store'))
}
</script>

<template>
    <Head title="Où commence le voyage ?" />

    <div class="max-w-xl mx-auto py-10 px-4 space-y-6">
        <h1 class="text-2xl font-bold mb-6">Où commence votre aventure&nbsp;?</h1>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Lieu obligatoire -->
            <div>
                <label class="block text-sm font-medium">Lieu de départ *</label>
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

            <!-- Bouton -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded"
                    :disabled="form.processing"
                >
                    Continuer
                </button>
            </div>
        </form>
    </div>
</template>
