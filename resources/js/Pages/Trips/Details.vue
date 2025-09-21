<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import TripCreationProgress from '@/Components/Trip/TripCreationProgress.vue'

const form = useForm({
    title: '',           // Pré-rempli côté serveur avec la destination
    description: '',
    start_date: '',
    end_date: '',
    budget: '',
    currency: 'EUR',
    is_public: false,
})

function submit() {
    form.post(route('trips.details.store'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Complétez les infos de votre voyage" />

    <div class="max-w-3xl mx-auto py-10 px-4 space-y-10">
        <!-- Étape 3 / 3 -->
        <TripCreationProgress :current-step="3" />

        <!-- Titre -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📝 Détails du voyage</h1>
            <p class="text-sm text-gray-500 mt-1">Complétez les informations de votre aventure.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Titre -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Titre du voyage *</label>
                <input
                    v-model="form.title"
                    type="text"
                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Ex. À la découverte de l'Islande"
                />
                <InputError :message="form.errors.title" />
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                    v-model="form.description"
                    rows="4"
                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Racontez un peu ce que vous comptez faire..."
                />
                <InputError :message="form.errors.description" />
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de début</label>
                    <input
                        v-model="form.start_date"
                        type="date"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                    />
                    <InputError :message="form.errors.start_date" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                    <input
                        v-model="form.end_date"
                        type="date"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                    />
                    <InputError :message="form.errors.end_date" />
                </div>
            </div>

            <!-- Budget -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Budget total (€)</label>
                    <input
                        v-model="form.budget"
                        type="number"
                        min="0"
                        step="0.01"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                    />
                    <InputError :message="form.errors.budget" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Devise</label>
                    <input
                        v-model="form.currency"
                        type="text"
                        maxlength="3"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                    />
                    <InputError :message="form.errors.currency" />
                </div>
            </div>

            <!-- Visibilité -->
            <div>
                <label class="inline-flex items-center gap-2">
                    <input
                        v-model="form.is_public"
                        type="checkbox"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                    />
                    Rendre ce voyage public
                </label>
            </div>

            <!-- Boutons -->
            <div class="flex justify-end pt-6">
                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Créer le voyage 🎉
                </button>
            </div>
        </form>
    </div>
</template>
