<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm, Head } from '@inertiajs/vue3'

const props = defineProps({
    trip: Object,
    steps: Array, // pour la sélection d'une étape (optionnelle)
})

const form = useForm({
    title: '',
    description: '',
    location: '',
    external_link: '',
    start_date: '',
    end_date: '',
    step_id: null,
})

function submit() {
    form.post(route('accommodations.store', { trip: props.trip.id }))
}
</script>

<template>
    <AppLayout title="Ajouter un logement">
        <Head title="Ajouter un logement" />

        <div class="max-w-3xl mx-auto py-10 px-4">
            <h1 class="text-2xl font-bold mb-6">Ajouter un logement</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Titre *</label>
                    <input v-model="form.title" type="text" class="w-full rounded border-gray-300" required />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea v-model="form.description" class="w-full rounded border-gray-300" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Lieu</label>
                    <input v-model="form.location" type="text" class="w-full rounded border-gray-300" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Lien externe (Airbnb, Booking...)</label>
                    <input v-model="form.external_link" type="url" class="w-full rounded border-gray-300" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de début</label>
                        <input v-model="form.start_date" type="date" class="w-full rounded border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                        <input v-model="form.end_date" type="date" class="w-full rounded border-gray-300" />
                    </div>
                </div>

                <div v-if="steps.length">
                    <label class="block text-sm font-medium text-gray-700">Associer à une étape</label>
                    <select v-model="form.step_id" class="w-full rounded border-gray-300">
                        <option value="">Aucune étape</option>
                        <option v-for="step in steps" :key="step.id" :value="step.id">
                            Étape {{ step.order }} — {{ step.title }}
                        </option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Ajouter le logement
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
