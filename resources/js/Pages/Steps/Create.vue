<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/StepMapPreview.vue'

const props = defineProps({
    trip: Object,
    storeUrl: String,
    defaultOrder: Number,
    insert_after_id: { type: [Number, String], default: null },
    at_start: { type: Boolean, default: false },
})

const form = useForm({
    title: '',
    description: '',
    location: '',
    latitude: null,
    longitude: null,
    start_date: '',
    end_date: '',
    insert_after_id: props.insert_after_id,
    at_start: props.at_start,
})

function updateCoords({ latitude, longitude }) {
    form.latitude = latitude
    form.longitude = longitude
}

function submit() {
    form.clearErrors()
    if (!form.location || form.location.trim() === '') {
        form.setError('location', 'Le lieu est requis.')
        return
    }
    // ✅ Utiliser useForm pour poster
    form.post(props.storeUrl)
}
</script>

<template>
    <div class="max-w-3xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold mb-6">Ajouter une nouvelle étape</h1>

        <div v-if="form.hasErrors" class="mb-6">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <p v-if="form.errors.start_date">{{ form.errors.start_date }}</p>
                <p v-if="form.errors.end_date">{{ form.errors.end_date }}</p>
                <p v-if="form.errors.location">{{ form.errors.location }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Titre -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Titre</label>
                <input v-model="form.title" type="text" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
                <InputError :message="form.errors.title" />
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea v-model="form.description" rows="4" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
                <InputError :message="form.errors.description" />
            </div>

            <!-- Lieu -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Lieu *</label>
                <MapboxAutocomplete v-model="form.location" @update:coords="updateCoords" />
                <InputError :message="form.errors.location" />
            </div>

            <!-- Carte -->
            <StepMapPreview class="mt-4" :latitude="form.latitude" :longitude="form.longitude" />

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de début</label>
                    <input v-model="form.start_date" type="date" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
                    <InputError :message="form.errors.start_date" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                    <input v-model="form.end_date" type="date" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
                    <InputError :message="form.errors.end_date" />
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-4">
                <!-- ✅ Utiliser Link d’Inertia -->
                <Link :href="route('trips.show', trip.id)" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded hover:bg-gray-200">
                    Annuler
                </Link>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700" :disabled="form.processing">
                    Ajouter l’étape
                </button>
            </div>
        </form>
    </div>
</template>
