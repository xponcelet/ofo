<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    step: Object,   // Étape à laquelle l'activité sera liée
    trip: Object    // Voyage parent (utile pour afficher le contexte)
})

// Formulaire
const form = useForm({
    title: '',
    description: '',
    location: '',
    start_at: '',
    end_at: '',
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
                <span v-if="form.errors.title" class="text-red-600 text-sm">{{ form.errors.title }}</span>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea v-model="form.description"
                          rows="3"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                <span v-if="form.errors.description" class="text-red-600 text-sm">{{ form.errors.description }}</span>
            </div>

            <!-- Localisation -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Localisation</label>
                <input v-model="form.location" type="text"
                       placeholder="Ex : Jardin Majorelle"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                <span v-if="form.errors.location" class="text-red-600 text-sm">{{ form.errors.location }}</span>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Début</label>
                    <input v-model="form.start_at" type="datetime-local"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                    <span v-if="form.errors.start_at" class="text-red-600 text-sm">{{ form.errors.start_at }}</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fin</label>
                    <input v-model="form.end_at" type="datetime-local"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                    <span v-if="form.errors.end_at" class="text-red-600 text-sm">{{ form.errors.end_at }}</span>
                </div>
            </div>

            <!-- Lien externe -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Lien externe</label>
                <input v-model="form.external_link" type="url"
                       placeholder="https://exemple.com"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                <span v-if="form.errors.external_link" class="text-red-600 text-sm">{{ form.errors.external_link }}</span>
            </div>

            <!-- Coût + devise -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Coût</label>
                    <input v-model="form.cost" type="number" step="0.01"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                    <span v-if="form.errors.cost" class="text-red-600 text-sm">{{ form.errors.cost }}</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Devise</label>
                    <input v-model="form.currency" type="text"
                           maxlength="3"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase"
                           placeholder="EUR" />
                    <span v-if="form.errors.currency" class="text-red-600 text-sm">{{ form.errors.currency }}</span>
                </div>
            </div>

            <!-- Catégorie -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                <input v-model="form.category" type="text"
                       placeholder="ex: Culture, Gastronomie, Nature"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                <span v-if="form.errors.category" class="text-red-600 text-sm">{{ form.errors.category }}</span>
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
