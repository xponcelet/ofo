<script setup>
import RootLayout from "@/Layouts/RootLayout.vue"
import { Head, Link, router } from "@inertiajs/vue3"
import { ref } from "vue"
import StepCard from "@/Components/Step/StepCard.vue"

defineOptions({ layout: RootLayout })

const props = defineProps({
    trip: Object,
    steps: Array,
})

// Action : ajout d’une nouvelle étape de départ
const addDeparture = () => {
    router.visit(route('steps.create', { trip: props.trip.id }), {
        method: 'get',
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Étapes du voyage" />

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Étapes du voyage</h1>
                <p class="text-sm text-gray-500">
                    Gérez les différentes étapes de votre itinéraire.
                </p>
            </div>

            <Link
                :href="route('trips.show', props.trip.id)"
                class="text-pink-600 hover:text-pink-700 text-sm font-medium flex items-center gap-1"
            >
                <span class="material-symbols-rounded text-[18px]">arrow_back</span>
                Retour au voyage
            </Link>
        </div>

        <!-- Liste des étapes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <StepCard
                v-for="step in props.steps"
                :key="step.id"
                :step="step"
                :trip-id="props.trip.id"
            />

            <!-- Bouton d’ajout -->
            <button
                @click="addDeparture"
                class="flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-gray-300 hover:border-pink-400 hover:bg-pink-50 transition-colors p-8 text-gray-500 hover:text-pink-600"
            >
                <span class="material-symbols-rounded text-[36px]">add_circle</span>
                <span class="font-medium">Ajouter une étape de départ</span>
            </button>
        </div>
    </div>
</template>
