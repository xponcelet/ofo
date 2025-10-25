<script setup>
import { useForm } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import StepForm from "@/Components/StepForm.vue";

const props = defineProps({
    trip: Object,
    storeUrl: String,
    defaultOrder: Number,
    // ⚠️ accepte Number ou String pour éviter le warning Vue
    insert_after_id: { type: [Number, String], default: null },
    at_start: { type: Boolean, default: false },
    // fourni par StepController@create
    suggested_start: { type: String, default: null },
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
    country_code: '',
    transport_mode: 'car',
    nights: 0,
    insert_after_id: props.insert_after_id,
    at_start: props.at_start,
})

onMounted(() => {
    const today = new Date().toISOString().slice(0, 10)
    form.start_date = props.suggested_start || today
})

function handleSubmit() {
    form.post(props.storeUrl, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            // Scroll vers le champ "Lieu principal" si une erreur est renvoyée (ex: location required)
            const el = document.getElementById('location-field')
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
            // Optionnel: tenter de focus un input interne si dispo :
            // document.querySelector('#location-field input')?.focus()
        },
    })
}
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">
            ➕ {{ at_start ? 'Ajouter le départ' : 'Ajouter une étape' }}
        </h1>

        <StepForm
            :form="form"
            :on-submit="handleSubmit"
            submit-label="Créer l’étape"
            :suggested-start="suggested_start"
            :at-start="at_start"
        />
    </div>
</template>
