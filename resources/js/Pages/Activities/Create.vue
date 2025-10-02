<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue' // ⚡ comme Steps/Create.vue

const props = defineProps({
    step: Object,
    trip: Object,
    date: String,
})

const form = useForm({
    title: '',
    description: '',
    location: '',
    start_at: props.date + ' 09:00', // par défaut matin
    end_at: props.date + ' 18:00',   // par défaut soir
    external_link: '',
    cost: '',
    currency: 'EUR',
    category: ''
})

function submit() {
    form.post(route('steps.activities.store', props.step.id), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <div class="max-w-3xl mx-auto py-8">
        <Head title="Nouvelle activité" />

        <h1 class="text-2xl font-bold mb-6">
            Nouvelle activité pour "{{ step.title }}" (voyage : {{ trip.title }})
        </h1>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Titre -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Titre *</label>
                <input v-model="form.title" type="text"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                       required />
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea v-model="form.description" rows="3"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
            </div>

            <!-- Localisation avec Mapbox -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Localisation</label>
                <MapboxAutocomplete v-model="form.location" placeholder="Chercher un lieu..." />
            </div>

            <!-- Date fixée -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Date</label>
                <input type="text" :value="props.date" disabled
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" />
                <p class="text-sm text-gray-500">La date est fixée par le jour choisi dans l’itinéraire</p>
            </div>

            <!-- Heure début + fin -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Heure de début</label>
                    <input v-model="form.start_at" type="time"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Heure de fin</label>
                    <input v-model="form.end_at" type="time"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                </div>
            </div>

            <!-- Lien externe -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Lien externe</label>
                <input v-model="form.external_link" type="url"
                       placeholder="https://exemple.com"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
            </div>

            <!-- Coût + devise -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Coût</label>
                    <input v-model="form.cost" type="number" step="0.01"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Devise</label>
                    <input v-model="form.currency" type="text" maxlength="3" placeholder="EUR"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase" />
                </div>
            </div>

            <!-- Catégorie -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                <input v-model="form.category" type="text"
                       placeholder="ex: Culture, Gastronomie, Nature"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
            </div>

            <!-- Boutons -->
            <div class="flex justify-between items-center">
                <Link :href="route('steps.edit', step.id) + '?tab=activities'" class="text-gray-600 hover:text-gray-900">
                    Annuler
                </Link>
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition"
                        :disabled="form.processing">
                    Créer l'activité
                </button>
            </div>
        </form>
    </div>
</template>
