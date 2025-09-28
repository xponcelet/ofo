<script setup>
import { useForm } from '@inertiajs/vue3'
import StepForm from "@/Components/StepForm.vue";

const props = defineProps({
    trip: Object,
    storeUrl: String,
    defaultOrder: Number,
    insert_after_id: Number,
    at_start: Boolean,
})

const form = useForm({
    title: '',
    description: '',
    location: '',
    latitude: null,
    longitude: null,
    start_date: '',
    end_date: '',
    country: '',
    transport_mode: 'car',
    nights: 0,
    insert_after_id: props.insert_after_id,
    at_start: props.at_start,
})

function handleSubmit() {
    form.post(props.storeUrl)
}
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">➕ Ajouter une étape</h1>

        <StepForm
            :form="form"
            :on-submit="handleSubmit"
            submit-label="Créer l’étape"
        />
    </div>
</template>
