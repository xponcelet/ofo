<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StepForm from '@/Components/StepForm.vue'

const props = defineProps({
    step: Object,
})

const form = useForm({
    title: props.step.title || '',
    description: props.step.description || '',
    location: props.step.location || '',
    latitude: props.step.latitude,
    longitude: props.step.longitude,
    start_date: props.step.start_date || '',
    end_date: props.step.end_date || '',
})

const submit = () => {
    form.put(`/steps/${props.step.id}`)
}
</script>


<template>
    <AppLayout title="Modifier l’étape">
        <StepForm
            :form="form"
            :on-submit="submit"
            submit-label="Mettre à jour"
        />
    </AppLayout>
</template>


