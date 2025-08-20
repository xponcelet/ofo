<!-- resources/js/Pages/Steps/StepForm.vue -->
<template>
    <form @submit.prevent="props.onSubmit" class="max-w-2xl mx-auto space-y-6">
        <!-- Titre -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Titre (optionnel)</label>
            <input
                v-model="props.form.title"
                type="text"
                class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
            <InputError :message="props.form.errors?.title" />
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
                v-model="props.form.description"
                rows="4"
                class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
            <InputError :message="props.form.errors?.description" />
        </div>

        <!-- Lieu -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Lieu *</label>
            <MapboxAutocomplete
                :key="mapboxInstanceKey"
                v-model="props.form.location"
                @update:coords="updateCoords"
            />
            <InputError :message="props.form.errors?.location" />
        </div>

        <!-- Carte -->
        <StepMapPreview
            class="mt-4"
            :latitude="props.form.latitude"
            :longitude="props.form.longitude"
        />

        <!-- Dates -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Date de début</label>
                <input
                    v-model="props.form.start_date"
                    type="date"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
                <InputError :message="props.form.errors?.start_date" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                <input
                    v-model="props.form.end_date"
                    type="date"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
                <InputError :message="props.form.errors?.end_date" />
            </div>
        </div>

        <!-- Bouton -->
        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="props.form.processing"
            >
                {{ props.submitLabel }}
            </button>
        </div>
    </form>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/StepMapPreview.vue'

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
    submitLabel: { type: String, default: 'Valider' },
})

const page = usePage()

// Clé stable pour forcer un remount propre du geocoder entre create/edit/changement d'étape
const routeStepId =
    page?.props?.ziggy?.routeParameters?.id ??
    page?.props?.routeParams?.id ??
    null

const mapboxInstanceKey = computed(() => {
    return `step-${props.form?.id ?? routeStepId ?? 'new'}`
})

// Valeurs par défaut pour éviter des undefined précoces
onMounted(() => {
    if (props.form.location === undefined) props.form.location = ''
    if (props.form.latitude === undefined) props.form.latitude = null
    if (props.form.longitude === undefined) props.form.longitude = null
})

function updateCoords({ latitude, longitude }) {
    props.form.latitude = latitude
    props.form.longitude = longitude
}
</script>

<style scoped>
/* Ajuste la largeur du contrôle geocoder si besoin */
.mapboxgl-ctrl-geocoder {
    min-width: 100%;
}
</style>
