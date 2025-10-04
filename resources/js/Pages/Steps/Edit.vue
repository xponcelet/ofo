<script setup>
import { useForm } from '@inertiajs/vue3'
import StepForm from '@/Components/StepForm.vue'

const props = defineProps({
    step: Object,
    trip: Object,
    allSteps: Array,
})

const form = useForm({
    id: props.step.id,
    title: props.step.title || '',
    description: props.step.description || '',
    location: props.step.location || '',
    latitude: props.step.latitude,
    longitude: props.step.longitude,
    start_date: props.step.start_date || '',
    end_date: props.step.end_date || '',
    country: props.step.country || '',
    country_code: '',
    transport_mode: props.step.transport_mode || 'car',
    nights: props.step.nights || 0,
})

function handleSubmit() {
    form.put(route('steps.update', props.step.id))
}
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">✏️ Modifier l’étape</h1>

        <StepForm
            :form="form"
            :on-submit="handleSubmit"
            submit-label="Enregistrer les modifications"
        />
    </div>
</template>
