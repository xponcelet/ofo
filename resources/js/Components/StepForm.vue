<template>
    <form @submit.prevent="onSubmit" class="max-w-2xl mx-auto space-y-6">
        <!-- Titre -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Titre (optionnel)</label>
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

        <!-- Bouton -->
        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700"
                :disabled="form.processing"
            >
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>

<script setup>
import InputError from '@/Components/InputError.vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/StepMapPreview.vue'

const props = defineProps({
    form: Object,
    onSubmit: Function,
    submitLabel: {
        type: String,
        default: 'Valider',
    },
})

function updateCoords({ latitude, longitude }) {
    props.form.latitude = latitude
    props.form.longitude = longitude
}
</script>
