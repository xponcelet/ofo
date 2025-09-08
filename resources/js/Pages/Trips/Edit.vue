<script setup>
import RootLayout from "@/Layouts/RootLayout.vue";
import { Head, useForm } from '@inertiajs/vue3'
import TripForm from '@/Components/TripForm.vue'

const props = defineProps({
    trip: Object,
})

const form = useForm({
    title: props.trip.title ?? '',
    description: props.trip.description ?? '',
    image: props.trip.image ?? '',
    start_date: props.trip.start_date ?? '',
    end_date: props.trip.end_date ?? '',
    budget: props.trip.budget ?? '',
    currency: props.trip.currency ?? 'EUR',
    is_public: props.trip.is_public ?? false,
})

function submit() {
    form.put(route('trips.update', props.trip.id))
}
</script>

<template>
    <Head title="Modifier le voyage" />
    <div class="max-w-4xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold mb-6">Modifier le voyage</h1>
        <TripForm :form="form" :on-submit="submit" submit-label="Enregistrer les modifications" />
    </div>
</template>
