<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import TripCreationProgress from '@/Components/Trip/TripCreationProgress.vue'

const props = defineProps({
    trip: Object
})

const form = useForm({
    title: props.trip.title || props.trip.destination || '',
    description: props.trip.description || '',
    image: props.trip.image || '',
    start_date: props.trip.start_date || '',
    end_date: props.trip.end_date || '',
    budget: props.trip.budget || '',
    currency: props.trip.currency || 'EUR',
    is_public: props.trip.is_public || false,
})

function submit() {
    form.put(route('trips.update', props.trip.id))
}

function goBack() {
    router.visit(route('trips.start'))
}
</script>

<template>
    <Head title="Détails du voyage" />
    <div class="max-w-3xl mx-auto py-10 px-4 space-y-8">
        <!-- Progression -->
        <TripCreationProgress :current-step="3" />

        <!-- Titre -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📝 Complétez votre voyage</h1>
            <p class="text-sm text-gray-500 mt-1">Ajoutez des informations utiles : dates, image, budget, visibilité, etc.</p>
        </div>

        <!-- Formulaire -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Titre -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                <input v-model="form.title" type="text" class="w-full rounded border-gray-300" />
                <InputError :message="form.errors.title" />
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                    <input v-model="form.start_date" type="date" class="w-full rounded border-gray-300" />
                    <InputError :message="form.errors.start_date" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                    <input v-model="form.end_date" type="date" class="w-full rounded border-gray-300" />
                    <InputError :message="form.errors.end_date" />
                </div>
            </div>

            <!-- Budget -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Budget</label>
                    <input v-model="form.budget" type="number" class="w-full rounded border-gray-300" />
                    <InputError :message="form.errors.budget" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Devise</label>
                    <select v-model="form.currency" class="w-full rounded border-gray-300">
                        <option value="EUR">EUR</option>
                        <option value="USD">USD</option>
                    </select>
                    <InputError :message="form.errors.currency" />
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea v-model="form.description" rows="4" class="w-full rounded border-gray-300" />
                <InputError :message="form.errors.description" />
            </div>

            <!-- Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Image (URL)</label>
                <input v-model="form.image" type="text" class="w-full rounded border-gray-300" />
                <InputError :message="form.errors.image" />
            </div>

            <!-- Visibilité -->
            <div class="flex items-center gap-2">
                <input v-model="form.is_public" type="checkbox" />
                <label class="text-sm text-gray-600">Rendre ce voyage public</label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4">
                <button type="button" @click="goBack" class="text-sm text-gray-600 hover:underline">← Retour</button>
                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded disabled:opacity-50"
                    :disabled="form.processing || !form.title"
                >
                    Terminer
                </button>
            </div>
        </form>
    </div>
</template>
