<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StepForm from '@/Components/StepForm.vue'

const page = usePage()
const tripId = page.props.trip.id

const form = useForm({
    title: '',
    description: '',
    location: '',
    latitude: null,
    longitude: null,
    start_date: '',
    end_date: '',
})

const submit = () => {
    form.post(`/trips/${tripId}/steps`)
}
</script>

<template>
    <AppLayout title="Nouvelle étape">
        <StepForm
            :form="form"
            :on-submit="submit"
            submit-label="Ajouter"
        />
    </AppLayout>
</template>
