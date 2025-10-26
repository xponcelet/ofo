<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import StepForm from "@/Components/StepForm.vue";

const page = usePage()

const props = defineProps({
    trip: Object,
    storeUrl: String,
    defaultOrder: Number,
    // peut venir en string via la query (?after=120), on gère les 2
    insert_after_id: [Number, String],
    at_start: Boolean,
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
    insert_after_id: props.insert_after_id ? Number(props.insert_after_id) : null,
    at_start: !!props.at_start,
})

onMounted(() => {
    const today = new Date().toISOString().slice(0, 10)
    form.start_date = props.suggested_start || today
})

function handleSubmit() {
    // ✅ Garde-fou client : champ obligatoire
    if (!form.location || String(form.location).trim() === '') {
        form.setError('location', 'Le lieu principal est requis.')
        // remonte visuellement vers le champ
        const el = document.getElementById('location-field')
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
        return
    }

    form.post(props.storeUrl, {
        preserveScroll: true,
        onError: (errors) => {
            // Affiche les erreurs remontées par Laravel (422 ou 302->session)
            console.debug('[Create onError] errors =', errors)
            if (errors.location) {
                const el = document.getElementById('location-field')
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
            }
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
