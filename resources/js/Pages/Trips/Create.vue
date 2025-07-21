<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import TripForm from '@/Components/TripForm.vue'

const form = useForm({
    title: '',
    description: '',
    image: '',
    start_date: '',
    end_date: '',
    budget: '',
    currency: 'EUR',
    is_public: false,
})

function submit() {
    form.post(route('trips.store'))
}
</script>

<template>
    <AppLayout title="Créer un nouveau voyage">
        <Head title="Créer un voyage" />
        <div class="max-w-4xl mx-auto py-10 px-4">
            <h1 class="text-2xl font-bold mb-6">Créer un nouveau voyage</h1>
            <TripForm :form="form" :on-submit="submit" submit-label="Créer le voyage" />
        </div>
    </AppLayout>
</template>
