<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import TripCreationProgress from '@/Components/Trip/TripCreationProgress.vue'
import TripForm from '@/Components/TripForm.vue'

const form = useForm({
    title: '',           // Pré-rempli côté serveur avec la destination
    description: '',
    image: '',
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
            <p class="text-sm text-gray-500 mt-1">
                Complétez les informations de votre aventure.
            </p>
        </div>

        <!-- Formulaire -->
        <TripForm
            :form="form"
            :on-submit="submit"
            submit-label="Créer le voyage 🎉"
        />
    </div>
</template>
